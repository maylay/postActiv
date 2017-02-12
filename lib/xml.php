<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * This file collates the two XML classes into a single file.  Previously
 * they were located at:
 *     xmlstringer.php
 *     xmloutputter.php
 *
 * XML stringer creates a XML file in memory, while XML outputter spits it out
 * for display elsewhere.
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
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
 * @category  Output
 * @package   postActiv
 * @author    Evan Prodromou <evan@status.net>
 * @author    Sarven Capadisli <csarven@status.net>
 * @author    Maiyannay Bishop <maiyannah@member.fsf.net>
 * @copyright 2016 Maiyannah Bishop
 * @copyright 2008 StatusNet, Inc.
 * @license   https://www.gnu.org/licenses/agpl.html
 * @link      https://git.gnu.io/maiyannah/postActiv
 *
 * @see       HTMLOutputter
 */

if (!defined('POSTACTIV')) { exit(1); }

class XMLOutputter
{
    /**
     * Wrapped XMLWriter object, which does most of the heavy lifting
     * for output.
     */

    var $xw = null;

    /**
     * Constructor
     *
     * Initializes the wrapped XMLWriter.
     *
     * @param string  $output URL for outputting, if null it defaults to stdout ('php://output')
     * @param boolean $indent Whether to indent output, default true
     */

    function __construct($output=null, $indent=null)
    {
        if (is_null($output)) {
            $output = 'php://output';
        }
        $this->xw = new XMLWriter();
        $this->xw->openURI($output);
        if(is_null($indent)) {
            $indent = common_config('site', 'indent');
        }
        $this->xw->setIndent($indent);
    }

    /**
     * Start a new XML document
     *
     * @param string $doc    document element
     * @param string $public public identifier
     * @param string $system system identifier
     *
     * @return void
     */

    function startXML($doc=null, $public=null, $system=null)
    {
        $this->xw->startDocument('1.0', 'UTF-8');
        if ($doc) {
            $this->xw->writeDTD($doc, $public, $system);
        }
    }

    /**
     * finish an XML document
     *
     * It's probably a bad idea to continue to use this object
     * after calling endXML().
     *
     * @return void
     */

    function endXML()
    {
        $this->xw->endDocument();
        $this->xw->flush();
    }

    /**
     * output an XML element
     *
     * Utility for outputting an XML element. A convenient wrapper
     * for a bunch of longer XMLWriter calls. This is best for
     * when an element doesn't have any sub-elements; if that's the
     * case, use elementStart() and elementEnd() instead.
     *
     * The $content element will be escaped for XML. If you need
     * raw output, use elementStart() and elementEnd() with a call
     * to raw() in the middle.
     *
     * If $attrs is a string instead of an array, it will be treated
     * as the class attribute of the element.
     *
     * @param string $tag     Element type or tagname
     * @param array  $attrs   Array of element attributes, as
     *                        key-value pairs
     * @param string $content string content of the element
     *
     * @return void
     */

    function element($tag, $attrs=null, $content=null)
    {
        $this->elementStart($tag, $attrs);
        if (!is_null($content)) {
            if (is_array($content)) {
               implode(" ",$content);
            }
            $this->xw->text($content);
        }
        $this->elementEnd($tag);
    }

    function elementNS(array $ns, $tag, $attrs=null, $content=null)
    {
        $this->elementStartNS($ns, $tag, $attrs);
        if (!is_null($content)) {
            $this->xw->text($content);
        }
        $this->elementEnd($tag);
    }

    /**
     * output a start tag for an element
     *
     * Mostly used for when an element has content that's
     * not a simple string.
     *
     * If $attrs is a string instead of an array, it will be treated
     * as the class attribute of the element.
     *
     * @param string $tag   Element type or tagname
     * @param array  $attrs Array of element attributes
     *
     * @return void
     */

    function elementStart($tag, $attrs=null)
    {
        $this->xw->startElement($tag);
        if (is_array($attrs)) {
            foreach ($attrs as $name => $value) {
                $this->xw->writeAttribute($name, $value);
            }
        } else if (is_string($attrs)) {
            $this->xw->writeAttribute('class', $attrs);
        }
    }

    function elementStartNS(array $ns, $tag, $attrs=null)
    {
        reset($ns); // array pointer to 0
        $uri = key($ns);
        $this->xw->startElementNS($ns[$uri], $tag, $uri);
        if (is_array($attrs)) {
            foreach ($attrs as $name => $value) {
                $this->xw->writeAttribute($name, $value);
            }
        } else if (is_string($attrs)) {
            $this->xw->writeAttribute('class', $attrs);
        }
    }

    /**
     * output an end tag for an element
     *
     * Used in conjunction with elementStart(). $tag param
     * should match the elementStart() param.
     *
     * For HTML 4 compatibility, this method will force
     * a full end element (</tag>) even if the element is
     * empty, except for a handful of exception tagnames.
     * This is a hack.
     *
     * @param string $tag Element type or tagname.
     *
     * @return void
     */

    function elementEnd($tag)
    {
        static $empty_tag = array('base', 'meta', 'link', 'hr',
                                  'br', 'param', 'img', 'area',
                                  'input', 'col', 'source');
        // XXX: check namespace
        if (in_array($tag, $empty_tag)) {
            $this->xw->endElement();
        } else {
            $this->xw->fullEndElement();
        }
    }

    /**
     * output plain text
     *
     * Text will be escaped. If you need it not to be,
     * use raw() instead.
     *
     * @param string $txt Text to output.
     *
     * @return void
     */

    function text($txt)
    {
        $this->xw->text($txt);
    }

    /**
     * output raw xml
     *
     * This will spit out its argument verbatim -- no escaping is
     * done.
     *
     * @param string $xml XML to output.
     *
     * @return void
     */

    function raw($xml)
    {
        $this->xw->writeRaw($xml);
    }

    /**
     * output a comment
     *
     * @param string $txt text of the comment
     *
     * @return void
     */

    function comment($txt)
    {
        $this->xw->writeComment($txt);
    }

    /**
     * Flush output buffers
     *
     * @return void
     */

    function flush()
    {
        $this->xw->flush();
    }
}

class XMLStringer extends XMLOutputter
{
    function __construct($indent=false)
    {
        $this->xw = new XMLWriter();
        $this->xw->openMemory();
        $this->xw->setIndent($indent);
    }

    function getString()
    {
        return $this->xw->outputMemory();
    }

    // utility for quickly creating XML-strings

    static function estring($tag, $attrs=null, $content=null)
    {
        $xs = new XMLStringer();
        $xs->element($tag, $attrs, $content);
        return $xs->getString();
    }
}
?>