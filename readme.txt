=== bcSpamBlock ===
Contributors: bchecketts
Tags: comments, spam
Requires at least: 2.0.2
Tested up to: 2.7
Stable tag: trunk

Uses Javascript and PHP crypt() functionality to ensure that a visitor is not a spambot.  Requires no sessions or database.  Based on JSSpamBlock

== Description ==

bcSpamBlock is a simple way to protect your blog from comment spam.  It
ensures that the client is a human by using a small bit of javascript on the
comments page.   Users with Javascript enabled will never notice any
difference.  For users without Javascript, they will have to copy/paste a
short code into a textbox to confirm.

This plugin was designed to be as light and compatible as possible.  It doesn't require
PHP sessions, a database table or file-based lookups.  It uses a bit of
cryptography to have the visitor submit all of the information necessary to
validate them.

For trackbacks and pingbacks, it ensures that the IP requesting the pingback
resolves back to the website it is saying that it is.   If so, it retrieves
the page that it says contains the link, and makes sure that it does, in-fact
have a like to this blog.

How it works:
Essentially, this generates one random value, and a crypted version of it with salt.
The crypted version is put directly into a hidden <input> field.
If the visitor has JavaScript enabled, the original random value one is copied to a
second <input> field via javascript and then hidden
If the visitor has javascript disabled, the user must copy/paste the random value
into the input box


Original logic for the Javascript portion was taken from the JS SpamBlock Wordpress plugin
by Paul Butler (http://www.paulbutler.org/)

The idea for the trackback validation came from the
simple-trackback-validation plugin


== Installation ==

1. Installation is the same as any simple WordPress plugin.   Simply create a
/wp-content/plugins/bcspamblock/ directory, then copy the three files 3
files (bcspamblock.php, bcspamblock_wp.php, and readme.txt) into it

2. Log into the administrative section of your blog.  Go to the Plugins menu and
click 'Activate' next to the bcspamblock plugin

== Changelog ==

* 2007-10-10 - v1.0 - Initial release 
* 2007-10-12 - v1.1 - Changed the salt to be a string in the format '$1$xxxxxxxx$'
               This fixes a problem where only the first 8 characters were
               useful for generating the hash
* 2007-11-08 - v1.2 - Disabled the checking for the hidden variables in
               trackbacks, and instead uses a system inspired by the
               simple-trackback-validation from
               http://sw-guide.de/wordpress/plugins/simple-trackback-validation/
               Essentially, make sure that the website referenced matches  
               REMOTE_ADDR, and that the page actually contains a link to this
               blog
               Thanks to wlx@cngis.org for information on how to handle
               trackback/pingbacks seperately
* 2007-11-09 - v1.2.1 - Fixed needle/haystack problem with strstr() call 
               An annoying PHP inconsistency
* 2008-05-14 - v1.3 - Fixed a problem where PHP's crypt() function includes
               the salt inside the encrypted value.
* 2009-01-13 - v1.4 - Auto approve comments submitted by administrators.  This 
               fixes the ability to add/edit comments from the admin screens

== License ==

Copyright (c) 2007 - 2009 Brandon Checketts

This software is provided 'as-is', without any express or implied warranty. In no event will the authors be held liable for any damages arising from the use of this software.

Permission is granted to anyone to use this software for any purpose, including commercial applications, and to alter it and redistribute it freely, subject to the following restrictions:

1. The origin of this software must not be misrepresented; you must not claim that you wrote the original software. If you use this software in a product, an acknowledgment in the product documentation would be appreciated but is not required.
2. Altered source versions must be plainly marked as such, and must not be misrepresented as being the original software.
3. This notice may not be removed or altered from any source distribution.
