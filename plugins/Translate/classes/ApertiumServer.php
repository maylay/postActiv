<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
   Translate - a plugin to provide notice translations on demand using an
 * Apertium-APy server interfacing with postActiv                            *
   Copyright (C) 2016 Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *                                                                           *
   @category     Notices
 * @package      postActiv                                                   *
   @author       Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 * @copyright    2016 Maiyannah Bishop                                       *
   @license      http://www.fsf.org/licensing/licenses/gpl-3.0.html GPL 3.0
 * @link         http://postactiv.com/                                       *
   @dependancies None
 * ------------------------------------------------------------------------- *
   ApteriumServer class
 * Contains interface and internal tracking of the ApteriumServer connection *
   and the status thereof.
 * ------------------------------------------------------------------------- */

class ApteriumServer {
   // These are for internal class storage of the configuration variables
   private $serverURL;
   private $serverPort;
   private $useHTTP;
   private $useJSON;
   private $defaultTransTo;
   private $defaultTransFrom;
   
   public function initialize() {
      // load config vars
   }
   
   public function onCheckSchema() {
      // check the apterium server records
   }
}

?>