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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.     See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * ----------------------------------------------------------------------------
 * PHP version 5
 *
 * Data class to store a Poll response
 * ----------------------------------------------------------------------------
 * @category  Poll
 * @package   postActiv
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2013-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 * ============================================================================
 */

if (!defined('POSTACTIV')) { exit(1); }

/**
 * For storing the poll options and such
 */
class Poll_response extends Managed_DataObject
{
    public $__table = 'poll_response'; // table name
    public $id;          // char(36) primary key not null -> UUID
    public $uri;         // varchar(191)   not 255 because utf8mb4 takes more space
    public $poll_id;     // char(36) -> poll.id UUID
    public $profile_id;  // int -> profile.id
    public $selection;   // int -> choice #
    public $created;     // datetime

    /**
     * The One True Thingy that must be defined and declared.
     */
    public static function schemaDef()
    {
        return array(
            'description' => 'Record of responses to polls',
            'fields' => array(
                'id' => array('type' => 'char', 'length' => 36, 'not null' => true, 'description' => 'UUID of the response'),
                'uri' => array('type' => 'varchar', 'length' => 191, 'not null' => true, 'description' => 'UUID to the response notice'),
                'poll_id' => array('type' => 'char', 'length' => 36, 'not null' => true, 'description' => 'UUID of poll being responded to'),
                'profile_id' => array('type' => 'int'),
                'selection' => array('type' => 'int'),
                'created' => array('type' => 'datetime', 'not null' => true),
            ),
            'primary key' => array('id'),
            'unique keys' => array(
                'poll_uri_key' => array('uri'),
                'poll_response_poll_id_profile_id_key' => array('poll_id', 'profile_id'),
            ),
            'indexes' => array(
                'poll_response_profile_id_poll_id_index' => array('profile_id', 'poll_id'),
            )
        );
    }

    /**
     * Get a poll response based on a notice
     *
     * @param Notice $notice Notice to check for
     *
     * @return Poll_response found response or null
     */
    static function getByNotice($notice)
    {
        return self::getKV('uri', $notice->uri);
    }

    /**
     * Get the notice that belongs to this response...
     *
     * @return Notice
     */
    function getNotice()
    {
        return Notice::getKV('uri', $this->uri);
    }

    function getUrl()
    {
        return $this->getNotice()->getUrl();
    }

    /**
     *
     * @return Poll
     */
    function getPoll()
    {
        return Poll::getKV('id', $this->poll_id);
    }
    /**
     * Save a new poll notice
     *
     * @param Profile $profile
     * @param Poll    $poll the poll being responded to
     * @param int     $selection (1-based)
     * @param array   $opts (poll responses)
     *
     * @return Notice saved notice
     */
    static function saveNew($profile, $poll, $selection, $options=null)
    {
        if (empty($options)) {
            $options = array();
        }

        if (!$poll->isValidSelection($selection)) {
            // TRANS: Client exception thrown when responding to a poll with an invalid option.
            throw new ClientException(_m('Invalid poll selection.'));
        }
        $opts = $poll->getOptions();
        $answer = $opts[$selection - 1];

        $pr = new Poll_response();
        $pr->id          = UUID::gen();
        $pr->profile_id  = $profile->id;
        $pr->poll_id     = $poll->id;
        $pr->selection   = $selection;

        if (array_key_exists('created', $options)) {
            $pr->created = $options['created'];
        } else {
            $pr->created = common_sql_now();
        }

        if (array_key_exists('uri', $options)) {
            $pr->uri = $options['uri'];
        } else {
            $pr->uri = common_local_url('showpollresponse',
                                        array('id' => $pr->id));
        }

        common_log(LOG_DEBUG, "Saving poll response: $pr->id $pr->uri");
        $pr->insert();

        // TRANS: Notice content voting for a poll.
        // TRANS: %s is the chosen option in the poll.
        $content  = sprintf(_m('voted for "%s"'),
                            $answer);
        $link = '<a href="' . htmlspecialchars($poll->uri) . '">' . htmlspecialchars($answer) . '</a>';
        // TRANS: Rendered version of the notice content voting for a poll.
        // TRANS: %s a link to the poll with the chosen option as link description.
        $rendered = sprintf(_m('voted for "%s"'), $link);

        $tags    = array();

        $options = array_merge(array('urls' => array(),
                                     'rendered' => $rendered,
                                     'tags' => $tags,
                                     'reply_to' => $poll->getNotice()->id,
                                     'object_type' => PollPlugin::POLL_RESPONSE_OBJECT),
                               $options);

        if (!array_key_exists('uri', $options)) {
            $options['uri'] = $pr->uri;
        }

        $saved = Notice::saveNew($profile->id,
                                 $content,
                                 array_key_exists('source', $options) ?
                                 $options['source'] : 'web',
                                 $options);

        return $saved;
    }
}
?>