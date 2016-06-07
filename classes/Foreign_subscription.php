<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Table Definition for foreign_subscription
 *
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
 *
 * @category  Networking
 * @package   postActiv
 * @author    Evan Prodromou <evan@prodromou.name>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2008-2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      https://git.gnu.io/maiyannah/postActiv
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Foreign_subscription extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'foreign_subscription';            // table name
    public $service;                         // int(4)  primary_key not_null
    public $subscriber;                      // int(4)  primary_key not_null
    public $subscribed;                      // int(4)  primary_key not_null
    public $created;                         // datetime()   not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(

            'fields' => array(
                'service' => array('type' => 'int', 'not null' => true, 'description' => 'service where relationship happens'),
                'subscriber' => array('type' => 'int', 'size' => 'big', 'not null' => true, 'description' => 'subscriber on foreign service'),
                'subscribed' => array('type' => 'int', 'size' => 'big', 'not null' => true, 'description' => 'subscribed user'),
                'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            ),
            'primary key' => array('service', 'subscriber', 'subscribed'),
            'foreign keys' => array(
                'foreign_subscription_service_fkey' => array('foreign_service', array('service' => 'id')),
                'foreign_subscription_subscriber_fkey' => array('foreign_user', array('subscriber' => 'id', 'service' => 'service')),
                'foreign_subscription_subscribed_fkey' => array('foreign_user', array('subscribed' => 'id', 'service' => 'service')),
            ),
            'indexes' => array(
                'foreign_subscription_subscriber_idx' => array('service', 'subscriber'),
                'foreign_subscription_subscribed_idx' => array('service', 'subscribed'),
            ),
        );
    }
}
?>