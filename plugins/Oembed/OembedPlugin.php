<?php
/***
 * postActiv - a fork of the GNU Social microblogging software
 * Copyright (C) 2016, Maiyannah Bishop <maiyannah@member.fsf.org>
 * Derived from code copyright various sources:
 *   GNU Social (C) 2013-2016, Free Software Foundation, Inc
 *   StatusNet (C) 2008-2011, StatusNet, Inc
 *
 * oEmbed plugin main class.  oEmbed allows remote images to be displayed
 * easily on our server despite being remote.
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
 * @category  Plugins
 * @package   postActiv
 * @author    Mikael Nordfeldth <mmn@hethane.se>
 * @author    Stephen Paul Weber <singpolyma@singpolyma.net>
 * @author    Hannes Mannerheim <h@nnesmannerhe.im>
 * @author    Maiyannah Bishop <maiyannah@member.fsf.org>
 * @copyright 2014-2016 Free Software Foundation, Inc.
 * @copyright 2016 Maiyannah Bishop
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @link      https://git.gnu.io/maiyannah/postActiv
 */

if (!defined('POSTACTIV')) { exit(1); }

// ============================================================================
// OembedPlugin class
//    extends Plugin
//
// Base class for the oEmbed plugin that does most of the heavy lifting to get
// and display representations for remote content.
class OembedPlugin extends Plugin
{
   // settings which can be set in config.php with addPlugin('Oembed', array('param'=>'value', ...));
   // WARNING, these are _regexps_ (slashes added later). Always escape your dots and end your strings
   public $domain_whitelist = array(       // hostname => service provider
                                    '^i\d*\.ytimg\.com$' => 'YouTube',
                                    '^i\d*\.vimeocdn\.com$' => 'Vimeo',
                                    );
   public $append_whitelist = array(); // fill this array as domain_whitelist to add more trusted sources
   public $check_whitelist  = false;    // security/abuse precaution

   protected $imgData = array();

   // ------------------------------------------------------------------------
   // OembedPlugin::initialize() function
   // Institiate the oEmbed plugin and set up the environment it needs for it.
   // Returns true if it initialized properly, the exception object if it
   // doesn't.
   public function initialize() {
      try {
         parent::initialize();
         $this->domain_whitelist = array_merge($this->domain_whitelist, $this->append_whitelist);
      } {catch exception $err) {
         return $err;
      }
      return true;
   }

   // ------------------------------------------------------------------------
   // OembedPlugin::onCheckSchema() function
   // The code executed on postActiv checking the database schema, which in
   // this case is to make sure we have the plugin table we need.
   // Returns true if it ran successfully, the exception object if it doesn't.
   public function onCheckSchema() {
      try {
         $schema = Schema::get();
         $schema->ensureTable('file_oembed', File_oembed::schemaDef());
      } catch (exception $err) {
         return $err;
      }
      return true;
   }

   // -------------------------------------------------------------------------
   // OembedPlugin::onRouterInitialized($m) function
   //   args: $m (URLMapper) = the router that was initialized.
   // This code executes when postActiv creates the page routing, and we hook
   // on this event to add our action handler for oEmbed.
   // Returns true if successful, the exception object if it isn't.
   public function onRouterInitialized(URLMapper $m) {
      try {
         $m->connect('main/oembed', array('action' => 'oembed'));
      } catch (exception $err) {
         return $err;
      }
      return true;
   }

   // --------------------------------------------------------------------------
   // OembedPlugin::onGetRemoteUrlMetadataFromDom($url, $dom, $metadata) function
   //    args: $url      = the remote URL we're looking at
   //          $dom      = the document we're getting metadata from
   //          $metadata = class representing the metadata
   // This event executes when postActiv encounters a remote URL we then decide
   // to interrogate for metadata.  oEmbed gloms onto it to see if we have an
   // oEmbed endpoint or image to try to represent in the post.
   // Returns true if successful, the exception object if it isn't.
   public function onGetRemoteUrlMetadataFromDom($url, DOMDocument $dom, stdClass &$metadata) {
      try {
         common_log(LOG_INFO, 'Trying to discover an oEmbed endpoint using link headers.');
         $api = oEmbedHelper::oEmbedEndpointFromHTML($dom);
         common_log(LOG_INFO, 'Found oEmbed API endpoint ' . $api . ' for URL ' . $url);
         $params = array(
            'maxwidth' => common_config('thumbnail', 'width'),
            'maxheight' => common_config('thumbnail', 'height'),
         );
         $metadata = oEmbedHelper::getOembedFrom($api, $url, $params);

         // Facebook just gives us javascript in its oembed html,
         // so use the content of the title element instead
         if(strpos($url,'https://www.facebook.com/') === 0) {
            $metadata->html = @$dom->getElementsByTagName('title')->item(0)->nodeValue;
         }

         // Wordpress sometimes also just gives us javascript, use og:description if it is available
         $xpath = new DomXpath($dom);
         $generatorNode = @$xpath->query('//meta[@name="generator"][1]')->item(0);
         if ($generatorNode instanceof DomElement) {
            // when wordpress only gives us javascript, the html stripped from tags
            // is the same as the title, so this helps us to identify this (common) case
            if(strpos($generatorNode->getAttribute('content'),'WordPress') === 0
               && trim(strip_tags($metadata->html)) == trim($metadata->title)) {
               $propertyNode = @$xpath->query('//meta[@property="og:description"][1]')->item(0);
               if ($propertyNode instanceof DomElement) { $metadata->html = $propertyNode->getAttribute('content'); }
            }
         }
      } catch (Exception $e) {
         // FIXME - make sure the error was because we couldn't get metadata, not something else! -mb
         common_log(LOG_INFO, 'Could not find an oEmbed endpoint using link headers, trying OpenGraph from HTML.');
         // Just ignore it!
         $metadata = OpenGraphHelper::ogFromHtml($dom);
      }

      if (isset($metadata->thumbnail_url)) {
         // sometimes sites serve the path, not the full URL, for images
         // let's "be liberal in what you accept from others"!
         // add protocol and host if the thumbnail_url starts with /
         if(substr($metadata->thumbnail_url,0,1) == '/') {
            try {
               $thumbnail_url_parsed = parse_url($metadata->url);
               $metadata->thumbnail_url = $thumbnail_url_parsed['scheme']."://".$thumbnail_url_parsed['host'].$metadata->thumbnail_url;
            } catch (exception $err) {
               common_log(LOG_WARNING, 'Failed parsing thumbnail URL in oEmbedPlugin::onGetRemoteUrlMetadataFromDom: ' . $err . '.');
               return $err;
            }
         }

         // some wordpress opengraph implementations sometimes return a white blank image
         // no need for us to save that!
         if($metadata->thumbnail_url == 'https://s0.wp.com/i/blank.jpg') {
            unset($metadata->thumbnail_url);
         }
         
         // FIXME: this is also true of locally-installed wordpress so we should watch out for that.
      }
      return true;
   }

   public function onEndShowHeadElements(Action $action) {
      switch ($action->getActionName()) {
         case 'attachment':
             $action->element('link',array('rel'=>'alternate',
                'type'=>'application/json+oembed',
                'href'=>common_local_url(
                    'oembed',
                    array(),
                    array('format'=>'json', 'url'=>
                        common_local_url('attachment',
                            array('attachment' => $action->attachment->id)))),
                'title'=>'oEmbed'),null);
            $action->element('link',array('rel'=>'alternate',
                'type'=>'text/xml+oembed',
                'href'=>common_local_url(
                    'oembed',
                    array(),
                    array('format'=>'xml','url'=>
                        common_local_url('attachment',
                            array('attachment' => $action->attachment->id)))),
                'title'=>'oEmbed'),null);
            break;
        case 'shownotice':
            if (!$action->notice->isLocal()) {
                break;
            }
            try {
                $action->element('link',array('rel'=>'alternate',
                    'type'=>'application/json+oembed',
                    'href'=>common_local_url(
                        'oembed',
                        array(),
                        array('format'=>'json','url'=>$action->notice->getUrl())),
                    'title'=>'oEmbed'),null);
                $action->element('link',array('rel'=>'alternate',
                    'type'=>'text/xml+oembed',
                    'href'=>common_local_url(
                        'oembed',
                        array(),
                        array('format'=>'xml','url'=>$action->notice->getUrl())),
                    'title'=>'oEmbed'),null);
            } catch (InvalidUrlException $e) {
                // The notice is probably a share or similar, which don't
                // have a representational URL of their own.
            }
            break;
        }

        return true;
    }

    public function onEndShowStylesheets(Action $action) {
        $action->cssLink($this->path('css/oembed.css'));
        return true;
    }

    /**
     * Save embedding information for a File, if applicable.
     *
     * Normally this event is called through File::saveNew()
     *
     * @param File   $file       The newly inserted File object.
     *
     * @return boolean success
     */
    public function onEndFileSaveNew(File $file)
    {
        $fo = File_oembed::getKV('file_id', $file->id);
        if ($fo instanceof File_oembed) {
            common_log(LOG_WARNING, "Strangely, a File_oembed object exists for new file {$file->id}", __FILE__);
            return true;
        }

        if (isset($file->mimetype)
            && (('text/html' === substr($file->mimetype, 0, 9)
            || 'application/xhtml+xml' === substr($file->mimetype, 0, 21)))) {

            try {
                $oembed_data = File_oembed::_getOembed($file->url);
                if ($oembed_data === false) {
                    throw new Exception('Did not get oEmbed data from URL');
                }
            } catch (Exception $e) {
                return true;
            }

            File_oembed::saveNew($oembed_data, $file->id);
        }
        return true;
    }

    public function onEndShowAttachmentLink(HTMLOutputter $out, File $file)
    {
        $oembed = File_oembed::getKV('file_id', $file->id);
        if (empty($oembed->author_name) && empty($oembed->provider)) {
            return true;
        }
        $out->elementStart('div', array('id'=>'oembed_info', 'class'=>'e-content'));
        if (!empty($oembed->author_name)) {
            $out->elementStart('div', 'fn vcard author');
            if (empty($oembed->author_url)) {
                $out->text($oembed->author_name);
            } else {
                $out->element('a', array('href' => $oembed->author_url,
                                         'class' => 'url'),
                                $oembed->author_name);
            }
        }
        if (!empty($oembed->provider)) {
            $out->elementStart('div', 'fn vcard');
            if (empty($oembed->provider_url)) {
                $out->text($oembed->provider);
            } else {
                $out->element('a', array('href' => $oembed->provider_url,
                                         'class' => 'url'),
                                $oembed->provider);
            }
        }
        $out->elementEnd('div');
    }

    public function onFileEnclosureMetadata(File $file, &$enclosure)
    {
        // Never treat generic HTML links as an enclosure type!
        // But if we have oEmbed info, we'll consider it golden.
        $oembed = File_oembed::getKV('file_id', $file->id);
        if (!$oembed instanceof File_oembed || !in_array($oembed->type, array('photo', 'video'))) {
            return true;
        }

        foreach (array('mimetype', 'url', 'title', 'modified', 'width', 'height') as $key) {
            if (isset($oembed->{$key}) && !empty($oembed->{$key})) {
                $enclosure->{$key} = $oembed->{$key};
            }
        }
        return true;
    }

    public function onStartShowAttachmentRepresentation(HTMLOutputter $out, File $file)
    {
        try {
            $oembed = File_oembed::getByFile($file);
        } catch (NoResultException $e) {
            return true;
        }

        // Show thumbnail as usual if it's a photo.
        if ($oembed->type === 'photo') {
            return true;
        }

        $out->elementStart('article', ['class'=>'h-entry oembed']);
        $out->elementStart('header');
        try  {
            $thumb = $file->getThumbnail(128, 128);
            $out->element('img', $thumb->getHtmlAttrs(['class'=>'u-photo oembed']));
            unset($thumb);
        } catch (Exception $e) {
            $out->element('div', ['class'=>'error'], $e->getMessage());
        }
        $out->elementStart('h5', ['class'=>'p-name oembed']);
        $out->element('a', ['class'=>'u-url', 'href'=>$file->getUrl()], common_strip_html($oembed->title));
        $out->elementEnd('h5');
        $out->elementStart('div', ['class'=>'p-author oembed']);
        if (!empty($oembed->author_name)) {
            // TRANS: text before the author name of oEmbed attachment representation
            // FIXME: The whole "By x from y" should be i18n because of different language constructions.
            $out->text(_('By '));
            $attrs = ['class'=>'h-card p-author'];
            if (!empty($oembed->author_url)) {
                $attrs['href'] = $oembed->author_url;
                $tag = 'a';
            } else {
                $tag = 'span';
            }
            $out->element($tag, $attrs, $oembed->author_name);
        }
        if (!empty($oembed->provider)) {
            // TRANS: text between the oEmbed author name and provider url
            // FIXME: The whole "By x from y" should be i18n because of different language constructions.
            $out->text(_(' from '));
            $attrs = ['class'=>'h-card'];
            if (!empty($oembed->provider_url)) {
                $attrs['href'] = $oembed->provider_url;
                $tag = 'a';
            } else {
                $tag = 'span';
            }
            $out->element($tag, $attrs, $oembed->provider);
        }
        $out->elementEnd('div');
        $out->elementEnd('header');
        $out->elementStart('div', ['class'=>'p-summary oembed']);
        $out->raw(common_purify($oembed->html));
        $out->elementEnd('div');
        $out->elementStart('footer');
        $out->elementEnd('footer');
        $out->elementEnd('article');

        return false;
    }

    public function onShowUnsupportedAttachmentRepresentation(HTMLOutputter $out, File $file)
    {
        try {
            $oembed = File_oembed::getByFile($file);
        } catch (NoResultException $e) {
            return true;
        }

        // the 'photo' type is shown through ordinary means, using StartShowAttachmentRepresentation!
        switch ($oembed->type) {
        case 'video':
        case 'link':
            if (!empty($oembed->html)
                    && (GNUsocial::isAjax() || common_config('attachments', 'show_html'))) {
                require_once INSTALLDIR.'/extlib/HTMLPurifier/HTMLPurifier.auto.php';
                $purifier = new HTMLPurifier();
                // FIXME: do we allow <object> and <embed> here? we did that when we used htmLawed, but I'm not sure anymore...
                $out->raw($purifier->purify($oembed->html));
            }
            return false;
            break;
        }

        return true;
    }

   // -------------------------------------------------------------------------
   // OembedPlugin::onCreateFileImageThumbnailSource($file, $imgPath, $media)
   //   public function
   //   args: $file = the file of the created thumbnail
   //         $imgPath = the path to the created thumbnail
   //         $media = media type the thumbnail was created for
   // Man that name is a mouthful.
   // This event executes when postActiv is creating a file thumbnail entry in 
   // the database.  We glom onto this to create proper information for oEmbed
   // object thumbnails.  Returns true if it succeeds (including non-action
   // states where it isn't oEmbed data, so it doesn't mess up the event handle
   // for other things hooked into it), or the exception if it fails.
   public function onCreateFileImageThumbnailSource(File $file, &$imgPath, $media=null)
   {
      // If we are on a private node, we won't do any remote calls (just as a precaution until
      // we can configure this from config.php for the private nodes)
      if (common_config('site', 'private')) { return true; }

      // All our remote Oembed images lack a local filename property in the File object
      if (!is_null($file->filename)) { return true; }

      try {
          // If we have proper oEmbed data, there should be an entry in the File_oembed
          // and File_thumbnail tables respectively. If not, we're not going to do anything.
          $file_oembed = File_oembed::getByFile($file);
          $thumbnail   = File_thumbnail::byFile($file);
      } catch (NoResultException $e) {
          // Not Oembed data, or at least nothing we either can or want to use.
          return true;
      }

      try {
          $this->storeRemoteFileThumbnail($thumbnail);
      } catch (AlreadyFulfilledException $e) {
          // aw yiss!
      }

      $imgPath = $thumbnail->getPath();

      return false;
   }

   // -------------------------------------------------------------------------
   // OembedPlugin::checkWhitelist($url) protected function
   //   args: $url = the URL being checked against the whitelist
   // A protected helper function that checks the whitelist configured in the
   // postActiv config.php if set, and sees if the passed url is from a provider
   // on the whitelist.  Returns the provider name if it is on the whitelist,
   // false if it isn't, and the exception if it fails the lookup.
   protected function checkWhitelist($url) {
      // If there is no whitelist present, return -1 for "no check made"
      if (!$this->check_whitelist) { return -1; }

      try {
         $host = parse_url($url, PHP_URL_HOST);
         foreach ($this->domain_whitelist as $regex => $provider) {
            if (preg_match("/$regex/", $host)) { return $provider; }
         }
      } catch (exception $err) {
         return $err;
      }

      common_log(LOG_DEBUG, 'Domain not in remote thumbnail source whitelist: '.$url);
      return false;
   }

   // -------------------------------------------------------------------------
   // OembedFunction::getRemoteFileSize($url)
   // Check the file size of a remote file using a HEAD request and checking
   // the content-length variable returned.  This isn't 100% foolproof but is
   // reliable enough for our purposes.  Returns the file size if it succeeds
   // or the exception if it fails.
   private function getRemoteFileSize($url) {
      if (!$url) {
         return false;
      }
      try {
         stream_context_set_default(array('http' => array('method' => 'HEAD')));
         $head = @get_headers($url,1);
         if (gettype($head)=="array") {
            $head = array_change_key_case($head);
            $size = isset($head['content-length']) ? $head['content-length'] : 0;

            if (!$size) {
               return false;
            }
         } else {
            return false;
         }
         return $size; // return formatted size
      } catch (Exception $err) {
         common_log(LOG_ERR, __CLASS__.': getRemoteFileSize on URL : '._ve($file->getUrl()).' threw exception: '.$err->getMessage());
         return false;
      }
   }

   // -------------------------------------------------------------------------
   // OembedPlugin::isRemoteImage($url) private function.
   // A private helper function that uses a CURL lookup to check the mime type
   // of a remote URL to see it it's an image.  Returns true if the remote URL 
   // is an image, or false otherwise.
   // FIXME: We should probably sanity-check the input to make sure it's a 
   // valid URL.
   private function isRemoteImage($url) {
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HEADER, TRUE);
      curl_setopt($ch, CURLOPT_NOBODY, TRUE);
      $headers = curl_exec($curl);
      $type    = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
      if (strpos($type, 'image') !== false) {
         return true;
      } else {
         return false;
      }
   }

   // -------------------------------------------------------------------------
   // OembedPlugin::getPHPUploadLimit() private function
   // An internal helper function that parses the php.ini file size limit from
   // the 'human-readable' shorthand into something we can use to test against
   // in conditionals.
   // Returns the php.ini upload limit in machine-readable format, or the 
   // exception if it fails.
   // FIXME: We could probably move this to a public utility library.
   private function getPHPUploadLimit() {
      $size = ini_get("upload_max_filesize");
      $suffix = substr($size, -1);
      $size = substr($size, 0, -1);
      switch(strtoupper($suffix)) {
      case 'P':
         $size *= 1024;
      case 'T':
         $size *= 1024;
      case 'G':
         $size *= 1024;
      case 'M':
         $size *= 1024;
      case 'K':
         $size *= 1024;
         break;
      }
      return $size;
   }

   // -------------------------------------------------------------------------
   // OembedPlugin::storeRemoteFileThumbnail($thumbnail) protected function
   //    args: $thumbnail = object containing the file thumbnail
   // Function to create and store a thumbnail representation of a remote image
   // Returns true if it succeeded, the exception if it fails, or false if it
   // is limited by system limits (ie the file is too large.)
   protected function storeRemoteFileThumbnail(File_thumbnail $thumbnail) {
      if (!empty($thumbnail->filename) && file_exists($thumbnail->getPath())) {
         throw new AlreadyFulfilledException(sprintf('A thumbnail seems to already exist for remote file with id==%u', $thumbnail->file_id));
      }

      $url = $thumbnail->getUrl();
      $this->checkWhitelist($url);

      try {
         $isImage = $this->isRemoteImage($url);
         if ($isImage==true) {
            $max_size  = $this->getPHPUploadLimit();
            $file_size = $this->getRemoteFileSize($url);
            if (($file_size!=false) & ($file_size > $max_size)) {
               common_debug("Went to store remote thumbnail of size " . $file_size . " but the upload limit is " . $max_size . " so we aborted.");
               return false;
            }
         }
      } catch (Exception $err) {
           common_debug("Could not determine size of remote image, aborted local storage.");
           return $err;
      }

      // First we download the file to memory and test whether it's actually an image file
      // FIXME: To support remote video/whatever files, this needs reworking.
      common_debug(sprintf('Downloading remote thumbnail for file id==%u with thumbnail URL: %s', $thumbnail->file_id, $url));
      $imgData = HTTPClient::quickGet($url);
      $info = @getimagesizefromstring($imgData);
      if ($info === false) {
         throw new UnsupportedMediaException(_('Remote file format was not identified as an image.'), $url);
      } elseif (!$info[0] || !$info[1]) {
         throw new UnsupportedMediaException(_('Image file had impossible geometry (0 width or height)'));
      }

      $ext = File::guessMimeExtension($info['mime']);

      try {
         // We'll trust sha256 (File::FILEHASH_ALG) not to have collision issues any time soon :)
         $filename = 'oembed-'.hash(File::FILEHASH_ALG, $imgData) . ".{$ext}";
         $fullpath = File_thumbnail::path($filename);
         // Write the file to disk. Throw Exception on failure
         if (!file_exists($fullpath) && file_put_contents($fullpath, $imgData) === false) {
            throw new ServerException(_('Could not write downloaded file to disk.'));
         }
      } catch {
         common_log(LOG_ERROR, "Went to write a thumbnail to disk in OembedPlugin::storeRemoteThumbnail but encountered error: ".$err);
         return $err;
      } finally {
         unset($imgData);
      }

      try {
         // Updated our database for the file record
         $orig = clone($thumbnail);
         $thumbnail->filename = $filename;
         $thumbnail->width = $info[0];    // array indexes documented on php.net:
         $thumbnail->height = $info[1];   // https://php.net/manual/en/function.getimagesize.php
         // Throws exception on failure.
         $thumbnail->updateWithKeys($orig);
      } catch (exception $err) {
         common_log(LOG_ERROR, "Went to write a thumbnail entry to the database in OembedPlugin::storeRemoteThumbnail but encountered error: ".$err);
         return $err;
      }
      return true;
   }

   // -------------------------------------------------------------------------
   // onPluginVersion($versions) function
   //   args: $versions - inherited from parent
   // Event raised when postActiv polls the plugin for information about it.
   // Creates a $versions array with the info that postActiv accesses for that
   // information.
   // Returns true if it execures successfully, the exception if it doesn't,
   // but if assigning an array fails, we uh, got issues.
   public function onPluginVersion(array &$versions) {
      try {
         $versions[] = array('name' => 'Oembed',
                             'version' => GNUSOCIAL_VERSION,
                             'author' => 'Mikael Nordfeldth',
                             'homepage' => 'http://gnu.io/',
                             'description' =>
                             // TRANS: Plugin description.
                             _m('Plugin for using and representing Oembed data.'));
      } catch (exception $err) {
         return $err;
      }
      return true;
   }
}
?>
