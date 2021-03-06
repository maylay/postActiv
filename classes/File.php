<?php
/* ============================================================================
 * Title: File
 * Table Definition for file
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
 * A superclass containing the database representation of a file attatchment
 * and the related interfaces.
 *
 * Basically, this is things attached to a post.  One thing to note here is that
 * it might not represent an actual attachment like an image or movie, it can
 * also represent the metadata for a hyperlink to another website.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 * o Robin Millette <robin@millette.info>
 * o Evan Prodromou
 * o Zach Copley
 * o Craig Andres <candrews@integralblue.com>
 * o Brenda Wallace <shiny@cpan.org>
 * o Brett Taylor <brett@webfroot.co.nz>
 * o Brion Vibber <brion@pobox.com>
 * o Nick Holliday <n.g.holliday@gmail.com>
 * o Siebrand Mazeland <s.mazeland@xs4all.nl>
 * o Mikael Nordfeldth <mmn@hethane.se>
 * o Jean Baptiste Favre <github@jbfavre.org>
 * o Hannes Mannerheim <h@nnesmannerhe.im>
 * o Stephen Paul Weber <singpolyma@singpolyma.net>
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

// ============================================================================
// Class: File
// A superclass containing the database representation of a file attatchment
// and the related interfaces.
//
// Property:
// o __table   - 'file';                            // table name
// o id        - int(4)  primary_key not_null
// o urlhash   - varchar(64)  unique_key
// o url       - text
// o filehash  - varchar(64)     indexed
// o mimetype  - varchar(50)
// o size      - int(4)
// o title     - text()
// o date      - int(4)
// o protected - int(4)
// o filename  - text()
// o width     - int(4)
// o height    - int(4)
// o modified  - timestamp()   not_null default_CURRENT_TIMESTAMP
class File extends Managed_DataObject {
    public $__table = 'file';                            // table name
    public $id;                              // int(4)  primary_key not_null
    public $urlhash;                         // varchar(64)  unique_key
    public $url;                             // text
    public $filehash;                        // varchar(64)     indexed
    public $mimetype;                        // varchar(50)
    public $size;                            // int(4)
    public $title;                           // text()
    public $date;                            // int(4)
    public $protected;                       // int(4)
    public $filename;                        // text()
    public $width;                           // int(4)
    public $height;                          // int(4)
    public $modified;                        // timestamp()   not_null default_CURRENT_TIMESTAMP

    const URLHASH_ALG = 'sha256';
    const FILEHASH_ALG = 'sha256';


   // -------------------------------------------------------------------------
   // Function: schemaDef
   // Returns an array representing the database schema this class is 
   // interfacing with.
   public static function schemaDef() {
      return array(
         'fields' => array(
            'id' => array('type' => 'serial', 'not null' => true),
            'urlhash' => array('type' => 'varchar', 'length' => 64, 'not null' => true, 'description' => 'sha256 of destination URL (url field)'),
            'url' => array('type' => 'text', 'description' => 'destination URL after following possible redirections'),
            'filehash' => array('type' => 'varchar', 'length' => 64, 'not null' => false, 'description' => 'sha256 of the file contents, only for locally stored files of course'),
            'mimetype' => array('type' => 'varchar', 'length' => 50, 'description' => 'mime type of resource'),
            'size' => array('type' => 'int', 'description' => 'size of resource when available'),
            'title' => array('type' => 'text', 'description' => 'title of resource when available'),
            'date' => array('type' => 'int', 'description' => 'date of resource according to http query'),
            'protected' => array('type' => 'int', 'description' => 'true when URL is private (needs login)'),
            'filename' => array('type' => 'text', 'description' => 'if file is stored locally (too) this is the filename'),
            'width' => array('type' => 'int', 'description' => 'width in pixels, if it can be described as such and data is available'),
            'height' => array('type' => 'int', 'description' => 'height in pixels, if it can be described as such and data is available'),
            'modified' => array('type' => 'timestamp', 'not null' => true, 'description' => 'date this record was modified'),),
         'primary key' => array('id'),
         'unique keys' => array(
            'file_urlhash_key' => array('urlhash'),),
         'indexes' => array(
            'file_filehash_idx' => array('filehash'), ),);
    }


   // -------------------------------------------------------------------------
   // Function: isProtected
   // Returns true/false whether the URL is considered "protected".
   //
   // FIXME: un-hardcode this, or at the very least, make it configurable.
   public static function isProtected($url) {
      $protected_urls_exps = array(
         'https://www.facebook.com/login.php',
         common_path('main/login'));
      foreach ($protected_urls_exps as $protected_url_exp) {
         if (preg_match('!^'.preg_quote($protected_url_exp).'(.*)$!i', $url) === 1) {
            return true;
         }
      }
      return false;
   }


   // -------------------------------------------------------------------------
   // Function: saveNew
   // Save a new file record.
   //
   // Parameters:
   // o array $redir_data lookup data eg from File_redirection::where()
   // o string $given_url
   //
   // Returns:
   // o File class representing the saved file
   //
   // FIXME: we should probably give at least some debug-level feedback if we
   // receive a link to a file that is our own allegedly, but we do not have
   // the file available locally.
   public static function saveNew(array $redir_data, $given_url) {
      $file = null;
      try {
         // I don't know why we have to keep doing this but we run a last check to avoid
         // uniqueness bugs.
         $file = File::getByUrl($given_url);
         return $file;
      } catch (NoResultException $e) {
         // We don't have the file's URL since before, so let's continue.
      }

      // if the given url is an local attachment url and the id already exists, don't
      // save a new file record. This should never happen, but let's make it foolproof
      // FIXME: how about attachments servers?
      $u = parse_url($given_url);
      if (isset($u['host']) && $u['host'] === common_config('site', 'server')) {
         $r = Router::get();
         // Skip the / in the beginning or $r->map won't match
         try {
            $args = $r->map(mb_substr($u['path'], 1));
            if ($args['action'] === 'attachment') {
               try {
                  // $args['attachment'] should always be set if action===attachment, given our routing rules
                  $file = File::getByID($args['attachment']);
                  return $file;
               } catch (EmptyPkeyValueException $e) {
                  // ...but $args['attachment'] can also be 0...
               } catch (NoResultException $e) {
                  // apparently this link goes to us, but is _not_ an existing attachment (File) ID?
               }
            }
         } catch (Exception $e) {
            // Some other exception was thrown from $r->map, likely a
            // ClientException (404) because of some malformed link to
            // our own instance. It's still a valid URL however, so we
            // won't abort anything... I noticed this when linking:
            // https://social.umeahackerspace.se/mmn/foaf' (notice the
            // apostrophe in the end, making it unrecognizable for our
            // URL routing.
            // That specific issue (the apostrophe being part of a link
            // is something that may or may not have been fixed since,
            // in lib/util.php in common_replace_urls_callback().
         }
      }

      $file = new File;
      $file->url = $given_url;
      if (!empty($redir_data['protected'])) $file->protected = $redir_data['protected'];
      if (!empty($redir_data['title'])) $file->title = $redir_data['title'];
      if (!empty($redir_data['type'])) $file->mimetype = $redir_data['type'];
      if (!empty($redir_data['size'])) $file->size = intval($redir_data['size']);
      if (isset($redir_data['time']) && $redir_data['time'] > 0) $file->date = intval($redir_data['time']);
      $file->saveFile();
      return $file;
   }


   // -------------------------------------------------------------------------
   // Function: saveFile
   // This slightly-misleadingly named function saves the File record to the
   // database, for actually creating a new file properly, see saveNew.
   public function saveFile() {
      $this->urlhash = self::hashurl($this->url);
      if (!Event::handle('StartFileSaveNew', array(&$this))) {
         throw new ServerException('File not saved due to an aborted StartFileSaveNew event.');
      }
      $this->id = $this->insert();
      if ($this->id === false) {
         throw new ServerException('File/URL metadata could not be saved to the database.');
      }
      Event::handle('EndFileSaveNew', array($this));
   }


   // -------------------------------------------------------------------------
   // Function: processNew
   // Go look at a URL and possibly save data about it if it's new:
   // o follow redirect chains and store them in file_redirection
   // o if a thumbnail is available, save it in file_thumbnail
   // o save file record with basic info
   // o optionally save a file_to_post record
   // o return the File object with the full reference
   //
   // Parameters:
   // o string $given_url the URL we're looking at
   // o Notice $notice (optional)
   // o bool $followRedirects defaults to true
   //
   // Returns:
   // o mixed File on success, -1 on some errors
   //
   // @throws ServerException on failure
   public static function processNew($given_url, Notice $notice=null, $followRedirects=true) {
      if (empty($given_url)) {
         throw new ServerException('No given URL to process');
      }

      $given_url = File_redirection::_canonUrl($given_url);
      if (empty($given_url)) {
         throw new ServerException('No canonical URL from given URL to process');
      }

      $redir = File_redirection::where($given_url);
      $file = $redir->getFile();
      if (!$file instanceof File || empty($file->id)) {
         // This should not happen
         throw new ServerException('URL processing failed without new File object');
      }
      if ($notice instanceof Notice) {
         File_to_post::processNew($file, $notice);
      }

      return $file;
   }


   // ------------------------------------------------------------------------
   // Function: respectsQuota
   // Tests whether a new upload respects the various quotas set in the
   // instance configuration.
   //
   // Parameters:
   // o Profile scoped
   // o fileSize
   public static function respectsQuota(Profile $scoped, $fileSize) {
      if ($fileSize > common_config('attachments', 'file_quota')) {
          // TRANS: Message used to be inserted as %2$s in  the text "No file may
          // TRANS: be larger than %1$d byte and the file you sent was %2$s.".
          // TRANS: %1$d is the number of bytes of an uploaded file.
          $fileSizeText = sprintf(_m('%1$d byte','%1$d bytes',$fileSize),$fileSize);

          $fileQuota = common_config('attachments', 'file_quota');
          // TRANS: Message given if an upload is larger than the configured maximum.
          // TRANS: %1$d (used for plural) is the byte limit for uploads,
          // TRANS: %2$s is the proper form of "n bytes". This is the only ways to have
          // TRANS: gettext support multiple plurals in the same message, unfortunately...
          throw new ClientException(
                   sprintf(_m('No file may be larger than %1$d byte and the file you sent was %2$s. Try to upload a smaller version.',
                              'No file may be larger than %1$d bytes and the file you sent was %2$s. Try to upload a smaller version.',
                              $fileQuota),
                    $fileQuota, $fileSizeText));
      }

      $file = new File;
      $query = "select sum(size) as total from file join file_to_post on file_to_post.file_id = file.id join notice on file_to_post.post_id = notice.id where profile_id = {$scoped->id} and file.url like '%/notice/%/file'";
      $file->query($query);
      $file->fetch();

      $total = $file->total + $fileSize;
      if ($total > common_config('attachments', 'user_quota')) {
          // TRANS: Message given if an upload would exceed user quota.
          // TRANS: %d (number) is the user quota in bytes and is used for plural.
          throw new ClientException(
                    sprintf(_m('A file this large would exceed your user quota of %d byte.',
                              'A file this large would exceed your user quota of %d bytes.',
                              common_config('attachments', 'user_quota')),
                    common_config('attachments', 'user_quota')));
      }
      $query .= ' AND EXTRACT(month FROM file.modified) = EXTRACT(month FROM now()) and EXTRACT(year FROM file.modified) = EXTRACT(year FROM now())';
      $file->query($query);
      $file->fetch();
      $total = $file->total + $fileSize;
      if ($total > common_config('attachments', 'monthly_quota')) {
         // TRANS: Message given id an upload would exceed a user's monthly quota.
         // TRANS: $d (number) is the monthly user quota in bytes and is used for plural.
         throw new ClientException(
                    sprintf(_m('A file this large would exceed your monthly quota of %d byte.',
                              'A file this large would exceed your monthly quota of %d bytes.',
                              common_config('attachments', 'monthly_quota')),
                    common_config('attachments', 'monthly_quota')));
      }
      return true;
   }


   // -------------------------------------------------------------------------
   // Function: getFilename
   // Returns the filename for the file represented by this File object.
   public function getFilename() {
      return self::tryFilename($this->filename);
   }


   // -------------------------------------------------------------------------
   // Function: getSize
   // Returns the filesize as reported by the system for the file.
   public function getSize() {
      return intval($this->size);
   }


   // -------------------------------------------------------------------------
   // Function: filename
   // Construct the filename for a new File object.  Returns the constructed
   // filename.
   static function filename(Profile $profile, $origname, $mimetype) {
      $ext = self::guessMimeExtension($mimetype, $origname);

      // Normalize and make the original filename more URL friendly.
      $origname = basename($origname, ".$ext");
      if (class_exists('Normalizer')) {
            // http://php.net/manual/en/class.normalizer.php
            // http://www.unicode.org/reports/tr15/
            $origname = Normalizer::normalize($origname, Normalizer::FORM_KC);
      }
      $origname = preg_replace('/[^A-Za-z0-9\.\_]/', '_', $origname);
      $nickname = $profile->getNickname();
      $datestamp = strftime('%Y%m%d', time());
      do {
         // generate new random strings until we don't run into a filename collision.
         $random = strtolower(common_confirmation_code(16));
         $filename = "$nickname-$datestamp-$origname-$random.$ext";
      } while (file_exists(self::path($filename)));
      return $filename;
   }

   // -------------------------------------------------------------------------
   // Function: guessMimeExtension
   // Attempt to determine the mime extension for the file represented by this
   // File object.  Returns the match on success.
   //
   // Parameters:
   // o $mimetype - The mimetype we've discovered for this file.
   // o $filename - An optional filename which we can use on failure.
   static function guessMimeExtension($mimetype, $filename=null) {
      try {
            // first see if we know the extension for our mimetype
            $ext = common_supported_mime_to_ext($mimetype);
            // we do, so use it!
            return $ext;
      } catch (UnknownMimeExtensionException $e) {
         // We don't know the extension for this mimetype, but let's guess.

         // If we can't recognize the extension from the MIME, we try
         // to guess based on filename, if one was supplied.
         if (!is_null($filename) && preg_match('/^.+\.([A-Za-z0-9]+)$/', $filename, $matches)) {
            // we matched on a file extension, so let's see if it means something.
            $ext = mb_strtolower($matches[1]);

            $blacklist = common_config('attachments', 'extblacklist');
            // If we got an extension from $filename we want to check if it's in a blacklist
            // so we avoid people uploading .php files etc.
            if (array_key_exists($ext, $blacklist)) {
               if (!is_string($blacklist[$ext])) {
                  // we don't have a safe replacement extension
                  throw new ClientException(_('Blacklisted file extension.'));
               }
               common_debug('Found replaced extension for filename '._ve($filename).': '._ve($ext));

               // return a safe replacement extension ('php' => 'phps' for example)
               return $blacklist[$ext];
            }
            // the attachment extension based on its filename was not blacklisted so it's ok to use it
            return $ext;
         }
      } catch (Exception $e) {
         common_log(LOG_INFO, 'Problem when figuring out extension for mimetype: '._ve($e));
      }

      // If nothing else has given us a result, try to extract it from
      // the mimetype value (this turns .jpg to .jpeg for example...)
      $matches = array();
      // FIXME: try to build a regexp that will get jpeg from image/jpeg as well as json from application/jrd+json
      if (!preg_match('/\/([a-z0-9]+)/', mb_strtolower($mimetype), $matches)) {
         throw new Exception('Malformed mimetype: '.$mimetype);
      }
      return mb_strtolower($matches[1]);
   }

   // -------------------------------------------------------------------------
   // Function: validFilename
   // Returns true/false whether the filename is a valid Linux filename.
   static function validFilename($filename) {
      return preg_match('/^[A-Za-z0-9._-]+$/', $filename);
   }


   // -------------------------------------------------------------------------
   // Function: tryFilename
   // Sees if a given $filename is a valid filename - raises
   // InvalidFilenameException on failure, true on success.
   //
   // FIXME: since this is being called elsewhere, shouldn't this return false
   // on failure?
   static function tryFilename($filename) {
      if (!self::validFilename($filename)) {
         throw new InvalidFilenameException($filename);
      }
      // if successful, return the filename for easy if-statementing
      return $filename;
   }


   // -------------------------------------------------------------------------
   // Function: path
   // Constructs the path for a given $filename based on site configuration.
   //
   // Error States:
   // o ClientException on invalid filename
   static function path($filename) {
      self::tryFilename($filename);
      $dir = common_config('attachments', 'dir');
      if (!in_array($dir[mb_strlen($dir)-1], ['/', '\\'])) {
         $dir .= DIRECTORY_SEPARATOR;
      }
      return $dir . $filename;
   }


   // -------------------------------------------------------------------------
   // Function: url
   // Constructs the URL for a file entry.
   static function url($filename) {
      self::tryFilename($filename);
      if (common_config('site','private')) {
         return common_local_url('getfile', array('filename' => $filename));
      }

      if (postActiv::useHTTPS()) {
         $sslserver = common_config('attachments', 'sslserver');
         if (empty($sslserver)) {
            // XXX: this assumes that background dir == site dir + /file/
            // not true if there's another server
            if (is_string(common_config('site', 'sslserver')) &&
                    mb_strlen(common_config('site', 'sslserver')) > 0) {
                    $server = common_config('site', 'sslserver');
            } else if (common_config('site', 'server')) {
                    $server = common_config('site', 'server');
            }
            $path = common_config('site', 'path') . '/file/';
         } else {
            $server = $sslserver;
            $path   = common_config('attachments', 'sslpath');
            if (empty($path)) {
               $path = common_config('attachments', 'path');
            }
         }
         $protocol = 'https';
      } else {
         $path = common_config('attachments', 'path');
         $server = common_config('attachments', 'server');
         if (empty($server)) {
            $server = common_config('site', 'server');
         }
         $ssl = common_config('attachments', 'ssl');
         $protocol = ($ssl) ? 'https' : 'http';
      }

      if ($path[strlen($path)-1] != '/') {
         $path .= '/';
      }

      if ($path[0] != '/') {
         $path = '/'.$path;
      }

      return $protocol.'://'.$server.$path.$filename;
   }


   static $_enclosures = array();


   // -------------------------------------------------------------------------
   // Function: getEnclosure
   // Return the mime enclosure for a file.
   function getEnclosure(){
      if (isset(self::$_enclosures[$this->getID()])) {
         return self::$_enclosures[$this->getID()];
      }

      $enclosure = (object) array();
      foreach (array('title', 'url', 'date', 'modified', 'size', 'mimetype', 'width', 'height') as $key) {
         if ($this->$key !== '') {
            $enclosure->$key = $this->$key;
         }
      }

      $needMoreMetadataMimetypes = array(null, 'application/xhtml+xml', 'text/html');

      if (!isset($this->filename) && in_array(common_bare_mime($enclosure->mimetype), $needMoreMetadataMimetypes)) {
         // This fetches enclosure metadata for non-local links with unset/HTML mimetypes,
         // which may be enriched through oEmbed or similar (implemented as plugins)
         Event::handle('FileEnclosureMetadata', array($this, &$enclosure));
      }
      if (empty($enclosure->mimetype)) {
         // This means we either don't know what it is, so it can't
         // be shown as an enclosure, or it is an HTML link which
         // does not link to a resource with further metadata.
         throw new ServerException('Unknown enclosure mimetype, not enough metadata');
      }

      self::$_enclosures[$this->getID()] = $enclosure;
      return $enclosure;
   }


   // ------------------------------------------------------------------------
   // Function: hasThumbnail
   // Returns true/false whether this attachment has a thumbnail on record.
   public function hasThumbnail() {
      try {
         $this->getThumbnail();
      } catch (Exception $e) {
         return false;
      }
      return true;
   }


   // ------------------------------------------------------------------------
   // Function: getThumbnail
   // Get the attachment's thumbnail record, if any.
   // Make sure you supply proper 'int' typed variables (or null).
   //
   // Parameters:
   // o integer width        - Max width of thumbnail in pixels. 
   //                          (if null, use common_config values)
   // o integer height       - Max height of thumbnail in pixels. 
   //                          (if null, square-crop to $width)
   // o boolean crop         - Crop to the max-values' aspect ratio
   // o boolean $force_still - Don't allow fallback to showing original 
   //                          (such as animated GIF)
   // o mixed $upscale       - Whether or not to scale smaller images up to 
   //                          larger thumbnail sizes. (null = site default)
   //
   // Returns:
   // o File_thumbnail object representing the thumbnail
   //
   // Error States:
   // o UseFileAsThumbnailException - if the file is considered an image itself and should be itself as thumbnail
   // o UnsupportedMediaException   - if, despite trying, we can't understand how to make a thumbnail for this format
   // o ServerException             - on various other errors
   public function getThumbnail($width=null, $height=null, $crop=false, $force_still=true, $upscale=null) {
      // Get some more information about this file through our ImageFile class
      $image = ImageFile::fromFileObject($this);
      if ($image->animated && !common_config('thumbnail', 'animated')) {
         // null  means "always use file as thumbnail"
         // false means you get choice between frozen frame or original when calling getThumbnail
         if (is_null(common_config('thumbnail', 'animated')) || !$force_still) {
            try {
               // remote files with animated GIFs as thumbnails will match this
               return File_thumbnail::byFile($this);
            } catch (NoResultException $e) {
               // and if it's not a remote file, it'll be safe to use the locally stored File
               throw new UseFileAsThumbnailException($this);
            }
         }
      }
      return $image->getFileThumbnail($width, $height, $crop,
                                      !is_null($upscale) ? $upscale : common_config('thumbnail', 'upscale'));
   }


   // -------------------------------------------------------------------------
   // Function: getPath
   // Returns the local filesystem path to a file we have stored locally.
   public function getPath() {
      $filepath = self::path($this->filename);
      if (!file_exists($filepath)) {
         throw new FileNotFoundException($filepath);
      }
      return $filepath;
   }


   // -------------------------------------------------------------------------
   // Function: getAttachmentUrl
   // Returns the local URL of an attachment - obviously, this needs to be a
   // locally-stored one but this won't be checked here.
   public function getAttachmentUrl() {
      return common_local_url('attachment', array('attachment'=>$this->getID()));
   }


   // -------------------------------------------------------------------------
   // Function: getUrl
   // Returns the URL of the File object - the filename if it's local,
   // otherwise just the stored URL.
   //
   // Parameters:
   // o mixed  $use_local - true means require local, null means prefer local,
   //   false means use whatever is stored
   public function getUrl($use_local=null) {
        if ($use_local !== false) {
            if (is_string($this->filename) || !empty($this->filename)) {
                // A locally stored file, so let's generate a URL for our instance.
                return self::url($this->getFilename());
            }
            if ($use_local) {
                // if the file wasn't stored locally (has filename) and we require a local URL
                throw new FileNotStoredLocallyException($this);
            }
        }


        // No local filename available, return the URL we have stored
        return $this->url;
   }


   // -------------------------------------------------------------------------
   // Function: getByUrl
   // Retrieve a File object by looking up its URL.
   //
   // Parameters:
   // o string url
   static public function getByUrl($url) {
        $file = new File();
        $file->urlhash = self::hashurl($url);
        if (!$file->find(true)) {
            throw new NoResultException($file);
        }
        return $file;
   }

   // -------------------------------------------------------------------------
   // Function: getByHash
   // Retrieve a File object by looking up its hash.
   //
   // Parameters:
   // o string $hashstr - String of (preferrably lower case) hexadecimal characters, same as result of 'hash_file(...)'
   static public function getByHash($hashstr) {
      $file = new File();
      $file->filehash = strtolower($hashstr);
      if (!$file->find(true)) {
         throw new NoResultException($file);
      }
      return $file;
   }


   // ------------------------------------------------------------------------
   // Function: updateUrl
   // Update an attachment - note, this will throw an exception if it hasn't
   // changed, which may not be expected behaviour in all control paths.
   public function updateUrl($url) {
      $file = File::getKV('urlhash', self::hashurl($url));
      if ($file instanceof File) {
         throw new ServerException('URL already exists in DB');
      }
      $sql = 'UPDATE %1$s SET urlhash=%2$s, url=%3$s WHERE urlhash=%4$s;';
      $result = $this->query(sprintf($sql, $this->tableName(),
                                           $this->_quote((string)self::hashurl($url)),
                                           $this->_quote((string)$url),
                                           $this->_quote((string)$this->urlhash)));
      if ($result === false) {
         common_log_db_error($this, 'UPDATE', __FILE__);
         throw new ServerException("Could not UPDATE {$this->tableName()}.url");
      }
      return $result;
   }


   // -------------------------------------------------------------------------
   // Function: blowCache
   // Blow the cache of notices that link to this URL
   //
   // Parameters:
   // o boolean $last Whether to blow the "last" cache too
   //
   // Returns:
   // o void
   function blowCache($last=false) {
      self::blow('file:notice-ids:%s', $this->id);
      if ($last) {
         self::blow('file:notice-ids:%s;last', $this->id);
      }
      self::blow('file:notice-count:%d', $this->id);
    }


   // -------------------------------------------------------------------------
   // Function: stream
   // Stream of notices linking to this URL or attachment
   //
   // o integer $offset   Offset to show; default is 0
   // o integer $limit    Limit of notices to show
   // o integer $since_id Since this notice
   // o integer $max_id   Before this notice
   //
   // Returns:
   // o array ids of notices that link to this file
   //
   // FIXME: Try to get the Profile::current() here in some other way to avoid mixing
   // the current session user with possibly background/queue processing.
   function stream($offset=0, $limit=NOTICES_PER_PAGE, $since_id=0, $max_id=0) {
        $stream = new FileNoticeStream($this, Profile::current());
        return $stream->getNotices($offset, $limit, $since_id, $max_id);
    }


   // -------------------------------------------------------------------------
   // Function: noticeCount
   // Returns how many notices use this attachment.  Good for finding orphaned
   // files.
   function noticeCount() {
      $cacheKey = sprintf('file:notice-count:%d', $this->id);
      $count = self::cacheGet($cacheKey);
      if ($count === false) {
         $f2p = new File_to_post();
         $f2p->file_id = $this->id;
         $count = $f2p->count();
         self::cacheSet($cacheKey, $count);
      }
      return $count;
   }


   // -------------------------------------------------------------------------
   // Function: isLocal
   // Returns whether we have a local copy of this file in the filesystem.
   public function isLocal() {
      return !empty($this->filename);
   }


   // -------------------------------------------------------------------------
   // Function: delete
   // Remove an attachment entry from the DB and the associated file if stored
   // locally.  Also cleans out thumbnails in the case of visual media.
   public function delete($useWhere=false) {
      // Delete the file, if it exists locally
      if (!empty($this->filename) && file_exists(self::path($this->filename))) {
         $deleted = @unlink(self::path($this->filename));
         if (!$deleted) {
            common_log(LOG_ERR, sprintf('Could not unlink existing file: "%s"', self::path($this->filename)));
         }
      }

      // Clear out related things in the database and filesystem, such as thumbnails
      if (Event::handle('FileDeleteRelated', array($this))) {
         $thumbs = new File_thumbnail();
         $thumbs->file_id = $this->id;
         if ($thumbs->find()) {
            while ($thumbs->fetch()) {
               $thumbs->delete();
            }
         }

         $f2p = new File_to_post();
         $f2p->file_id = $this->id;
         if ($f2p->find()) {
            while ($f2p->fetch()) {
               $f2p->delete();
            }
         }
      }

      // And finally remove the entry from the database
      return parent::delete($useWhere);
   }


   // -------------------------------------------------------------------------
   // Function: getTitle
   // Retrieves the title of an attachment entry, or if none is set, the
   // filename.
   public function getTitle() {
      $title = $this->title ?: $this->filename;
      return $title ?: null;
   }


   // -------------------------------------------------------------------------
   // Function: setTitle
   // Set the title of the attachment object.
   public function setTitle($title) {
      $orig = clone($this);
      $this->title = mb_strlen($title) > 0 ? $title : null;
      return $this->update($orig);
   }


   // ------------------------------------------------------------------------
   // Function: hashurl
   // Hash a URL provided using our given hash algorithm.
   static public function hashurl($url) {
      if (empty($url)) {
         throw new Exception('No URL provided to hash algorithm.');
      }
      return hash(self::URLHASH_ALG, $url);
   }


   // -------------------------------------------------------------------------
   // Function: beforeSchemaUpdate
   // This procedure contains the various integrity checks we need to perform
   // for the attachment system specifically.
   static public function beforeSchemaUpdate() {
      $table = strtolower(get_called_class());
      $schema = Schema::get();
      $schemadef = $schema->getTableDef($table);

      // 2015-02-19 We have to upgrade our table definitions to have the urlhash field populated
      if (isset($schemadef['fields']['urlhash']) && isset($schemadef['unique keys']['file_urlhash_key'])) {
         // We already have the urlhash field, so no need to migrate it.
         return;
      }
      echo "\nFound old $table table, upgrading it to contain 'urlhash' field...";

      $file = new File();
      $file->query(sprintf('SELECT id, LEFT(url, 191) AS shortenedurl, COUNT(*) AS c FROM %1$s WHERE LENGTH(url)>191 GROUP BY shortenedurl HAVING c > 1', $schema->quoteIdentifier($table)));
      print "\nFound {$file->N} URLs with too long entries in file table\n";
      while ($file->fetch()) {
         // We've got a URL that is too long for our future file table
         // so we'll cut it. We could save the original URL, but there is
         // no guarantee it is complete anyway since the previous max was 255 chars.
         $dupfile = new File();
         // First we find file entries that would be duplicates of this when shortened
         // ... and we'll just throw the dupes out the window for now! It's already so borken.
         $dupfile->query(sprintf('SELECT * FROM file WHERE LEFT(url, 191) = "%1$s"', $file->shortenedurl));
         // Leave one of the URLs in the database by using ->find(true) (fetches first entry)
         if ($dupfile->find(true)) {
            print "\nShortening url entry for $table id: {$file->id} [";
            $orig = clone($dupfile);
            $dupfile->url = $file->shortenedurl;    // make sure it's only 191 chars from now on
            $dupfile->update($orig);
            print "\nDeleting duplicate entries of too long URL on $table id: {$file->id} [";
            // only start deleting with this fetch.
            while($dupfile->fetch()) {
               print ".";
               $dupfile->delete();
            }
            print "]\n";
         } else {
            print "\nWarning! URL suddenly disappeared from database: {$file->url}\n";
         }
      }
      echo "...and now all the non-duplicates which are longer than 191 characters...\n";
      $file->query('UPDATE file SET url=LEFT(url, 191) WHERE LENGTH(url)>191');

      echo "\n...now running hacky pre-schemaupdate change for $table:";
      // We have to create a urlhash that is _not_ the primary key,
      // transfer data and THEN run checkSchema
      $schemadef['fields']['urlhash'] = array (
                                              'type' => 'varchar',
                                              'length' => 64,
                                              'not null' => false,  // this is because when adding column, all entries will _be_ NULL!
                                              'description' => 'sha256 of destination URL (url field)',);
      $schemadef['fields']['url'] = array (
                                           'type' => 'text',
                                           'description' => 'destination URL after following possible redirections',);
      unset($schemadef['unique keys']);
      $schema->ensureTable($table, $schemadef);
      echo "DONE.\n";

      $classname = ucfirst($table);
      $tablefix = new $classname;
      // urlhash is hash('sha256', $url) in the File table
      echo "Updating urlhash fields in $table table...";
      // Maybe very MySQL specific :(
      $tablefix->query(sprintf('UPDATE %1$s SET %2$s=%3$s;',
                       $schema->quoteIdentifier($table),
                       'urlhash',
                       // The line below is "result of sha256 on column `url`"
                       'SHA2(url, 256)'));
      echo "DONE.\n";
      echo "Resuming core schema upgrade...";
   }
}

// END OF FILE
// ============================================================================
?>