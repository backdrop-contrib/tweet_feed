<?php

namespace Drupal\tweet_feed\Controller;

use Drupal\Core\Link;
use Drush\Log;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\tweet_feed\Entity\TwitterProfileEntity;
use Drupal\tweet_feed\Entity\TweetEntity;
use Abraham\TwitterOAuth\TwitterOAuth;
use Drupal\Component\Utility\Html;
use Drupal\Core\Language\Language;

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
  public function saveTweet($tweet, $feed) {
    $language = Language::LANGCODE_DEFAULT;

    // Get the creation time of the tweet and store it.
    $creation_timestamp = strtotime($tweet->created_at);

    // Add our hash tags to the hashtag taxonomy. If it already exists, then get the tid
    // for that term. Returns an array of tid's for hashtags used.
    $hashtags = $this->processTaxonomy($tweet->entities->hashtags, 'twitter_hashtag_terms');

    // Add our user mentions to it's relative taxonomy. Handled just like hashtags
    $user_mentions = $this->processTaxonomy($tweet->entities->user_mentions, 'twitter_user_mention_terms');

    // Process the tweet. This linkes our twitter names, hash tags and converts any
    // URL's into HTML.
    $tweet_text = $tweet->full_text;
    $tweet_html = tweet_feed_format_output($tweet_text, $feed['new_window'], $feed['hash_taxonomy'], $hashtags);

    $specific_tweets = [];
    $uuid_service = \Drupal::service('uuid');

    // Populate our tweet entity with the data we will need to save
    $entity = new TweetEntity([], 'tweet_entity');
    $entity->setOwnerId(1);
    $entity->setUuid($uuid_service->generate());
    $entity->setCreatedTime(strtotime($tweet->created_at));
    $entity->setFeedMachineName($feed['machine_name']);
    $entity->setTweetId($tweet->id_str);
    $entity->setTweetTitle(mb_substr(Html::decodeEntities($tweet->user->screen_name) . ': ' . Html::decodeEntities($tweet_text), 0, 255));
    $entity->setTweetFullText(tweet_feed_format_output($tweet->full_text, $feed['new_window'], $feed['hash_taxonomy'], $hashtags));
    $entity->setTweetUserProfileId($tweet->user->id);
    $entity->setIsVerifiedUser((int) $tweet->user->verified);
    $entity->setUserMentions($tweet->entities->user_mentions);

    /** Re-Tweet*/
    if (!empty($tweet->retweeted_status->id_str)) {
      $entity->setTypeOfTweetReference('retweeted');
      $entity->setReferencedTweetId($tweet->retweeted_status->id_str);
      $specific_tweets[] = $tweet->retweeted_status->id_str;
    }

    /** Tweet Reply*/
    if (!empty($tweet->in_reply_to_status_id_str)) {
      $entity->setTypeOfTweetReference('replied');
      $entity->setReferencedTweetId($tweet->in_reply_to_status_id_str);
      $specific_tweets[] = $tweet->in_reply_to_status_id_str;
    }
    
    /** Quoted Tweet w/Comment */
    if (!empty($tweet->is_quote_status)) {
      $entity->setTypeOfTweetReference('quoted');
      $entity->setReferencedTweetId($tweet->quoted_status_id_str);
      $specific_tweets[] = $tweet->quoted_status_id_str;
    }

    /** Handle media and by media I mean images attached to this tweet. */
    if (!empty($tweet->entities->media) && is_array($tweet->entities->media)) {
      $files = [];
      foreach ($tweet->entities->media as $key => $media) {
        if (is_object($media)) {
          /** Edge case - a really big image could push a PHP max memory issue. I need to research */
          /** alternative ways of doing this. */
          $image = file_get_contents($media->media_url . ':large');
          if (!empty($image)) {
            $this->check_path('public://tweet-feed-tweet-images');
            $file_temp = file_save_data($image, 'public://tweet-feed-tweet-images/' . date('Y-m') . '/' . $tweet->id_str . '.jpg', 1);
            if (is_object($file_temp)) {
              $fid = $file_temp->fid->getvalue()[$key]['value'];
              $files[] = [
                'target_id' => $fid,
                'alt' => '',
                'title' => $media->id,
                'width' => $media->sizes->large->w,
                'height' => $media->sizes->large->h,
              ];
            }
            unset($file_temp);
            unset($image);
          }
        }
      }
      $entity->setLinkedImages($files);
    }

    if (!empty($hashtags)) {
      $entity->setHashtags($hashtags);
    }

    if (!empty($user_mentions)) {
      $entity->setUserMentionsTags($user_mentions);
    }

    if (!empty($tweet->place) && is_object($tweet->place)) {
      $bb = json_encode($tweet->place->bounding_box->coordinates[0]);
      $entity->setGeographicCoordites($bb);
    }

    if (!empty($tweet->place->full_name)) {
      $entity->setGeographicPlace($tweet->place->full_name);
    }

    if (!empty($tweet->source)) {
      $entity->setSource($tweet->source);
    }

    $entity->setLinkToTweet('https://twitter.com/' . $tweet->user->screen_name . '/status/' . $tweet->id_str);

    $entity->setTweetUserProfileId($tweet->user->id);

    $tweet->user->profile_image_url = str_replace('_normal', '', $tweet->user->profile_image_url);
    $file = $this->process_twitter_image($tweet->user->profile_image_url, 'profile', $tweet->user->id_str, FALSE);
    if ($file !== FALSE) {
      $file_array = [];
      $file_array[] = $file;
      $entity->setProfileImage($file_array);
    }

    \Drupal::moduleHandler()->alter('tweet_feed_tweet_save', $entity, $tweet);

    $entity->save();

    if (empty($entity)) {
      return;
    }

    $entity = new TwitterProfileEntity([], 'twitter_profile');

    // If we are creating a user profile for the person who made this tweet, then we need
    // to either create it or update it here. To determine create/update we need to check
    // the hash of the profile and see if it matches our data.
    
    $profile_hash = md5(serialize($tweet->user));
    $query = \Drupal::entityQuery('twitter_profile')
      ->condition('twitter_user_id', $tweet->user->id)
      ->execute();


    // If we have a result, then we have a profile! Then we need to check to see if the hash
    // of the profile is the same as the hash of the user data. If so, then update. If not,
    // then skip.
    if (!empty($query)) {
      $keys = array_keys($query);
      $entity = $entity->load($keys[0]);
      $entity_hash = $entity->getHash();
      print $profile_hash ."- $entity_hash\n";
      if ($profile_hash == $entity_hash) {
        \Drupal::moduleHandler()->alter('tweet_feed_twitter_profile_save', $entity, $tweet->user);
        return;
      }
    }

    // Populate our entity with the data we will need to save
    $entity->setOwnerId(1);
    $entity->setUuid($uuid_service->generate());
    $entity->setTwitterUserId($tweet->user->id_str);
    $entity->setName($tweet->user->name);
    $entity->setDescription($tweet->user->description);
    $entity->setScreenName($tweet->user->screen_name);
    $entity->setLocation($tweet->user->location);
    $entity->setFollowersCount($tweet->user->followers_count);
    $entity->setVerified((int) $tweet->user->verified);
    $entity->setStatusesCount($tweet->user->statuses_count);
    $entity->setHash($profile_hash);
  
    // Handle the user profile image obtained from twitter.com
    $file = $this->process_twitter_image($tweet->user->profile_image_url, 'user-profile', $tweet->user->id_str, FALSE);
    if ($file !== FALSE) {
      $file_array = [];
      $file_array[] = $file;
      $entity->setProfileImage($file_array);
    }

    // Handle the user profile banner image obtained from twitter.com
    $file = $this->process_twitter_image($tweet->user->profile_banner_url, 'banner-image', $tweet->user->id_str, FALSE);
    if ($file !== FALSE) {
      $file_array = [];
      $file_array[] = $file;
      $entity->setBannerImage($file_array);
    }

    \Drupal::moduleHandler()->alter('tweet_feed_twitter_profile_save', $entity, $tweet->user);

    $entity->save();
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

      /** I do not want to replicate this data in the settings for feeds so we will just */
      /** assign it ad-hoc to the array here. */
      $feed->machine_name = $feed_machine_name;

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
   * @param array $tids
   *   The term id's to be saved
   */
  public function processTaxonomy($entities, $taxonomy) {
    $tids = [];
    foreach($entities as $entity) {
      switch($taxonomy) {
        case 'twitter_hashtag_terms':
          $taxonomy_name = $entity->text;
          break;
        case 'twitter_user_mention_terms':
          $taxonomy_name = $entity->screen_name;
          break;
        default:
          break;
      }

      $tid = NULL;
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($taxonomy);

      if (!empty($terms)) {
        foreach ($terms as $term) {
          if ($term->name == $taxonomy_name) {
            $tid = $term->tid;
            break;
          }
        }
      }
      if (empty($tid)) {
        $new_term = \Drupal\taxonomy\Entity\Term::create([
          'vid' => $taxonomy,
          'name' => $taxonomy_name,
        ]);
        $tid = $new_term->tid;
        $new_term->save();
      }
      $tids[] = $tid;
    }
    return $tids;
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
   *   The type of image. This affects the folder the image is placed in.
   * @param int id
   *   The id associated with this image
   * @return object file
   *   The file object for the retrieved image or NULL if unable to retrieve
   */
  private function process_twitter_image($url, $type, $id, $add_date = FALSE) {
    $image = file_get_contents($url);
    if (!empty($image)) {
      $file = FALSE;
      list($width, $height) = getimagesize($url);
      $this->check_path('public://tweet-feed-' . $type . '-images/', $add_date);
      if ($add_date == TRUE) {
        $file_temp = file_save_data($image, 'public://tweet-feed-' . $type . '-images/' . date('Y-m') . '/' . $id . '.jpg', 1);
      }
      else {
        $file_temp = file_save_data($image, 'public://tweet-feed-' . $type . '-images/' . $id . '.jpg', 1);
      }
      if (is_object($file_temp)) {
        $fid = $file_temp->fid->getValue()[0]['value'];
        $file = [
          'target_id' => $fid,
          'alt' => '',
          'title' => $id,
          'width' => $width,
          'height' => $height,
        ];
      }
      return $file;
    }
    else {
      return FALSE;
    }
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

  /**
   * Make sure the directory exists. If not, create it
   *
   * @param string $uri
   *   The URI location of the path to be created.
   */
  private function check_path($uri) {
    $real_path = \Drupal::service('file_system')->realpath($uri);
    if (!file_exists($real_path)) {
      mkdir($real_path, 0777, TRUE);
    }
    return file_exists($real_path);
  }
}
