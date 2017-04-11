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

class ApteriumServer
{
   // Read only
   private $ServerURL;
   private $ServerPort;
   private $Server;
      // composite of the URL and port, ie http://localhost:2800
   private $UseHTTP;
   private $UseJSON;
   private $DefaultTransTo;
   private $DefaultTransFrom;

   // Read+write
   private $Description;
      // an optional admin-side description of this particular server
   private $LastConnection;
      // MYSQL formatted timestamp of last successful connection
   private $LastFailure;
      // MYSQL formatted timestamp of last failed connection
   private $NumConnSuccesses;
      // Number of successful connections to this server, used for conn
      // integrity tracking.
   private $NumConnFailures;
      // Number of failed connections to this server, used for conn integrity
      // tracking.

   public function initialize() {
      // load config vars
   }

   public function onCheckSchema() {
      // check the apterium server records
   }

   // Standard battery of interfaces, because I'm a PASCAL lass at heart
   static function getServerURL()
   {
      return $this->ServerURL;
   }

   static function getServerPort()
   {
      return $this->ServerPort;
   }

   static function getUseHTTP()
   {
      return $this->UseHTTP;
   }

   static function getUseJSON()
   {
      return $this->UseJSON;
   }

   static function getDefaultTransTo()
   {
      return $this->DefaultTransTo;
   }

   static function getDefaultTransFrom()
   {
      return $this->DefaultTransFrom;
   }

   static function getServerFullURL()
   {
      return $this->Server;
   }

   static function getDescription()
   {
      return $this->Description;
   }

   static function setDescription(string $Desc)
   {
      try
      {
         $this->Description = $Desc;
         if ($this->Description==$Desc)
         {
            return true;
         }
         else
         {
            return false;
         }
      }
      catch (exception $err)
      {
         // todo: log exception
         return false;
      }
   }
   
   static function getLastConnection()
   {
      return $this->LastConnection;
   }

   static function setLastConnection(string $datetime)
   {
      try
      {
         $this->LastConnection = $datetime;
         if ($this->LastConnection==$datetime)
         {
            return true;
         }
         else
         {
            return false;
         }
      }
      catch (Exception $err)
      {
         // todo: log the exception
         return false;
      }
   }

   static function getLastFailure()
   {
      return $this->LastFailure;
   }

   static function setLastFailure(string $datetime)
   {
      try
      {
         $this->LastFailure = $datetime;
         if ($this->LastFailure==$datetime)
         {
            return true;
         }
         else
         {
            return false;
         }
      }
      catch (Exception $err)
      {
         // todo: log the exception
         return false;
      }
   }

   static function getConnSuccesses()
   {
      return $this->ConnSuccesses;
   }

   static function incrConnSuccesses()
   {
      try
      {
         $original = $this->ConnSuccesses;
         $incr = ++$this->ConnSuccesses;
         if ($incr > $original)
         {
            return true;
         }
         else
         {
            return false;
         }
      }
      catch (Exception $err)
      {
         // todo: log error
         return false;
      }
   }

   static function getConnFailures()
   {
      return $this->ConnFailures;
   }
   
   static function incrConnFailures()
   {
      try
      {
         $original = $this->ConnFailures;
         $incr = ++$this->ConnFailures;
         if ($incr > $original)
         {
            return true;
         }
         else
         {
            return false;
         }
      }
      catch (Exception $err)
      {
         // todo: log error
         return false;
      }
   }
}

?>
