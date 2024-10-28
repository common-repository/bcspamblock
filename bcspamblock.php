<?php
/*
    bcspamblock Functionality
    A simple spam blocking technique that requires no user intervention for most users

    Author:  Brandon Checketts
    Website: http://www.brandonchecketts.com/bcspamblock/
    Version: 1.3
    Date:    2008-05-14
    
    Description: 
    Essentially, this generates one random value, then uses it plus the current timestamp
    to create an and an encrypted string.   
    The timestamp and encrypted string are put directly into hidden <input> fields.  
    If the visitor has JavaScript enabled, the original random value is copied to a
    third <input> field via javascript and then hidden
    If the visitor has javascript disabled, the user must copy/paste the random value
    into the input box

    This was made as lightweight and compatible as possible.  It doesn't require PHP sessions,
    a database table, or flat files for operation.

    The original idea for the javascript portions of this were taken from the excellent 
    JSSpamBlock WordPress pluging by Paul Butler (http://www.paulbutler.org/)
    Inspiration also came from the logic used by TCP Syncookies

    Sample Usage:

    When displaying the form, do something like this:
        <form action="whatever.php" method="POST">
        ... your normal form inputs here ...
        <?php bcspamblock_generate(); ?>
        <input type="submit">
        </form>

    To validate, do something like this
        if (!bcspamblock_verify() ) {
            print "Spamblock verification failed!";
            // Preferrably do something nicer here
            exit;
        }
    
    License:
    Copyright 2007 Brandon Checketts
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// The salt needs to be something that a visitor wouldn't be able to find.
// It needs to be something consistant between the displaying page and
// the processing page.
// The value supplied should be a good default and make each installation unique
// Feel free to change it to something unique to your server if you like
// To change, edit the argument to md5().  The salt must be in the format $1$xxxxxxxx$
// (see http://php.net/crypt)
define('BCSPAMBLOCK_SALT', '$1$'.substr(md5($_SERVER['SERVER_NAME'].$_SERVER['PATH'].$_SERVER['SERVER_ADDR']),0,8).'$'  );

// These parameters will be used to generate input field names for the hidden fields
// You can change it on your site if you'd like your site to be different than
// any other installation of bcspamblock
define('BCSPAMBLOCK_FIELD',        'bcspamblock');
define('BCSPAMBLOCK_TIME_FIELD',   'bcspamblock_time');
define('BCSPAMBLOCK_HIDDEN_FIELD', 'bcspamblock_hidden');

// Length of random string.  A shorter string may be easier for non-javascript
// users to copy into the textbox
define('BCSPAMBLOCK_CODE_LENGTH', 6);

// Time limit (in seconds) between when page is loaded and when it is submitted
// 60 * 60      = 3600  = 1 hour
// 60 * 60 * 24 = 86400 = 1 day
define('BCSPAMBLOCK_TIMEOUT', 86400);


/*
    If called with no arguments, it will print the output.  If called
    with any true argument, it will return the content as a string
*/
function bcspamblock_generate($return = false)
{
    // Generate a random code
    $charset = 'abcdefghijklmnopqrstuvwyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $code    = '';
    for($i = 0; $i < BCSPAMBLOCK_CODE_LENGTH; $i++) {
        $code .= $charset[(mt_rand(0,(strlen($charset) -1)))];
    }
    $time = time();
    // Make sure to strip the salt from the encrypted string (thanks to jontiw)
    $crypted = substr(crypt($time.$code, BCSPAMBLOCK_SALT), strlen(BCSPAMBLOCK_SALT));
    $content = "
<div id='bcspamblock_div'>
    <input name='".BCSPAMBLOCK_HIDDEN_FIELD."' type='hidden' value='$crypted' />
    <input name='".BCSPAMBLOCK_TIME_FIELD."' type='hidden' value='$time' />
    <p>Please copy the string <strong>$code</strong> to the field below:</p>
    <input name='".BCSPAMBLOCK_FIELD."' id='".BCSPAMBLOCK_FIELD."' value='' />
</div>
<script type='text/javascript' language='javascript'>
    document.getElementById('".BCSPAMBLOCK_FIELD."').value = '$code';
    document.getElementById('bcspamblock_div').style.display = 'none';
    document.getElementById('bcspamblock_div').style.visibility = 'hidden';
</script>
";
    return ($return) ? $content : (print $content);
}

/* 
    Verify that the request was submitted within BCSPAMBLOCK_TIMEOUT seconds and
    that the crypted value matches the expected value.
    Returns true on success, false on failure
*/
function bcspamblock_verify()
{
    return ($_REQUEST[BCSPAMBLOCK_TIME_FIELD] < time() - BCSPAMBLOCK_TIMEOUT) ? false : (crypt(trim($_REQUEST[BCSPAMBLOCK_TIME_FIELD].$_REQUEST[BCSPAMBLOCK_FIELD]), BCSPAMBLOCK_SALT) == BCSPAMBLOCK_SALT.trim($_REQUEST[BCSPAMBLOCK_HIDDEN_FIELD]));
}


?>
