<?php

class tweet_feed_handler_profile_image_field extends views_handler_field {

  function render($values) {
  
    // Get the tweet text from the object
    $profile_image_url = $values->tweet_feed_profile_image_url;

    /* based on our preference, assign all links to new windows or to the same window */
    $https = (variable_get('tweet_feed_profile_https',0) == 1) ? 'https:' : 'http:';

    // Convert it to http or https depending on our selection 
    $profile_image_url = str_replace('http:', $https, $profile_image_url);
   
    return $profile_image_url;
  }
}
