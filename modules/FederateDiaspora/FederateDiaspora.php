/* ============================================================================
 * Title: Federate Diaspora
 * EXPERIMENTAL DO NOT USE YET
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
 * EXPERIMENTAL Diaspora federation module.
 *
 * Largely and shamelessly cribbed from Friendica's module.
 *
 * PHP version:
 * Tested with PHP 7
 * ----------------------------------------------------------------------------
 * File Authors:
 *  o Michael Vogel
 *  o Tobias Diekershoff
 *  o Hypolite Petovan
 *  o Roland Häder
 *  o Rainulf Pineda
 *  o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
 *
 * Web:
 *  o postActiv  <http://www.postactiv.com>
 *  o GNU social <https://www.gnu.org/s/social/>
 * ============================================================================
 */

// This file is formatted so that it provides useful documentation output in
// NaturalDocs.  Please be considerate of this before changing formatting.

// ============================================================================
// Class: Diaspora
// Federation module to federate with the v2 Diaspora protocol.
//
// 100% experimental at this point and mostly shamelessly lifted from the
// Friendica code.
class Diaspora extends FederationModule {

   // -------------------------------------------------------------------------
   // Function: repair_signature
   // Repairs a signature that was double encoded
   // The function is unused at the moment. It was copied from the old implementation.
   //
   // Parameters:
   // o string $signature The signature
   // o string $handle The handle of the signature owner
   // o integer $level This value is only set inside this function to avoid endless loops
   //
   // Returns:
   // o string the repaired signature
   private static function repair_signature($signature, $handle = "", $level = 1) {
      if ($signature == "")
         return ($signature);
      if (base64_encode(base64_decode(base64_decode($signature))) == base64_decode($signature)) {
         $signature = base64_decode($signature);
         common_log("Repaired double encoded signature from Diaspora/Hubzilla handle ".$handle." - level ".$level);
         // Do a recursive call to be able to fix even multiple levels
         if ($level < 10)
            $signature = self::repair_signature($signature, $handle, ++$level);
      }
      return($signature);
   }

   // -------------------------------------------------------------------------
   // Function: verify_magic_envelope
   // Verifies the envelope and return the verified data
   //
   // Parameters:
   // o string $envelope The magic envelope
   //
   // Returns:
   // o string verified data
   private static function verify_magic_envelope($envelope) {
      $basedom = parse_xml_string($envelope, false);
      if (!is_object($basedom)) {
         common_debug("Envelope is no XML file");
         return false;
      }
      $children = $basedom->children('http://salmon-protocol.org/ns/magic-env');
      if (sizeof($children) == 0) {
         common_debug("XML has no children");
         return false;
      }
      $handle    = "";
      $data      = base64url_decode($children->data);
      $type      = $children->data->attributes()->type[0];
      $encoding  = $children->encoding;
      $alg       = $children->alg;
      $sig       = base64url_decode($children->sig);
      $key_id    = $children->sig->attributes()->key_id[0];
      if ($key_id != "")
         $handle = base64url_decode($key_id);

      $b64url_data = base64url_encode($data);

      $msg           = str_replace(array("\n", "\r", " ", "\t"), array("", "", "", ""), $b64url_data);
      $signable_data = $msg.".".base64url_encode($type).".".base64url_encode($encoding).".".base64url_encode($alg);
      $key           = self::key($handle);
      $verify        = rsa_verify($signable_data, $sig, $key);
      if (!$verify) {
         common_log('Diaspora federation: Message did not verify. Discarding.');
         return false;
      }
      return $data;
   }
}

?>