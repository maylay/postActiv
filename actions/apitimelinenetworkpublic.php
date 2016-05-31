<?php
/***
 * postActiv - a fork of the gnuSocial microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   gnuSocial (C) 2015, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * API form of the network timeline.
 */

if (!defined('GNUSOCIAL')) { exit(1); }

class ApiTimelineNetworkPublicAction extends ApiTimelinePublicAction
{
    function title()
    {
        return sprintf(_("%s network public timeline"), common_config('site', 'name'));
    }

    protected function getStream()
    {
        if (!$this->scoped instanceof Profile && common_config('public', 'localonly')) {
            $this->clientError(_('Network wide public feed is not permitted without authorization'), 403);
        }
        return new NetworkPublicNoticeStream($this->scoped);
    }
}
