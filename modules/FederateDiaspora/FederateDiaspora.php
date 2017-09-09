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

   // -------------------------------------------------------------------------
   // Function: aes_encrypt
   // Encrypts data via AES
   //
   // o string $key The AES key
   // o string $iv The IV (is used for CBC encoding)
   // o string $data The data that is to be encrypted
   //
   // Returns:
   // o string encrypted data
   private static function aes_encrypt($key, $iv, $data) {
      return openssl_encrypt($data, 'aes-256-cbc', str_pad($key, 32, "\0"), OPENSSL_RAW_DATA, str_pad($iv, 16, "\0"));
   }
   
   // -------------------------------------------------------------------------
   // Function: aes_decrypt
   // Decrypts data via AES
   //
   // o param string $key The AES key
   // o param string $iv The IV (is used for CBC encoding)
   // o string $encrypted The encrypted data
   //
   // Returns:
   // o string decrypted data
   private static function aes_decrypt($key, $iv, $encrypted) {
      return openssl_decrypt($encrypted,'aes-256-cbc', str_pad($key, 32, "\0"), OPENSSL_RAW_DATA,str_pad($iv, 16, "\0"));
   }

   // -------------------------------------------------------------------------
   // Function: decode
   // Decodes incoming Diaspora message in the new format
   //
   // Parameters:
   // o array $importer Array of the importer user
   // o string $raw raw post message
    *
   // Returns:
   // o array
   //    o 'message' -> decoded Diaspora XML message
   //    o 'author' -> author diaspora handle
   //    o 'key' -> author public key (converted to pkcs#8)
   public static function decode($importer, $raw) {
      $data = json_decode($raw);
      // Is it a private post? Then decrypt the outer Salmon
      if (is_object($data)) {
         $encrypted_aes_key_bundle = base64_decode($data->aes_key);
         $ciphertext = base64_decode($data->encrypted_magic_envelope);
         $outer_key_bundle = '';
         @openssl_private_decrypt($encrypted_aes_key_bundle, $outer_key_bundle, $importer['prvkey']);
         $j_outer_key_bundle = json_decode($outer_key_bundle);
         if (!is_object($j_outer_key_bundle)) {
            logger('Outer Salmon did not verify. Discarding.');
            http_status_exit(400);
         }
         $outer_iv = base64_decode($j_outer_key_bundle->iv);
         $outer_key = base64_decode($j_outer_key_bundle->key);
         $xml = Diaspora::aes_decrypt($outer_key, $outer_iv, $ciphertext);
      } else {
         $xml = $raw;
      }
      $basedom = parse_xml_string($xml);
      if (!is_object($basedom)) {
         common_log('FederateDiaspora: Received data does not seem to be an XML. Discarding.');
         http_status_exit(400);
      }
      $base = $basedom->children(NAMESPACE_SALMON_ME);
      // Not sure if this cleaning is needed
      $data = str_replace(array(" ", "\t", "\r", "\n"), array("", "", "", ""), $base->data);
      // Build the signed data
      $type = $base->data[0]->attributes()->type[0];
      $encoding = $base->encoding;
      $alg = $base->alg;
      $signed_data = $data.'.'.base64url_encode($type).'.'.base64url_encode($encoding).'.'.base64url_encode($alg);
      // This is the signature
      $signature = base64url_decode($base->sig);
      // Get the senders' public key
      $key_id = $base->sig[0]->attributes()->key_id[0];
      $author_addr = base64_decode($key_id);
      $key = diaspora::key($author_addr);
      $verify = rsa_verify($signed_data, $signature, $key);
      if (!$verify) {
         common_log('FederateDispora: Message did not verify. Discarding.');
         http_status_exit(400);
      }
      return array('message' => (string)base64url_decode($base->data),
            'author' => unxmlify($author_addr),
            'key' => (string)$key);
   }
}

?>
