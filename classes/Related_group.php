<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * Table Definition for related_group
 *
 * @author Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 */

class Related_group extends Managed_DataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'related_group';                   // table name
    public $group_id;                        // int(4)  primary_key not_null
    public $related_group_id;                // int(4)  primary_key not_null
    public $created;                         // datetime()   not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public static function schemaDef()
    {
        return array(
            // @fixme description for related_group?
            'fields' => array(
                'group_id' => array('type' => 'int', 'not null' => true, 'description' => 'foreign key to user_group'),
                'related_group_id' => array('type' => 'int', 'not null' => true, 'description' => 'foreign key to user_group'),

                'created' => array('type' => 'datetime', 'not null' => true, 'description' => 'date this record was created'),
            ),
            'primary key' => array('group_id', 'related_group_id'),
            'foreign keys' => array(
                'related_group_group_id_fkey' => array('user_group', array('group_id' => 'id')),
                'related_group_related_group_id_fkey' => array('user_group', array('related_group_id' => 'id')),
            ),
        );
    }
}
?>