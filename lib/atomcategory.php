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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * ----------------------------------------------------------------------------
 * PHP version 5
 *
 * @category  Feed
 * @package   postActiv
 * @author    Evan Prodromou
 * @author    Zach Copley
 * @author    Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright 2010-2012 StatusNet, Inc.
 * @copyright 2012-2016 Free Software Foundation, Inc
 * @copyright 2016 Maiyannah Bishop
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      http://www.postactiv.com/
 */

if (!defined('POSTACTIV')) { exit(1); }

class AtomCategory
{
    public $term;
    public $scheme;
    public $label;

    function __construct($element=null)
    {
        if ($element && $element->attributes) {
            $this->term = $this->extract($element, 'term');
            $this->scheme = $this->extract($element, 'scheme');
            $this->label = $this->extract($element, 'label');
        }
    }

    protected function extract($element, $attrib)
    {
        $node = $element->attributes->getNamedItemNS(Activity::ATOM, $attrib);
        if ($node) {
            return trim($node->textContent);
        }
        $node = $element->attributes->getNamedItem($attrib);
        if ($node) {
            return trim($node->textContent);
        }
        return null;
    }

    function asString()
    {
        $xs = new XMLStringer();
        $this->outputTo($xs);
        return $xs->getString();
    }

    function outputTo($xo)
    {
        $attribs = array();
        if ($this->term !== null) {
            $attribs['term'] = $this->term;
        }
        if ($this->scheme !== null) {
            $attribs['scheme'] = $this->scheme;
        }
        if ($this->label !== null) {
            $attribs['label'] = $this->label;
        }
        $xo->element('category', $attribs);
    }
}
?>