<?php
/*
Plugin Name: bcSpamBlock
Plugin URI: http://www.brandonchecketts.com/bcspamblock/
Description: Uses Javascript and PHP crypt() functionality to ensure that a visitor is not a spambot.  Requires no sessions or database.  Based on JSSpamBlock
Author: Brandon Checketts
Version: 1.4
Author URI: http://www.brandonchecketts.com/
*/

/*  
    Version History
    Version 1.4 - 2009-01-13  - Auto approve comments submitted by administrators.  This
    fixes the ability to add/edit comments from the admin screens

    Version 1.3 - 2008-05-14 - Removed the salt from the crypted string

    Version 1.2.1 - 2007-11.09 - Fixed needle/haystack problem with strstr() call

    Version 1.2  - 2007-11-08 - Disabled the checking for the hidden variables in trackbacks, 
    and instead uses a system inspired by the simple-trackback-validation from 
    http://sw-guide.de/wordpress/plugins/simple-trackback-validation/
    Essentially, make sure that the website referenced matches REMOTE_ADDR, and that the page 
    actually contains a link to this blog
    Thanks to wlx@cngis.org for information on how to handle trackback/pingbacks seperately
        
    Version 1.1 - 2007-10-12 - Changed the salt to be a string in the format '$1$xxxxxxxx$' This fixes a
    problem where only the first 8 characters were useful for generating the hash

    Version 1.0 - 2007-10-10 - Initial Relase
*/

/* Configuration options */

// Delete comments that are detected as spam
// Change this to true to just mark the comments as spam. Not sure why anybody would do that
// but its an option just in case
define('BCSPAMBLOCK_DELETECOMMENTS', true);

// Set this to false if you have a different plugin that you would prefer
// to handle trackback/pingback validation
define('BCSPAMBLOCK_CHECK_TRACKBACKS', true);


require_once(dirname(__FILE__)."/bcspamblock.php");

function bcspamblock_doform () {
    echo "\n<!-- bcSpamblock Spam protection by Brandon Checketts http://www.brandonchecketts.com/bcspamblock/ -->";
    bcspamblock_generate();
}

function bcspamblock_checkcomment ($id) {
    if (! bcspamblock_verify()) {
        global $wpdb;
        $comments_table = $wpdb->prefix . "comments";
        if(BCSPAMBLOCK_DELETECOMMENTS){
            $sql = "DELETE FROM $comments_table WHERE comment_id = $id";
        } else {
            $sql = "UPDATE $comments_table SET comment_approved = 'spam' WHERE comment_id = $id";
        }
        $wpdb->query($sql);

	    wp_die( __('bcSpamBlock verification failed.  You need to copy/paste the verification number into the textbox.  Please <a href="javascript:history.go(-1);">Go back and try again</a>'));
	}
}

function bcspamblock_checkfilter($content)
{
    // Auto-approve if logged in as an administrator
    if (!empty($GLOBALS['user']->data->wp_capabilities->administrator)) {
        return $content;
    }

    // For pingback/trackbacks, verify that the server sending the request resolves
    // back to the host, and that the page actually contains a link to me
    if ($content['comment_type'] == 'trackback' || $content['comment_type'] == 'pingback') {
        if(BCSPAMBLOCK_CHECK_TRACKBACKS) {
            if(! preg_match("/^http/", $content['comment_author_url'])) {
                wp_die("Invalid url: ".$content['comment_author_url']);
            }
            $author_website = '';
            preg_match('/^(http|https|ftp)?(:\/\/)?([^\/]+)/i', $content['comment_author_url'], $matches);
            if (isset($matches[3])) {
                $author_website = $matches[3];
            }
            if(! $author_website) {
                wp_die("Unable to determine your website");
            }
            if($_SERVER['REMOTE_ADDR'] != gethostbyname($author_website)) {
                wp_die("$author_website does not resolve to ".$_SERVER['REMOTE_ADDR']);
            }
    
            // TODO, use something more flexible than file_get_contents, since it may be disabled, 
            // or configured not to get files via http for security reasons on shared hosting accounts
            if(strstr(file_get_contents($content['comment_author_url']), $_SERVER['HTTP_HOST']) === false) {
                wp_die ("Doesn't look like ".$content['comment_author_url']." contains a link to me");
            }
            return $content;
        } else {
            // Not configured to validate trackbacks
            return $content;
        }
    }
    if( bcspamblock_verify() ) {
        return $content;
    }

    wp_die('Your comment has been rejected.  It failed to pass our spam checking system');
}



// Actions
if(function_exists('add_action')) {
    add_action('comment_form', 'bcspamblock_doform');
}
if(function_exists('add_filter')) {
    add_filter('preprocess_comment', 'bcspamblock_checkfilter', 2, 1);
}


?>
