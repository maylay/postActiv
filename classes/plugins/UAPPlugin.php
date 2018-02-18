<?php
/* ============================================================================
 * Title: UAPPlugin
 * Superclass for UAP (Universal Ad Package) plugins
 *
 * postActiv:
 * the micro-blogging software
 *
 * Copyright:
 * Copyright (C) 2016-2018, Maiyannah Bishop
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
 * Superclass for UAP (Universal Ad Package) plugins
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Evan Prodromou
 * o Sarven Capadisli
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

/**
 * Abstract superclass for advertising plugins
 *
 * Plugins for showing ads should derive from this plugin.
 *
 * Outputs the following ad types (based on UAP):
 *
 * Medium Rectangle 300x250
 * Rectangle        180x150
 * Leaderboard      728x90
 * Wide Skyscraper  160x600
 */
abstract class UAPPlugin extends Plugin
{
    public $mediumRectangle = null;
    public $rectangle       = null;
    public $leaderboard     = null;
    public $wideSkyscraper  = null;

    /**
     * Output our dedicated stylesheet
     *
     * @param Action $action Action being shown
     *
     * @return boolean hook flag
     */
    public function onEndShowStylesheets(Action $action)
    {
        // XXX: allow override by theme
        $action->cssLink('css/uap.css', 'base', 'screen, projection, tv');
        return true;
    }

    /**
     * Add a medium rectangle ad at the beginning of sidebar
     *
     * @param Action $action Action being shown
     *
     * @return boolean hook flag
     */
    function onStartShowAside(Action $action)
    {
        if (!is_null($this->mediumRectangle)) {

            $action->elementStart('div',
                                  array('id' => 'ad_medium-rectangle',
                                        'class' => 'ad'));

            $this->showMediumRectangle($action);

            $action->elementEnd('div');
        }

        // XXX: Hack to force ads to show on single-notice pages

        if (!is_null($this->rectangle) &&
            $action->trimmed('action') == 'shownotice') {

            $action->elementStart('div', array('id' => 'aside_primary',
                                               'class' => 'aside'));

            if (Event::handle('StartShowSections', array($action))) {
                $action->showSections();
                Event::handle('EndShowSections', array($action));
            }

            $action->elementEnd('div');

            return false;
        }

        return true;
    }

    /**
     * Add a leaderboard in the header
     *
     * @param Action $action Action being shown
     *
     * @return boolean hook flag
     */

    function onEndShowHeader($action)
    {
        if (!is_null($this->leaderboard)) {
            $action->elementStart('div',
                                  array('id' => 'ad_leaderboard',
                                        'class' => 'ad'));
            $this->showLeaderboard($action);
            $action->elementEnd('div');
        }

        return true;
    }

    /**
     * Add a rectangle before aside sections
     *
     * @param Action $action Action being shown
     *
     * @return boolean hook flag
     */
    function onStartShowSections(Action $action)
    {
        if (!is_null($this->rectangle)) {
            $action->elementStart('div',
                                  array('id' => 'ad_rectangle',
                                        'class' => 'ad'));
            $this->showRectangle($action);
            $action->elementEnd('div');
        }

        return true;
    }

    /**
     * Add a wide skyscraper after the aside
     *
     * @param Action $action Action being shown
     *
     * @return boolean hook flag
     */
    function onEndShowAside(Action $action)
    {
        if (!is_null($this->wideSkyscraper)) {
            $action->elementStart('div',
                                  array('id' => 'ad_wide-skyscraper',
                                        'class' => 'ad'));

            $this->showWideSkyscraper($action);

            $action->elementEnd('div');
        }
        return true;
    }

    /**
     * Show a medium rectangle ad
     *
     * @param Action $action Action being shown
     *
     * @return void
     */
    abstract protected function showMediumRectangle($action);

    /**
     * Show a rectangle ad
     *
     * @param Action $action Action being shown
     *
     * @return void
     */
    abstract protected function showRectangle($action);

    /**
     * Show a wide skyscraper ad
     *
     * @param Action $action Action being shown
     *
     * @return void
     */
    abstract protected function showWideSkyscraper($action);

    /**
     * Show a leaderboard ad
     *
     * @param Action $action Action being shown
     *
     * @return void
     */
    abstract protected function showLeaderboard($action);
}

// END OF FILE
// ============================================================================
?>