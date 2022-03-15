<?php

namespace Drupal\tweet_feed\Commands;

use Abraham\TwitterOAuth\TwitterOAuth;
use Drush\Commands\DrushCommands;
use Drupal\tweet_feed\Controller\TweetFeed;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class TweetFeedCommands extends DrushCommands {

  protected $db;

  /**
   * Load our usable objects into scope.
   */
  public function __construct() {
    $this->db = \Drupal::database();
  }

  /**
   * Delete everthing (remove before prod)
   *
   * @usage tweet_feed:kill
   *   Kill the data with fire.
   *
   * @command tweet_feed:kill
   * @aliases tfk
   */
  public function kill() {
    $this->db->truncate('tweet_entity')->execute();
    $this->db->truncate('tweet_entity__hashtags')->execute();
    $this->db->truncate('tweet_entity__linked_images')->execute();
    $this->db->truncate('tweet_entity__user_mentions')->execute();
    $this->db->truncate('tweet_entity__user_mentions_tags')->execute();
    $this->db->truncate('twitter_profiles')->execute();
  }

  /**
   * Import the latest batch of tweets.
   *
   * @param $feed
   *   The machine name of the feed to be imported
   * @usage tweet_feed:import feed1
   *   Import the feeds as configured in machine name feed1.
   *
   * @command tweet_feed:import
   * @aliases tfi
   */
  public function import($feed) {
    // Sanity check to make sure the feed exists.
    $feed_config = \Drupal::service('config.factory')->get('tweet_feed.twitter_feeds');
    $feeds = $feed_config->get('feeds');
    if (!empty($feeds[$feed])) {
      $tf = new TweetFeed();
      $tf->pullDataFromFeed($feed, FALSE);
      return TRUE;
    }
  }
}
