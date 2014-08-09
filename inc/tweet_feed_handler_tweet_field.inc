<?php

/**
 * override views_handler_field::render so that it does not sanitize our URL's and other links
 */
class tweet_feed_handler_tweet_field extends views_handler_field {

  function render($values) {
    // Get the tweet text from the object
    $tweet = $values->tweet_feed_tweet;

    /* based on our preference, assign all links to new windows or to the same window */
    $target = (variable_get('tweet_feed_new_window',0) == 1) ? '_blank' : '_self';

    /* look for twitter handles and make them clickable */
    /* modified so that slashes in the twitter handle are counted */
    $pattern = '/@([A-Za-z0-9_]{1,15})(?![.A-Za-z])/';
    $replace  = '<a target="'.$target.'" href="http://twitter.com/'.strtolower('\1').'">@\1</a>';
    $tweet   = preg_replace($pattern,$replace,$tweet);

    /* look for twitter hash tags and make them clickable */
    /* modified so that slashes in the twitter handle are counted */
    $tweet = preg_replace('/(^|\s)#(\w*+\w*)/', '\1<a target="'.$target.'" href="http://twitter.com/search?q=%23\2">#\2</a>', $tweet);
    
    $tweet = utf8_decode($tweet);
    return htmlspecialchars_decode($tweet);
  }
}