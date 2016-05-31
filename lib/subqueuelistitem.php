<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 */

if (!defined('POSTACTIV')) { exit(1); }

class SubQueueListItem extends ProfileListItem
{
    public function showActions()
    {
        $this->startActions();
        if (Event::handle('StartProfileListItemActionElements', array($this))) {
            $this->showApproveButtons();
            Event::handle('EndProfileListItemActionElements', array($this));
        }
        $this->endActions();
    }

    public function showApproveButtons()
    {
        $this->out->elementStart('li', 'entity_approval');
        $form = new ApproveSubForm($this->out, $this->profile);
        $form->show();
        $this->out->elementEnd('li');
    }
}
?>