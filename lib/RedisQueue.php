<?php
/* ============================================================================
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * ----------------------------------------------------------------------------
 * PHP version 5
 *
 * Simple-minded queue manager for storing items in the database
 *
 * @category  Queue
 * @package   postActiv
 * @author    Neil E. Hodges
 * @author    Neil E. Hodges <47hasbegun@gmail.com>
 * @copyright 2016 Neil E. Hdoges
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://status.net/
 */


class HostPort {
	public $host;
	public $port;
	function __construct($host, $port) {
		$this->host = $host;
		$this->port = $port;
	}
}



class RedisLock {
	protected $redis;
	const REDLOCK_UNLOCK = '
		if redis.call("GET", KEYS[1]) == ARGV[1] then
			return redis.call("DEL", KEYS[1])
		else
			return 0
		end';

	function __construct($redis, $name) {
		$this->redis = $redis;
		$this->name = $name;
		$this->nonce = null;
	}

	function lock($expiration, $retries = 1) {
		$this->nonce = uniqid();
		for($i = 0; $i < $retries; $i++) {
			$result = $this->redis->set($this->name, $this->nonce, ['NX', 'PX' => $expiration]);
			if ($result)
				return true;
		}
		return false;
	}

	function unlock() {
		return $this->redis->eval(RedisLock::REDLOCK_UNLOCK, [$this->name, $this->nonce], 1);
	}
}

class RedisTimeout extends Exception {
	public $processing_id_count;
	public function __construct($processing_id_count) {
		Exception::__construct("Timed out waiting for Redis.  PIDC: $processing_id_count");
		$this->processing_id_count = $processing_id_count;
	}
}


class RedisContainer {
	# Only used for storing the item within Redis itself.
	public $item;
	public $created;
	public function __construct($item, $created = null) {
		$this->item = $item;
		if ($created === null)
			$this->created = new DateTimeImmutable('now', new DateTimeZone('UTC'));
		else
			$this->created = $created;
	}
}

class RedisQueueItem {
	public $id;
	public $tries;
	public $created;
	public $item;
	public $processing_id_count;
	public function __construct($id, $tries, $created, $item, $processing_id_count = 0) {
		$this->id = $id;
		$this->tries = $tries;
		$this->created = $created;
		$this->item = $item;
		$this->processing_id_count = 0;
	}
	public function age($now = null) {
		if ($now === null)
			$now = new Datetime('now', new DateTimeZone('UTC'));
		return $now->diff($this->created);
	}
}

class RedisQueue {
	const REDIS_SYNC = '
		local flush_count = 0
		local to_repush = {}
		while true
		do
			local item_id = redis.call("RPOP", KEYS[1])
			if not item_id then
				break
			end

			if redis.call("SISMEMBER", KEYS[2], item_id) > 0 then
				-- Completed items
				redis.call("SREM", KEYS[2], item_id)
				flush_count = flush_count + 1

			elseif redis.call("EXISTS", ARGV[1] .. "." .. item_id .. ".processing") == 0 then
				-- Items that have been explicitcly marked incomplete
				redis.call("LPUSH", KEYS[4], item_id)

			else
				-- Items still being processed
				table.insert(to_repush, item_id)
			end
		end
		for key, value in ipairs(to_repush) do
			redis.log(redis.LOG_VERBOSE, "Repushing: " .. value)
			redis.call("LPUSH", KEYS[1], value)
		end

		return flush_count';
	const PROCESSING_TIMEOUT = 300; # 5 minutes
	protected $redis;

	function __construct($address, $namespace, $expiration) {
		$this->namespace = $namespace;
		$this->key_regex = sprintf('/^%s\./', addslashes($namespace));
		$this->redis = new Redis();
		if($address instanceof HostPort) {
			if (!$this->redis->connect($address->host, $address->port))
				throw new RuntimeException("Failed to connect to Redis at $address->host:$address->port.");
		} else {
			if (!$this->redis->connect($address))
				throw new RuntimeException("Failed to connect to Redis at $address");
		}
		$this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
		$this->redis->setOption(Redis::OPT_PREFIX, $namespace . '.');
		$this->expiration = $expiration;
		$this->sync();
	}


	public function close($sync = false) {
		if ($this->redis !== null) {
			if ($sync)
				$this->sync();
			$this->redis->close();
			$this->redis = null;
		}
	}


	public function sync($disk = false) {
		if ($this->redis === null)
			throw new LogicException("Connection has already been closed");
		$result = $this->redis->eval(RedisQueue::REDIS_SYNC, ['processing_ids', 'completed_ids', 'incomplete_ids', 'item_ids', $this->namespace], 4);
		$error = $this->redis->getLastError();

		if ($error !== null)
			throw new LogicException(strval($error));

		if ($disk)
			$this->redis->save();
		return ($result > 0);
	}

	public static function fromTCP($host, $port, $namespace, $expiration) {
		$hp = new HostPort($host, $port);
		return new RedisQueue($hp, $namespace, $expiration);
	}

	public static function fromUnix($location, $namespace, $expiration) {
		return new RedisQueue($location, $namespace, $expiration);
	}

	protected function nextIdLock() {
		return new RedisLock($this->redis, 'next_id_lock');
	}

	protected function nextId() {
		if ($this->redis === null)
			throw new LogicException("Connection has already been closed");

		$lock = $this->nextIdLock();
		if (!$lock->lock(60 * 1000, 10))
			throw new RuntimeException("Failed to acquire next_id lock");

		$next_id = null;
		try {
			$next_id = $this->redis->get('next_id');
			if ($next_id === false)
				$next_id = 0;

			$next_id = gmp_init($next_id, 16);
			$next_id = gmp_add($next_id, 1);
			$next_id = gmp_strval($next_id, 16);

			$this->redis->set('next_id', $next_id);
			
		} finally {
			if (!$lock->unlock())
				throw new RuntimeException("Failed to release next_id lock");
		}
		return $next_id;
	}

	public function put($item) {
		$item = serialize(new RedisContainer($item));
		$item_id = $this->nextId();
		$item_id = "items.$item_id";

		$result = $this->redis->multi()
			->set($item_id, $item)
			->set("$item_id.tries", 0)
			->expire($item_id, $this->expiration)
			->lPush('item_ids', $item_id)
			->exec();

		if ($result[2] === false)
			throw new RuntimeException('Failed to add item');

		return $item_id;
	}

	public function get($timeout, $tries = 0) {
		if ($this->redis === null)
			throw new LogicException("Connection has already been closed");

		$processing_id_count = 0;

		for($i = 0; ($tries == 0 || $i < $tries); $i++) {
			# Can't use a multi() here because the timeout is ignored.
			$item_id = $this->redis->bRPopLPush('item_ids', 'processing_ids', $timeout);
			$processing_id_count = $this->redis->lLen('processing_ids');

			if(!$item_id)
				break;
			
			$result = $this->redis->multi()
				->sIsMember('completed_ids', $item_id)
				->get("$item_id.tries") # We want the value before incrementing
				->get($item_id)
				->incr("$item_id.tries")
				->exec();

			if ($result[0] > 0) {
				# Since this item has been completed, we can just delete it here
				# and let the next sync() call purge it later.
				$this->redis->multi()
					->delete($item_id)
					->delete("$item_id.tries")
					->exec();

			} else if (!$result[2]) {
				# This item has expired, so we can't do anything with it.
				$this->redis->multi()
					->sAdd('completed_ids', $item_id)
					->delete($item_id)
					->delete("$item_id.tries")
					->exec();

			} else {
				# Got something we can use
				$tries = intval($result[1]);
				$item = unserialize($result[2]);

				$this->redis->set("$item_id.processing", 1, ['EX' => RedisQueue::PROCESSING_TIMEOUT]);

				return new RedisQueueItem($item_id, $tries, $item->created, $item->item, $processing_id_count);
			}
		}
		throw new RedisTimeout($processing_id_count);
	}

	public function markComplete($item_id) {
		if ($this->redis === null)
			throw new LogicException("Connection has already been closed");

		$this->redis->multi()
			->sAdd('completed_ids', $item_id)
			->delete($item_id)
			->delete("$item_id.tries")
			->exec();
		
		return true;
	}

	public function markIncomplete($item_id) {
		if ($this->redis === null)
			throw new LogicException("Connection has already been closed");
		$this->redis->delete("$item_id.processing");
		return true;
	}


	public function scrub() {
		if ($this->redis === null)
			throw new LogicException("Connection has already been closed");
		$inkeys = $this->redis->keys('*');
		$outkeys = [];
		foreach($inkeys as $inkey)
			array_push($outkeys, preg_replace($this->key_regex, '', $inkey));
		return $this->redis->delete($outkeys);
	}

	public function itemsBeingProcessed() {
		# This isn't a precise count, but rather a metric used for determining
		# when to call sync().
		if ($this->redis === null)
			throw new LogicException("Connection has already been closed");
		return $this->redis->lLen('processing_ids');
	}
}
?>
