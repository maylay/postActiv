<?php
/* ============================================================================
 * Title: Notice
 * Handler for posting new notices
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
 * Handler for posting new notices
 *
 * PHP version:
 * Tested with PHP 7.0
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Matthew Gregg <matthew.gregg@gmail.com>
 * o Mike Cochrane <mikec@mikenz.geek.nz>
 * o Zach Copley
 * o Robin Millette <robin@millette.info>
 * o Sarven Capadisli
 * o Adrian Lang <mail@adrianlang.de>
 * o Ciaran Gultneiks <ciaran@ciarang.com>
 * o Craig Andrews <candrews@integralblue.com>
 * o Brion Vibber <brion@pobox.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Chimo <chimo@chromic.org>
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

/**
 * Action for posting new notices
 */
class NewnoticeAction extends FormAction
{
    protected $form = 'Notice';

    protected $inreplyto = null;

    /**
     * Title of the page
     *
     * Note that this usually doesn't get called unless something went wrong
     *
     * @return string page title
     */
    function title()
    {
        if ($this->getInfo() && $this->stored instanceof Notice) {
            // TRANS: Page title after sending a notice.
            return _('Notice posted');
        }
        if ($this->int('inreplyto')) {
            return _m('TITLE', 'New reply');
        }
        // TRANS: Page title for sending a new notice.
        return _m('TITLE','New notice');
    }

    protected function doPreparation()
    {
        foreach(array('inreplyto') as $opt) {
            if ($this->trimmed($opt)) {
                $this->formOpts[$opt] = $this->trimmed($opt);
            }
        }

        if ($this->int('inreplyto')) {
            // Throws exception if the inreplyto Notice is given but not found.
            $this->inreplyto = Notice::getByID($this->int('inreplyto'));
        }

        // Backwards compatibility for "share this" widget things.
        // If no 'content', use 'status_textarea'
        $this->formOpts['content'] = $this->trimmed('content') ?: $this->trimmed('status_textarea');
    }

    /**
     * This doPost saves a new notice, based on arguments
     *
     * If successful, will show the notice, or return an Ajax-y result.
     * If not, it will show an error message -- possibly Ajax-y.
     *
     * Also, if the notice input looks like a command, it will run the
     * command and show the results -- again, possibly ajaxy.
     *
     * @return void
     */
    protected function doPost()
    {
        assert($this->scoped instanceof Profile); // XXX: maybe an error instead...
        $user = $this->scoped->getUser();
        $content = $this->formOpts['content'];
        $options = array('source' => 'web');
        Event::handle('StartSaveNewNoticeWeb', array($this, $user, &$content, &$options));

        $upload = null;
        try {
            // throws exception on failure
            $upload = MediaFile::fromUpload('attach', $this->scoped);
            if (Event::handle('StartSaveNewNoticeAppendAttachment', array($this, $upload, &$content, &$options))) {
                $content .= ($content==='' ? '' : ' ') . $upload->shortUrl();
            }
            Event::handle('EndSaveNewNoticeAppendAttachment', array($this, $upload, &$content, &$options));

            // We could check content length here if the URL was added, but I'll just let it slide for now...

            $act->enclosures[] = $upload->getEnclosure();
        } catch (NoUploadedMediaException $e) {
            // simply no attached media to the new notice
            if (empty($content)) {
                // TRANS: Client error displayed trying to send a notice without content.
                throw new ClientException(_('No content!'));
            }
        }

        $inter = new CommandInterpreter();

        $cmd = $inter->handle_command($user, $content);

        if ($cmd) {
            if (postActiv::isAjax()) {
                $cmd->execute(new AjaxWebChannel($this));
            } else {
                $cmd->execute(new WebChannel($this));
            }
            return;
        }

        $act = new Activity();
        $act->verb = ActivityVerb::POST;
        $act->time = time();
        $act->actor = $this->scoped->asActivityObject();

        // Reject notice if it is too long (without the HTML)
        // This is done after MediaFile::fromUpload etc. just to act the same as the ApiStatusesUpdateAction
        if (Notice::contentTooLong($content)) {
            // TRANS: Client error displayed when the parameter "status" is missing.
            // TRANS: %d is the maximum number of character for a notice.
            throw new ClientException(sprintf(_m('That\'s too long. Maximum notice size is %d character.',
                                                 'That\'s too long. Maximum notice size is %d characters.',
                                                 Notice::maxContent()),
                                              Notice::maxContent()));
        }

        $act->context = new ActivityContext();

        if ($this->inreplyto instanceof Notice) {
            $act->context->replyToID = $this->inreplyto->getUri();
            $act->context->replyToUrl = $this->inreplyto->getUrl(true);  // maybe we don't have to send true here to force a URL?
        }

        if ($this->scoped->shareLocation()) {
            // use browser data if checked; otherwise profile data
            if ($this->arg('notice_data-geo')) {
                $locOptions = Notice::locationOptions($this->trimmed('lat'),
                                                      $this->trimmed('lon'),
                                                      $this->trimmed('location_id'),
                                                      $this->trimmed('location_ns'),
                                                      $this->scoped);
            } else {
                $locOptions = Notice::locationOptions(null,
                                                      null,
                                                      null,
                                                      null,
                                                      $this->scoped);
            }

            $act->context->location = Location::fromOptions($locOptions);
        }

        $content = $this->scoped->shortenLinks($content);

        // FIXME: Make sure NoticeTitle plugin gets a change to add the title to our activityobject!
        if (Event::handle('StartNoticeSaveWeb', array($this, $this->scoped, &$content, &$options))) {

            // FIXME: We should be able to get the attentions from common_render_content!
            // and maybe even directly save whether they're local or not!
            $act->context->attention = common_get_attentions($content, $this->scoped, $this->inreplyto);

            // $options gets filled with possible scoping settings
            ToSelector::fillActivity($this, $act, $options);

            $actobj = new ActivityObject();
            $actobj->type = ActivityObject::NOTE;
            $actobj->content = common_render_content($content, $this->scoped, $this->inreplyto);

            // Finally add the activity object to our activity
            $act->objects[] = $actobj;

            $this->stored = Notice::saveActivity($act, $this->scoped, $options);

            if ($upload instanceof MediaFile) {
                $upload->attachToNotice($this->stored);
            }

            Event::handle('EndNoticeSaveWeb', array($this, $this->stored));
        }

        Event::handle('EndSaveNewNoticeWeb', array($this, $user, &$content, &$options));

        if (!postActiv::isAjax()) {
            $url = common_local_url('shownotice', array('notice' => $this->stored->id));
            common_redirect($url, 303);
        }

        return _('Saved the notice!');
    }

    protected function showContent()
    {
        if ($this->getInfo() && $this->stored instanceof Notice) {
            $this->showNotice($this->stored);
        } elseif (!$this->getError()) {
            if (!postActiv::isAjax() && $this->inreplyto instanceof Notice) {
                $this->showNotice($this->inreplyto);
            }
            parent::showContent();
        }
    }

    /**
     * Output a notice
     *
     * Used to generate the notice code for Ajax results.
     *
     * @param Notice $notice Notice that was saved
     *
     * @return void
     */
    function showNotice(Notice $notice)
    {
        $nli = new NoticeListItem($notice, $this);
        $nli->show();
    }

    public function showNoticeForm()
    {
        // pass
    }
}

// END OF FILE
// =============================================================================
?>
