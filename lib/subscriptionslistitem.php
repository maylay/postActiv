<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 */

if (!defined('POSTACTIV')) { exit(1); }

class SubscriptionsListItem extends SubscriptionListItem
{
    function showOwnerControls()
    {
        $sub = Subscription::pkeyGet(array('subscriber' => $this->owner->id,
                                           'subscribed' => $this->profile->id));
        if (!$sub) {
            return;
        }

        $transports = array();
        Event::handle('GetImTransports', array(&$transports));
        if (!$transports && !common_config('sms', 'enabled')) {
            return;
        }

        $this->out->elementStart('form', array('id' => 'subedit-' . $this->profile->id,
                                          'method' => 'post',
                                          'class' => 'form_subscription_edit',
                                          'action' => common_local_url('subedit')));
        $this->out->hidden('token', common_session_token());
        $this->out->hidden('profile', $this->profile->id);
        if ($transports) {
            $attrs = array('name' => 'jabber',
                           'type' => 'checkbox',
                           'class' => 'checkbox',
                           'id' => 'jabber-'.$this->profile->id);
            if ($sub->jabber) {
                $attrs['checked'] = 'checked';
            }

            $this->out->element('input', $attrs);
            // TRANS: Checkbox label for enabling IM messages for a profile in a subscriptions list.
            $this->out->element('label', array('for' => 'jabber-'.$this->profile->id), _m('LABEL','IM'));
        } else {
            $this->out->hidden('jabber', $sub->jabber);
        }
        if (common_config('sms', 'enabled')) {
            $attrs = array('name' => 'sms',
                           'type' => 'checkbox',
                           'class' => 'checkbox',
                           'id' => 'sms-'.$this->profile->id);
            if ($sub->sms) {
                $attrs['checked'] = 'checked';
            }

            $this->out->element('input', $attrs);
            // TRANS: Checkbox label for enabling SMS messages for a profile in a subscriptions list.
            $this->out->element('label', array('for' => 'sms-'.$this->profile->id), _('SMS'));
        } else {
            $this->out->hidden('sms', $sub->sms);
        }
        // TRANS: Save button for settings for a profile in a subscriptions list.
        $this->out->submit('save', _m('BUTTON','Save'));
        $this->out->elementEnd('form');
    }
}
?>