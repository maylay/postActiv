<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2012, StatusNet, Inc
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
 * PHP version 5
 *
 * Cancel the subscription of a profile
 *
 * @category  Group
 * @package   postActiv
 * @author    Brion Vibber <brion@pobox.com>
 * @author    Siebrand Mazeland <s.mazeland@xs4all.nl>
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2011 StatusNet, Inc.
 * @copyright 2013-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

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

        if (GNUsocial::isAjax()) {
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