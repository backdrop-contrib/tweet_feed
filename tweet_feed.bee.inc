<?php
/**
 * @file
 * Command(s) for working with Tweet Feed's Import
 */

 /**
 * Implements hook_bee_command().
 */
function tweet_feed_bee_command() {
  return array(
    'tf-import-tweets' => array(
      'description' => bt('Import Tweets'),
      'callback' => 'tweet_feed_tf_import_tweets',
      'examples' => array(
        'bee tf-import-tweets' => 'Load the tweets for all configured feeds.',
      ),
      'aliases' => array('tfi'),
      'bootstrap' => BEE_BOOTSTRAP_FULL,
    ),
  );
}

/**
 * Import Tweets
 *
 * Bee command to import all tweets from all feeds or just the one specified. 
 */
function tweet_feed_tf_import_tweets($arguments, $options) {
  tweet_feed_process_feed(NULL);
}
