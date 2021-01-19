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
    $feed_config = \Drupal::service('config.factory')->get('tweet_feed.twitter_feeds');
    $feeds = $feed_config->get('feeds');
    if (!empty($feeds[$feed])) {
      $data = $feeds[$feed];

      /** Get the account of the feed we are processing */
      $accounts_config = \Drupal::service('config.factory')->get('tweet_feed.twitter_accounts');
      $accounts = $accounts_config->get('accounts');
      if (!empty($accounts[$feeds[$feed]['aid']])) {
        $account = $accounts[$feeds[$feed]['aid']];
        $connection = new TwitterOAuth($account['consumer_key'], $account['consumer_secret'], $account['oauth_token'], $account['oauth_token_secret']);

        switch ($data['query_type']) {
          case 3:
            $content = $connection->get("lists/statuses", ['count' => $data['pull_count'], 'slug' => $data['list_name'], 'owner_screen_name' => $data['timeline_id'], 'tweet_mode' => 'extended']);
            break;
          case 2:
            $content = $connection->get("statuses/user_timeline", ['count' => $data['pull_count'], 'screen_name' => $data['timeline_id'], 'tweet_mode' => 'extended']);
            break;
          case 1:
          default:
            $content = $connection->get("search/tweets", ['count' => $data['pull_count'], 'q' => $data['search_term'], 'tweet_mode' => 'extended']);
            break;
        }

        if (!empty($content) && is_object($content)) {
          $errors = $content->errors;
          foreach($error as $error) {
            $this->logger()->error("Tweet Feed: Twitter Error ($error->code) - $error->message");
          }
          return;
        }
        elseif (!empty($content) && is_array($content)) {
          $tweetFeed = new TweetFeed();
          if ($data['query_type'] == 2 || $data['query_type'] == 3) {
            // Get the lowest ID from the last element in the timeline
            $end_of_the_line = array_pop($content);
            array_push($content, $end_of_the_line);
            $lowest_id = $end_of_the_line->id_str;

            // Proceed with our processing
            $tweet_data = $content;
          }
          else {
            $tweet_data = $content->statuses;
          }

          foreach ($tweet_data as $tweet) {

            $tweetFeed->saveTweet($tweet, $data);

            /**
             * If we have a replied to tweet, we get a reference to the tweet being replied to,
             * but we do not get the tweet itself - so we need to retrieve that tweet, save it,
             * and then reference it when we do the view.
             */

            /**
             * We have a quoted tweet. So we will save this as well for context but go no further.
             * I am not sure we want to go down the rabit hole. Quoted tweets are kept in the same
             * dataset as the tweet that quotes it, so we do not need to make an API call to get the
             * tweet.
             */
            if (!empty($tweet->quoted_status_id)) {
              
            }
          }
        }
        else {
          $this->logger()->warning("Tweet Feed: No tweets to process");
          return;
        }
      }
    }
  }
}


