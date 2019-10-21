<?php

namespace Drupal\tweet_feed\Controller;

use Drupal\Core\Link;
use Drush\Log;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Class TweetFeed.
 */
class TweetFeed extends ControllerBase {

  /**
   * Save the tweet to our tweet entity.
   *
   * @param object $tweet
   *  The tweet as it is retrieved from Twitter.
   *
   * @param array $feed
   *  The information on the feed from which this feed is being fetched.
   */
  public function save_tweet($tweet, $feed) {

  }

  /**
   * Get Twitter Data
   *
   * Pull data from the feed given our internal feed id. Our feed object also contains the
   * information about the account associated with this feed (reference) so we have everything
   * we need to connect via the Twitter API and retrieve the data.
   *
   * @param string feed_machine_name
   *   The machine name of the feed with which we wish to procure the data
   * @return array tweets
   *   An array of all the tweets for this feed. FALSE if there are problems.
   */
  public function pull_data_from_feed($feed_machine_name) {

    /** Get a list of all the available feeds. */
    $config = \Drupal::service('config.factory')->getEditable('tweet_feed.twitter_feeds');
    $feeds = $config->get('feeds');
    if (!empty($feeds[$feed_machine_name])) {
      $feed = $feeds[$feed_machine_name];
      /** Get the account of the feed we are processing */
      $accounts_config = \Drupal::service('config.factory')->get('tweet_feed.twitter_accounts');
      $accounts = $accounts_config->get('accounts');
      if (!empty($accounts[$feeds[$feed]['aid']])) {
        $account = $accounts[$feeds[$feed]['aid']];
      }

      // If we have selected to clear our prior tweets for this particular feed, then we need
      // to do that here.
      if (!empty($feed['clear_prior'])) {
        // All tweets are entities, so we do an entity query to get the entity id's for the tweets
        // belonging to this feed and delete them. It's conceivable that this could take some
        // time.
        //tweet_feed_set_message('Clearing Previous Tweets', 'ok');
        $query = new EntityFieldQuery();
        $entities = $query->entityCondition('entity_type', 'tweet_feed')
          ->condition('feed_machine_name', $feed_machine_name, '=')
          ->execute();
        if (isset($entities['tweet_feed'])) {
          foreach ($result['tweet_feed'] as $entity_id => $entity) {
            $tweet = Drupal\tweet_feed\Entity\TweetEntity::load($entity_id);
            $tweet->delete();
          }
        }
        //tweet_feed_set_message('All previous tweets for this feed are deleted.', 'ok', $web_interface);
      }

      // Build TwitterOAuth object with client credentials
      $con = new TwitterOAuth($account['consumer_key'], $account['consumer_secret'], $account['oauth_token'], $account['oauth_token_secret']);

      // Get the number of tweets to pull from our list
      $number_to_get = $feed['pull_count'];
      $number_of_pages = intval(ceil(($feed['pull_count'] % 2)));

      $run_count = 0;
      $current_page = 0;
      $lowest_id = -1;
      $tweets = [];
      $params = ($feed->query_type == QUERY_TIMELINE) ?
        array('screen_name' => $feed['timeline_id'], 'count' => 100, 'tweet_mode' => 'extended') :
        array('q' => $feed['search_term'], 'count' => 100, 'tweet_mode' => 'extended');

      while ($tweet_count < $number_to_get && $lowest_id != 0) {
        //tweet_feed_set_message('Tweets Imported: ' . count($tweets) . ', Total To Import: ' . $number_to_get, 'ok');
        if (!empty($tdata->search_metadata->next_results)) {
          $next = substr($tdata->search_metadata->next_results, 1);
          $parts = explode('&', $next);
          foreach($parts as $part) {
            list($key, $value) = explode('=', $part);
            if ($key == 'max_id') {
              $lowest_id = $value;
            }
            $params[$key] = $value;
          }
        }

        $data = new stdClass();
        switch ($feed['query_type']) {
          case QUERY_TIMELINE:
            if ($lowest_id > 0) {
              $params['max_id'] = $lowest_id;
            }
            $tdata = json_decode($con->get("statuses/user_timeline", $params));
            break;

          case QUERY_LIST:
            $params = [
              'slug' => $feed['list_name'],
              'owner_screen_name' => $feed['timeline_id'],
              'count' => 100,
              'tweet_mode' => 'extended',
            ];

            if ($lowest_id > 0) {
              $params['max_id'] = $lowest_id;
            }
            $tdata = json_decode($con->get("list/statuses", $params));
            break;

          case QUERY_SEARCH:
          default:
            $tdata = json_decode($con->get("search/tweets", $params));
            if (empty($tdata->search_metadata->next_results)) {
              $lowest_id = 0;
            }
            break;
        }

        if (!empty($tdata)) {
          if (!empty($tdata->errors)) {
            foreach($tdata->errors as $error) {
              //tweet_feed_set_message(t('Tweet Feed Fail: ') . $error->message . ': ' . $error->code,  'error', $web_interface);
              $lowest_id = 0;
              $tweets = [];
            }
          }
          else {
            if ($feed->query_type == QUERY_TIMELINE || $feed->query_type == QUERY_LIST) {
              /** Get the lowest ID from the last element in the timeline. Inconsistent from feed type to feed type. Normalize. */
              $end_of_the_line = array_pop($tdata);
              array_push($tdata, $end_of_the_line);
              $lowest_id = $end_of_the_line->id_str;
              $tweet_data = $tdata;
            }
            else {
              $tweet_data = $tdata->statuses;
            }

            // If this is FALSE, then we have hit an error and need to stop processing
            if (isset($tweet_data['tweets']) && $tweet_data['tweets'] === FALSE) {
              break;
            }

            foreach ($tweet_data as $key => $tweet) {
              $this->save_tweet($tweet, $feed);
            }

            if (count($tweet_data) == 0) {
              $lowest_id = 0;
            }
            else {
              $tweet_count += count($tweet_data);
            }
          }
          $run_count++;
        }
        else {
          //tweet_feed_set_message('No tweets available for this criteria.', 'ok', $web_interface);
          break;
        }
      }
    }
  }

  /**
   * Checks to see if the provided tweet_id is currently in our entity.
   *
   * @param string $tweet_id
   *   The id of the tweet. This is the Twitter id, so it is a string.
   *
   * @return bool $exists
   *   Returns TRUE if an entity exists with Twitter ID, FALSE if not.
   */
  public function tweet_exists($tweet_id) {
    $query = \Drupal::entityQuery('tweet_feed');
    $query->condition('tweet_id', $tweet_id);
    $tweets = $query->execute();
    if (!empty($tweets)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Process hashtags and user mentions in tweets
   *
   * We need to store these in our taxonomy (do not save duplicates) and save a reference
   * to them in our created tweet node
   *
   * @param array $entities
   *   An array of entities to be saved to our taxonomy.
   * @param string $taxonomy
   *   The machine name of the taxonomy to be saved to.
   * @param array $terms
   *   An array of taxonomy objects to be saved to the node for this tweet.
   */
  public function process_taxonomy($entities, $taxonomy) {
  }

  /**
   * Process Images from URL
   *
   * Allows the passage of a URL and a saves the resulting image in that URL to a file
   * that can be attached to our node. These are mostly used in user profiles and avatars
   * associated with user tweets.
   *
   * @param string url
   *   The twitte.com url of the image being retrieved
   * @param string type
   *   The node type (feed item or user profile item)
   * @param int tid
   *   The tweet id associated with this image
   * @return object file
   *   The file object for the retrieved image or NULL if unable to retrieve
   */
  function process_twitter_image($url, $type, $tid = NULL) {
  }

  /**
   * Filter iconv from text.
   */
  function ilter_iconv_text($text, $replace = '--') {
    // The tweet author goes into the title field
    // Filter it cleanly since it is going into the title field. If we cannot use iconv,
    // then use something more primitive, but effective
    // @see https://www.drupal.org/node/1910376
    // @see http://webcollab.sourceforge.net/unicode.html
    // Reject overly long 2 byte sequences, as well as characters above U+10000
    // and replace with --.
    $altered = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]' .
     '|[\x00-\x7F][\x80-\xBF]+' .
     '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' .
     '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})' .
     '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
     '--', $text);
    // Reject overly long 3 byte sequences and UTF-16 surrogates and replace
    // with --.
    return preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]' . '|\xED[\xA0-\xBF][\x80-\xBF]/S', $replace, $altered);
  }

  /**
   * Filter smart quotes to ASCII equivalent.
   *
   * @param string $text
   *   Input text to filter.
   *
   * @return string $text
   *   Filtered text.
   */
  public function filter_smart_quotes($text) {
    // Convert varieties of smart quotes to ACSII equivalent.
    $search = array(
      chr(0xe2) . chr(0x80) . chr(0x98),
      chr(0xe2) . chr(0x80) . chr(0x99),
      chr(0xe2) . chr(0x80) . chr(0x9c),
      chr(0xe2) . chr(0x80) . chr(0x9d),
      chr(0xe2) . chr(0x80) . chr(0x93),
      chr(0xe2) . chr(0x80) . chr(0x94),
    );

    $ascii_replace = array(
      "'",
      "'",
      '"',
      '"',
      '-',
      '&mdash;',
    );

    return str_replace($search, $ascii_replace, $text);
  }

  /**
   * Iterate through the feeds and import
   *
   * @param string feed_machine_name
   *   The machine name of the feed that we are going to process. If empty, then process them all.
   */
  public function process_feed($feed_machine_name = NULL) {
    /** Get a list of all the available feeds. */
    $config = \Drupal::service('config.factory')->getEditable('tweet_feed.twitter_feeds');
    $feeds = $config->get('feeds');

    $feeds_to_process = [];
    if ($feed_machine_name === NULL) {
      $feeds_to_process = array_keys($feeds);
    }
    else {
      /** Make sure the field specified exists. */
      if (!empty($feeds[$field_machine_name])) {
        $feeds_to_process[] = $feed_machine_name;
      }
    }
    if (!empty($fields_to_process)) {
      foreach ($feeds_to_process as $feed_to_process => $feed) {
        $drush = \Drush\Log\Logger::log('ok', 'Processing Feed: ' . $feed['name']);
        $tweets = $this->pull_data_from_feed($feed_to_process);
      }
    }
  }

}