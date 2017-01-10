postActiv Coding Style
===============================================================================

This document exists to document the prefered coding style for submissions to
postActiv.  While coding style is quite personal and I'm not going to force my
particular views on anyone, I am responsible for maintaining any code that I do
accept into postActiv, and it makes it much easier for me to do so, if it's in
a format I can easily use.

You'll probably want to take some notes, because this breaks from both the
kernel and GNU standards in places.  What works for them doesn't neccesarialy
work for me, and they're made coding in C/C++ and not PHP as well.


1. PARAGRAPH SPACING
-------------------------------------------------------------------------------
This one is strangely the ongoing topic of hot debate.  Listen, I don't care,
there's tools to easily change tabs to spaces and vice versa in code, do what
you like.  But when submitting a merge request, set the tab space to 3 and
convert them to spaces.

Why?  Well this breaks down to a couple things:

Spaces over tabs: tabs render differently based on the settings you have in
the editor and environment you are using.  Spaces don't.  I prefer consistent
representations over different environments, because depending on where and
how I'm editing, I might be using nano from the CLI, ConText from Windows,
or gedit from the Linux desktop.

3-space: this is just preference, but to keep the code consistent, use one
of these tools.  If you're on Windows, ConText can do this automagically
for you, just go to Environment > Editor and set "Tabs to Spaces" and the
"Tab Width" to 3.  Done.  If you already have tabs in there, go Format >
Convert Tabs to Spaces.  You can go the other way round too, for your own
editing and convinence while you're modifying the code.  Just flip it
back when you do submit.

Functions should be seperated with 1 line unless you are sectioning off a
large file.

Trailing whitespace at the ends of lines should be avoided.  Be mindful that
many editors with "smart" indentation will insert whitespace to create 
paragraph intentation for you, but will not remove these tabs or spaces if you
do not type something on that line.  As a result, you end up with lines 
containing trailing whitespace.  Along with making your source files slightly
larger than they have to be, this can also lead to obscure and sometimes 
difficult to resolve problems if that whitespace is in the wrong place.

If Git warns you about your code containing whitespace, and you ignore that
warning with your merge request, I will probably not be very happy and reject
your merge request.


2. LINE LENGTH
-------------------------------------------------------------------------------
Where-ever possible, try to keep the lines to 80 characters.  Don't
sacrifice readability for it though - if it makes more sense to have it in
one longer line, and it's more easily read that way, that's fine.

With assignments, avoid breaking them down into multiple lines unless
neccesary, except for enumerations and arrays.


3. BRACES
-------------------------------------------------------------------------------
Functions should be formatted as follows:

    function xyz($args) {
       code;
    }

Both GNU and kernel coding conventions ask for the brace on the line after
the function declaration, but this is inconsistent (as the kernel coding
conventions point out), and more importantly, used to signal possible
nesting.  You cannot nest functions in C.  You can in PHP.  This leads us
to ...


4. NESTING FUNCTIONS
-------------------------------------------------------------------------------
Avoid, if at all possible.  When not possible, document the living daylights
out of why you're nesting it.  It's not always avoidable, but PHP 5 has a lot
of obscure problems that come up with using nested functions.

If you must use a nested function, be sure to have robust error-handling.
This is a must and submissions including nested functions that do not have
robust error handling will be rejected and you'll be asked to add it.


5. NAMING CONVENTIONS
-------------------------------------------------------------------------------
I have basically one requirement for a name, but it's an important one:

I have to be able to understand what you mean by it without neccesarialy seeing
it in context, because the code that calls something might not always make it
clear.

So if you have something like:

    $notice->post($contents);

Well I can easily tell what you're doing there because the names are straight-
forward and clear.

Something like this:

    foo->bar();

Is much less clear.

Also, whereever possible, avoid ambiguous term.  For example, don't use text
as a term for a variable.  Call back to "contents" above.


6. SCOPING
-------------------------------------------------------------------------------
Properly enforcing scope of functions is something many PHP programmers don't 
do, but should.

In general:
*  Variables unique to a class should be protected and use interfacing to
   change them.  This allows for input validation and making sure we don't have
   injection, especially when something's exposed to the API, that any program
   can use, and not all of them are going to be be safe and trusted.

*  Variables not unique to a class should be validated prior to every call,
   which is why it's generally not a good idea to re-use stuff across classes
   unless there's significant performance gains to doing so.

*  Classes should protect functions that they do not want overriden, but they
   should avoid protecting the constructor and destructor and related helper
   functions as this prevents proper inheritance.


7. TYPECASTING
-------------------------------------------------------------------------------
PHP is a soft-typed language and it falls to us developers to make sure that
we are using the proper inputs.  Where ever possible use explicit type casting.
Where it in't, you're going to have to make sure that you check all your 
inputs before you pass them.

All outputs should be cast as an explicit PHP type.

Not properly typecasting is a shooting offence.  Soft types let programmers
get away with a lot of lazy code, but lazy code is buggy code, and frankly, I
don't want it in postActiv if it's going to be buggy.


8. CONSISTENT EXCEPTION HANDLING
-------------------------------------------------------------------------------
Consistency is key to good code to begin with, but it is especially important
to be consistent with how we handle errors.  postActiv has a variety of built-
in exception classes.  Use them, wherever it's possible and appropriate, and
they will do the heavy lifting for you.

Additionally, ensure you clean up any and all records and variables that need
cleanup in a function using try { } finally { } even if you do not plan on
catching exceptions (why wouldn't you, though?  That's silly.)

If you do not call an exception handler, you must, at a minimum, record errors
to the log using common_log(level, message)

Ensure all possible control flows of a function have exception handling and
cleanup, where appropriate.  Don't leave endpoints with unhandled exceptions.
Try not to leave something in an error state if it's avoidable.


9. COMMENT EFFECTIVELY
-------------------------------------------------------------------------------
Generally-speaking, you should only need to comment at the start of a function
or class, to explain what it does or why it exists, what inputs it takes, and
what outputs it generates, as well as possible error states.

Inline commenting to explain how code works is generally only repetitive, but
it can also indicate code that might be trying to get a bit too "fancy" for its
own good.  If you find yourself thinking you need to comment a piece of code
because it is complex or not easily understood, please consider how to write it
in a more straight-forward manner instead.

On the other hand, if there is no other way to get something done but "get 
fancy" then that inline commenting becomes a MUST.

File headers follow a consistent format, as such:

    /* ============================================================================
     * Title: [title of file here]
     * [summary description of file here]
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
     * [extended description of file here]
     *
     * PHP version:
     * Tested with [versions it works with here, ideally 5.6 and 7]
     * ----------------------------------------------------------------------------
     * File Authors:
     * o [your name here!]
     *
     * Web:
     *  o postActiv  <http://www.postactiv.com>
     *  o GNU social <https://www.gnu.org/s/social/>
     * ============================================================================
     */

    // This file is formatted so that it provides useful documentation output in
    // NaturalDocs.  Please be considerate of this before changing formatting.

Please use it.

A few notes:

*  The description of the file doesn't have to be exhaustive.  Rather it's
   meant to be a short summary of what's in this file and what it does.  Try
   to keep it to 1-5 lines.  You can get more in-depth when documenting
   individual functions!

*  You'll probably see files with multiple authors and copyrights, this is by
   design - many people contributed to postActiv or its forebears!  If you
   are modifying an existing file, APPEND your own author line, and copyright.
   Do not replace existing ones.

*  You can use a license other than the AGPL if you are writing 100% from
   scratch.  If you do, include the source code file header specified by your
   given license, and change the license link as appropriate.


8. FILE CONSOLIDATION
-------------------------------------------------------------------------------
Try to avoid having a bunch of loose, small files.  This isn't always
avoidable, but it makes the codebase a mess and makes debugging a mess too.

If your file is under 100 lines of actual code consider adding it into
an existing file, rather than having it in a new file.  If your file is over 
2000 lines of code, consider breaking it down as logically-appropriate.

This goes for plugins/modules too (explained further below) - if it's a very
small change, it's probably not appropriate for a module or plugin. If you're 
adding something new but small, consider adding it to one of the "utils" files.


9. LAYOUT AND LOCATION OF FILES
-------------------------------------------------------------------------------
/actions/ contains files that determine what happens when something "happens":
for instance, when someone favourites or repeats a notice.  Code that is
related to a "happening" should go here.

/classes/ contains abstract definitions of certain "things" in the codebase
such as a user or notice.  If you're making a new "thing", it goes here.

/lib/ is basically the back-end.  Actions will call something in here to get
stuff done usually, which in turn will probably manipulate information stored
in one or more records represented by a class.

/extlib/ is where external libraries are located.  If you include a new
external library, it goes here.

/modules/ are basically plugins for postActiv that are not optional.  Many
core functions of the GNU Social software this was derived from, prototyped
new features as plugins, and then required it.  This is also a great way to
modularize your own new features.  If you want to create new core features
for postActiv, it is probably best to create a module unless you absolutely
must override or modify the core behaviours.

/plugins/ are basically optional plugins.  If you want to create something new
that a site can choose freely to enable or disable, create a plugin.

The /modules/ and /plugins/ directories will also likely have their own
/actions/, /classes/, and /lib/ folders.


10. RETURN VALUES
-------------------------------------------------------------------------------
All functions must return a value.  Every single one.  This is not optional.

If you are simply making a procedure call, for example as part of a helper
function, then return boolean TRUE on success, and the exception on failure.

When returning the exception, return the whole nine yards, which is to say the
actual PHP exception object, not just an error message.

All return values not the above should be type cast, and you should sanitize
anything returned to ensure it fits into the cast.  You might technically make
an integer a string, for instance, but you should be making sure that integer
SHOULD be a string, if you're returning it, and that it is a valid return
value.

A vast majority of programming errors come down to not checking your inputs
and outputs properly, so please try to do so as best and thoroughly as you can.


11. EDITOR CRUFT
-------------------------------------------------------------------------------
Except for the important attribution and source file information included in
the header, editor cruft like mode lines should be purged with fire and salt.

I don't force my editing environment on you in the source files.  Don't do so
to me in your submissions.


12. VERSION CONTROLLING
-------------------------------------------------------------------------------
Whenever possible, avoid combining or moving files in a way that loses version
control information.  This isn't always possible, but when it is, we should do
so.  Where it's not, use GIT BLAME to find out the authors of whatever file
you're doing away with and ensure that you attribute the parts of their code
that remain.


13. OPCODES AND OTHER FANCY CODE
-------------------------------------------------------------------------------
Keep it simple.  As I've said earlier in this document, if there's a simple
way to do something, and a complex, "fancy" way, use the simple way.  While you
can get very fast PHP code by using direct opcode references in the same way
that you can often get fast C code by using inline assembler, the performance
gains are not analogous, and fancy means to unwind and directly manipulate
opcodes usually get in the way of things.

Stuff like zend and other such things are pieces of software an end-user can
install on their own system and get faster code out of postActiv using without
any need for putting that kind of thing directly in postActiv.

Likewise, give considerable thought to including external libraries for 
functions you develop.  Each additional external library increases both the
memory and disk footprint of postActiv, as well as the potential attack surface
for the nefarious types.  I'm not saying "don't use external libraries ever" -
that's a line I'd be breaking myself because postActiv DOES use many external
libraries itself, but give it careful consideration before doing so.  As is we
already have inherited some issues we've had to deal with from the existing
external libraries and I'd rather not add to that.

-mb