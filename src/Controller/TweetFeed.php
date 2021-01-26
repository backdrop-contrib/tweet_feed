<?php

namespace Drupal\tweet_feed\Controller;

use Drupal\Core\Link;
use Drush\Log;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\tweet_feed\Entity\TweetProfileEntity;
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

    // $node =  \Drupal\node\Entity\Node::load(1);
    // print_r($node);
    // exit();

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

    // Populate our tweet entity with the data we will need to save
    $entity = new TweetEntity([], 'tweet_entity');
  
    $uuid_service = \Drupal::service('uuid');
    
    //$entity->setOwnerId(1);
    
    $entity->setUuid($uuid_service->generate());
    $entity->setCreatedTime(strtotime($tweet->created_at));
    $entity->setFeedMachineName($feed['machine_name']);
    $entity->setTweetId($tweet->id_str);
    $entity->setTweetTitle(mb_substr(Html::decodeEntities($tweet->user->screen_name) . ': ' . Html::decodeEntities($tweet_text), 0, 255));
    $entity->setTweetFullText(tweet_feed_format_output($tweet->full_text, $feed['new_window'], $feed['hash_taxonomy'], $hashtags));
    $entity->setTweetUserProfileId($tweet->user->id);
    $entity->setIsVerifiedUser((int) $tweet->user->verified);
    $entity->setUserMentions($tweet->entities->user_mentions);
    print "here";
    exit();

    /** Handle media and by media I mean images attached to this tweet. */
    if (0 && !empty($tweet->entities->media) && is_array($tweet->entities->media)) {
      $files = [];
      foreach ($tweet->entities->media as $key => $media) {
        if (is_object($media)) {
          /** Edge case - a really big image could push a PHP max memory issue. I need to research */
          /** alternative ways of doing this. */
          $image = file_get_contents($media->media_url . ':large');
          if (!empty($image)) {
            $this->check_path('public://tweet-feed-tweet-images');
            $file_temp = file_save_data($image, 'public://tweet-feed-tweet-images/' . date('Y-m') . '/' . $tweet->id_str . '.jpg', FILE_EXISTS_REPLACE);
            if (is_object($file_temp)) {
              $file = [
                'fid' => $file_temp->fid,
                'filename' => $file_temp->filename,
                'filemime' => $file_temp->filemime,
                'uid' => 1,
                'uri' => $file_temp->uri,
                'status' => 1,
              ];
              $files[] = $file;
            }
            unset($file);
            unset($file_temp);
            unset($image);
          }
        }
      }
      $entity->setLinkedImages($files);
    }

    $entity->setHashtags($hashtags);
    $entity->setUserMentions($user_mentions);

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

    // User Mention Taxonomy Tags
    // Is Quoted or Replied Tweet (Bool)
    // Quoted Tweet Id
    // Replied-to Tweet ID

    $entity->setLinkToTweet('https://twitter.com/' . $tweet->user->screen_name . '/status/' . $tweet->id_str);
    $entity->setTweetUserProfileId($tweet->user->id);

    // profile image

    if (0) {

    // Handle user mentions (our custom field defined by the module). Also places them in
    // the user mentions taxonomy.
    if (!empty($tweet->entities->user_mentions) && is_array($tweet->entities->user_mentions)) {
      foreach ($tweet->entities->user_mentions as $key => $mention) {
        if ($utf8 == TRUE) {
          $node->field_tweet_user_mentions[$node->language][$key] = array(
            'tweet_feed_mention_name' => $mention->name,
            'tweet_feed_mention_screen_name' => $mention->screen_name,
            'tweet_feed_mention_id' => $mention->id,
          );
        }
        else {

        }
      }

      foreach ($user_mentions as $mention) {
        $node->field_twitter_mentions_in_tweet[$node->language][] = array(
          'target_id' => $mention,
        );
      }
    }

    // Not sure about this method of getting the big twitter profile image, but we're
    // going to roll with it for now.
    $tweet->user->profile_image_url = str_replace('_normal', '', $tweet->user->profile_image_url);

    // Handle the profile image obtained from twitter.com
    $file = tweet_feed_process_twitter_image($tweet->user->profile_image_url, 'tweet-feed-profile-image', $tweet->id_str);
    if ($file !== NULL) {
      $node->field_profile_image[$node->language][0] = (array)$file;
    }

    \Drupal::moduleHandler()->alter('tweet_feed_tweet_save', $entity, $tweet);

    if (empty($entity)) {
      return;
    }

    // Save the node
    $entity->save();

    print "first attempted save";
    exit();

    // If we are creating a user profile for the person who made this tweet, then we need
    // to either create it or update it here. To determine create/update we need to check
    // the hash of the profile id and see if it matches our data.
    if (variable_get('tweet_feed_get_tweeter_profiles', FALSE) == TRUE) {
      $user_hash = md5(serialize($tweet->user));
      // See if we have a profile for the author if this tweet. If we do not then we do not
      // need to do the rest of the checks
      $query = new EntityFieldQuery();
      $result = $query->entityCondition('entity_type', 'node')
                      ->entityCondition('bundle', 'twitter_user_profile')
                      ->fieldCondition('field_twitter_user_id', 'value', $tweet->user->id, '=')
                      ->execute();
      // If we have a result, then we have a profile! Then we need to check to see if the hash
      // of the profile is the same as the hash of the user data. If so, then update. If not,
      // then skip and on to the next
      if (isset($result['node'])) {
        $result = db_select('tweet_user_hashes', 'h')
                  ->fields('h', array('nid', 'tuid', 'hash'))
                  ->condition('h.tuid', $tweet->user->id)
                  ->execute();
        if ($result->rowCount() > 0) {
          $tdata = $result->fetchObject();
          // If our hashes are equal, we have nothing to update and can move along
          if ($user_hash == $tdata->hash) {
            return;
          }
          else {
            $update_node_id = $tdata->nid;
          }
        }
      }

      // Populate our node object with the data we will need to save
      $node = new stdClass();

      // If we are being passed a node id for updating, then set it here so we update that
      // node. (might be an edge case)
      if ($update_node_id > 0) {
        $node->nid = $update_node_id;
      }

      // Initialize the standard parts of our tweeting node.
      $node->type = 'twitter_user_profile';
      $node->uid = 1;
      $node->created = $creation_timestamp;
      $node->status = 1;
      $node->comment = 0;
      $node->promote = 0;
      $node->moderate = 0;
      $node->sticky = 0;
      

      $node->field_twitter_user_id[$node->language][0]['value'] = $tweet->user->id_str;
      $node->title = $tweet->user->name;

      if ($utf8 == TRUE) {
        $node->body[$node->language][0]['value'] = $tweet->user->description;
        $node->field_twitter_a_screen_name[$node->language][0]['value'] = $tweet->user->screen_name;
      }
      else {
        $node->body[$node->language][0]['value'] = tweet_feed_filter_iconv_text(tweet_feed_filter_smart_quotes($tweet->user->description));
        $node->field_twitter_a_screen_name[$node->language][0]['value'] = tweet_feed_filter_iconv_text(tweet_feed_filter_smart_quotes($tweet->user->screen_name));
      }

      $node->field_twitter_location[$node->language][0]['value'] = $tweet->user->location;
      $node->field_twitter_a_profile_url[$node->language][0]['value'] = $tweet->user->entities->url->urls[0]->url;
      $node->field_twitter_profile_url[$node->language][0]['value'] = $tweet->user->entities->url->urls[0]->display_url;
      $node->field_twitter_followers[$node->language][0]['value'] = $tweet->user->followers_count;
      $node->field_twitter_following[$node->language][0]['value'] = $tweet->user->friends_count;
      $node->field_twitter_favorites_count[$node->language][0]['value'] = $tweet->user->favourites_count;
      $node->field_twitter_tweet_count[$node->language][0]['value'] = $tweet->user->statuses_count;
      $node->field_tweet_author_verified[$node->language][0]['value'] = (int) $tweet->user->verified;

      // Handle the profile background image obtained from twitter.com
      $file = tweet_feed_process_twitter_image($tweet->user->profile_background_image_url, 'tweet-feed-profile-background-image', $tweet->user->id_str);
      if ($file !== NULL) {
        $node->field_background_image[$node->language][0] = (array)$file;
      }

      // Handle the user profile image obtained from twitter.com
      $file = tweet_feed_process_twitter_image($tweet->user->profile_image_url, 'tweet-feed-profile-user-profile-image', $tweet->user->id_str);
      if ($file !== NULL) {
        $node->field_profile_image[$node->language][0] = (array)$file;
      }

      // Handle the user profile banner image obtained from twitter.com
      $file = tweet_feed_process_twitter_image($tweet->user->profile_banner_url, 'tweet-feed-profile-banner-image', $tweet->user->id_str);
      if ($file !== NULL) {
        $node->field_banner_image[$node->language][0] = (array)$file;
      }

      $node->field_background_color[$node->language][0]['value'] = $tweet->user->profile_background_color;
      $node->field_profile_text_color[$node->language][0]['value'] = $tweet->user->profile_text_color;
      $node->field_link_color[$node->language][0]['value'] = $tweet->user->profile_link_color;
      $node->field_sidebar_border_color[$node->language][0]['value'] = $tweet->user->profile_sidebar_border_color;
      $node->field_sidebar_fill_color[$node->language][0]['value'] = $tweet->user->profile_sidebar_fill_color;

      node_save($node);

      // Make sure the hash in our tweet_hashes table is right by deleting what is there
      // for this node and updating
      db_delete('tweet_user_hashes')
        ->condition('nid', $node->nid)
        ->execute();
      $hash_insert = array(
        'tuid' => $tweet->user->id_str,
        'nid' => $node->nid,
        'hash' => $user_hash,
      );
      db_insert('tweet_user_hashes')->fields($hash_insert)->execute();
    }
  } }


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
   *   The node type (feed item or user profile item)
   * @param int tid
   *   The tweet id associated with this image
   * @return object file
   *   The file object for the retrieved image or NULL if unable to retrieve
   */
  public function process_twitter_image($url, $type, $tid = NULL) {
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
  function check_path($uri) {
    $instance = file_stream_wrapper_get_instance_by_uri($uri);
    $real_path = $instance->realpath();
    if (!file_exists($real_path)) {
      @mkdir($real_path, 0777, TRUE);
    }
    return file_exists($real_path);
  }
}
