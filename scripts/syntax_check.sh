#!/bin/bash

# ============================================================================
# Title: Syntax_check
# Simple syntax-checker for postActiv PHP
#
# postActiv:
# the micro-blogging software
#
# Copyright:
# Copyright (C) 2016-2018, Maiyannah Bishop
#
# Derived from code copyright various sources:
# o GNU Social (C) 2013-2016, Free Software Foundation, Inc
# o StatusNet (C) 2008-2012, StatusNet, Inc
# ----------------------------------------------------------------------------
# License:
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# <https://www.gnu.org/licenses/agpl.html>
# ----------------------------------------------------------------------------
# About:
# A script to run all the files in the source tree through PHP LINT to make
# sure they follow a valid syntax.
# ----------------------------------------------------------------------------
# File Authors:
# o Maiyannah Bishop <maiyannah.bishop@postactiv.com>
#
# Web:
#  o postActiv  <http://www.postactiv.com>
#  o GNU social <https://www.gnu.org/s/social/>
# ============================================================================

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
APP_ROOT="$(cd -P ..; pwd)"
NUMBADFILES=0

for file in `find $APP_ROOT`
do
   EXTENSION="${file##*.}"
   if [ "$EXTENSION" == "php" ] || [ "$EXTENSION" == "phtml" ]
   then
      RESULTS=`php -l $file`
      if [ "$RESULTS" != "No syntax errors detected in $file" ]
      then
         echo $RESULTS
         BADFILES="${BADFILES}${file}\n"
         NUMBADFILES=NUMBADFILES+1
      else
         echo $file looks correct according to PHP.
      fi
   fi
done

if [ "$NUMBADFILES" -gt "0" ]
then
   printf "\nThe following files appear to have errors:\n${BADFILES}"
fi
