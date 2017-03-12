<?php
/* ============================================================================
 * Title: CancelSubscription
 * Cancel the subscription of a profile
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2017, Maiyannah Bishop
 *
 * Derived from code copyright various sources:
 * o GNU Social (C) 2013-2016, Free Software Foundation, Inc
 * o StatusNet (C) 2008-2012, StatusNet, Inc
 * ----------------------------------------------------------------------------
 * License:
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
 * <https://www.gnu.org/licenses/agpl.html>
 * ----------------------------------------------------------------------------
 * About:
 * Cancel the subscription of a profile
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Brion Vibber <brion@pobox.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

if (!defined('POSTACTIV')) { exit(1); }

class CancelsubscriptionAction extends FormAction
{
    protected $needPost = true;

    protected function doPreparation()
    {
        $profile_id = $this->int('unsubscribeto');
        $this->target = Profile::getKV('id', $profile_id);
        if (!$this->target instanceof Profile) {
            throw new NoProfileException($profile_id);
        }
    }

    protected function doPost()
    {
        try {
            $request = Subscription_queue::pkeyGet(array('subscriber' => $this->scoped->id,
                                                         'subscribed' => $this->target->id));
            if ($request instanceof Subscription_queue) {
                $request->abort();
            }
        } catch (AlreadyFulfilledException $e) {
            common_debug('Tried to cancel a non-existing pending subscription');
        }

        if (postActiv::isAjax()) {
            $this->startHTML('text/xml;charset=utf-8');
            $this->elementStart('head');
            // TRANS: Title after unsubscribing from a group.
            $this->element('title', null, _m('TITLE','Unsubscribed'));
            $this->elementEnd('head');
            $this->elementStart('body');
            $subscribe = new SubscribeForm($this, $this->target);
            $subscribe->show();
            $this->elementEnd('body');
            $this->endHTML();
            exit();
        }
        common_redirect(common_local_url('subscriptions', array('nickname' => $this->scoped->getNickname())), 303);
    }
}
?>
