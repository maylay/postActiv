*Auto-Translate* - a plugin to provide notice translations on demand using an
Apertium-APy server interfacing with postActiv

# Copyright
Copyright (C) 2016 Maiyannah Bishop <maiyannah.bishop@postactiv.com>

# Installation

This plugin requires a running Apertium-APy server to run properly.  For
instructions on how to set one up see: http://wiki.apertium.org/wiki/Apy

To install the plugin itself, merely copy the /Translate/ folder containing
it into the postActiv /plugins/ directory and then configure it as outlined
below.

# Configuration

You will have to point this plugin at the Apertium-APy server for it to be
able to poll it for translations, most of the rest is handled by the plugin
itself, but there are optional configuration variables for finer-grained
control.

## Mandatory Configuration

$["plugins"]["translate"]["server"] = "http://uri.to.apy-server/"
$["plugins"]["translate"]["port"] = port the server is running on

## Optional Configuration
$["plugins"]["translate"]["default_lang"] = default language code to 
   translate to, for eg, en_gb
$["plugins"]["translate"]["assume_from"] = default language code to
   assume we're translating from, if analysis fails, for eg fr_ca
$["plugins"]["translate"]["req_method"] = API method to use, default json,
   can also use http if the server is localhost.

# License

This program is free software: you can redistribute it and/or modify it 
under the terms of the GNU General Public License as published by the Free 
Software Foundation, either version 3 of the License, or at your option) 
any later version.

This program is distributed in the hope that it will be useful, but WITHOUT 
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more 
details.

You should have received a copy of the GNU General Public License along with 
this program.  If not, see <http://www.gnu.org/licenses/>.