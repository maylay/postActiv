<?php
/****
 * @license   https://www.gnu.org/licenses/agpl.html
 */

if (!defined('GNUSOCIAL')) { exit(1); }

class InboxMessageList extends MessageList
{
    function newItem($message)
    {
        return new InboxMessageListItem($this->out, $message);
    }
}
