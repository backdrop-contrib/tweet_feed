<?php

namespace Drupal\tweet_feed\Controller;

use Drupal\Core\Link;
use Drush\Log;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\tweet_feed\Entity\TwitterProfileEntity;
use Drupal\tweet_feed\Entity\TweetEntity;
use Drupal\Component\Utility\Html;
use Drupal\Core\Language\Language;
//use Abraham\TwitterOAuth\TwitterOAuth;
use Drupal\tweet_feed\Controller\TwitterOAuth2;

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
  public function saveTweet($tweet, $feed, $process_specific_tweet = FALSE) {
    $language = Language::LANGCODE_DEFAULT;

    // Check to see if we already have this tweet in play.
    // If so, don't reimport it.
    $entities = \Drupal::entityQuery('tweet_entity')
      ->condition('tweet_id', $tweet->id_str, '=')
      ->execute();
    if (!empty($entities)) {
      return FALSE;
    }

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
            $this->checkPath('public://tweet-feed-tweet-images', TRUE);
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
      $entity->setGeographicCoordinates($bb);
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
    $file = $this->processTwitterImage($tweet->user->profile_image_url, 'profile', $tweet->user->id_str, FALSE);
    if ($file !== FALSE) {
      $file_array = [];
      $file_array[] = $file;
      $entity->setProfileImage($file_array);
    }

    \Drupal::moduleHandler()->alter('tweet_feed_tweet_save', $entity, $tweet);

    $entity->save();

    if (empty($entity)) {
      return TRUE;
    }

    if ($process_specific_tweet == FALSE) {
      if (!empty($specific_tweets)) {
        $this->checkSpecificTweets($specific_tweets, $feed);
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
        if ($profile_hash == $entity_hash) {
          \Drupal::moduleHandler()->alter('tweet_feed_twitter_profile_save', $entity, $tweet->user);
          return TRUE;
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
      $file = $this->processTwitterImage($tweet->user->profile_image_url, 'user-profile', $tweet->user->id_str, FALSE);
      if ($file !== FALSE) {
        $file_array = [];
        $file_array[] = $file;
        $entity->setProfileImage($file_array);
      }

      // Handle the user profile banner image obtained from twitter.com
      if (!empty($tweet->user->profile_banner_url)) {
        $file = $this->processTwitterImage($tweet->user->profile_banner_url, 'banner-image', $tweet->user->id_str, FALSE);
        if ($file !== FALSE) {
          $file_array = [];
          $file_array[] = $file;
          $entity->setBannerImage($file_array);
        }
      }

      \Drupal::moduleHandler()->alter('tweet_feed_twitter_profile_save', $entity, $tweet->user);

      $entity->save();
    }

    return TRUE;
  }

  private function checkSpecificTweets($specific_tweets, $feed) {
    /** Get the account of the feed we are processing */
    $accounts_config = \Drupal::service('config.factory')->get('tweet_feed.twitter_accounts');
    $accounts = $accounts_config->get('accounts');
    if (!empty($accounts[$feed['aid']])) {
      $account = $accounts[$feed['aid']];
    }
    $tweet_count = 0;
    foreach($specific_tweets as $tweet_id) {
      // Build TwitterOAuth object with client credentials
      $con = new TwitterOAuth2(
        $account['consumer_key'],
        $account['consumer_secret'],
        $account['oauth_token'],
        $account['oauth_token_secret']
      );
      $tweet = $con->get("statuses/show", ['id' => $tweet_id, 'tweet_mode' => 'extended']);
      if (!empty($tweet->errors)) {
        continue;
      }
      else {
        $this->saveTweet($tweet, $feed, TRUE);
        $tweet_count++;
      }
    }
    return $tweet_count;
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
   */
  public function pullDataFromFeed($feed_machine_name) {
    \Drupal::logger("tweet_feed")->notice(dt('Beginning Twitter import of ') . $feed_machine_name);
    /** Get a list of all the available feeds. */
    $config = \Drupal::service('config.factory')->getEditable('tweet_feed.twitter_feeds');
    $feeds = $config->get('feeds');
    if (!empty($feeds[$feed_machine_name])) {
      $feed = $feeds[$feed_machine_name];

      /** I do not want to replicate this data in the settings for feeds so we will just */
      /** assign it ad-hoc to the array here. */
      $feed['machine_name'] = $feed_machine_name;

      /** Get the account of the feed we are processing */
      $accounts_config = \Drupal::service('config.factory')->get('tweet_feed.twitter_accounts');
      $accounts = $accounts_config->get('accounts');
      if (!empty($accounts[$feed['aid']])) {
        $account = $accounts[$feed['aid']];
      }

      // If we have selected to clear our prior tweets for this particular feed, then we need
      // to do that here.
      if (!empty($feed['clear_prior'])) {
        \Drupal::logger('tweet_feed')->notice(dt('Clearing existing tweet entities'));
        $entities = \Drupal::entityQuery('tweet_entity')
          ->condition('feed_machine_name', $feed_machine_name, '=')
          ->execute();
        if (isset($entities)) {
          foreach ($entities as $entity_id) {
            $entity = \Drupal::entityTypeManager()->getStorage('tweet_entity')->load($entity_id);
            $entity->deleteLinkedImages();
            $entity->deleteProfileImage();
            $entity->delete();
          }
        }
      }
      // Build TwitterOAuth object with client credentials
      $con = new TwitterOAuth2($account['consumer_key'], $account['consumer_secret'], $account['oauth_token'], $account['oauth_token_secret']);

      // Get the number of tweets to pull from our list & variable init.
      $tweets = [];
      $tweet_count = 0;
      $max_id = 0;
      $process = TRUE;
      $number_to_get = $feed['pull_count'];

      $params = ($feed['query_type'] == 3 || $feed['query_type'] == 2) ?
        array('screen_name' => $feed['timeline_id'], 'count' => 200, 'tweet_mode' => 'extended') :
        array('q' => $feed['search_term'], 'count' => 200, 'tweet_mode' => 'extended');

      // $max_id overrides $since_id
      if (!empty($max_id)) {
        $params['max_id'] = $max_id;
      }

      while ($process === TRUE) {
        switch ($feed['query_type']) {
          case 2:
            $response = $con->get("statuses/user_timeline", $params);
            if (!empty($response)) {
              if (empty($response->errors)) {
                $tdata = $response;
              }
            }
            else {
              $process = FALSE;
            }
            break;

          case 3:
            $params += [
              'slug' => $feed['list_name'],
              'owner_screen_name' => $feed['timeline_id'],
              'count' => 200,
              'tweet_mode' => 'extended',
            ];
            $tdata = $con->get("lists/statuses", $params);
            break;

          case 1:
          default:
            $tdata = $con->get("search/tweets", $params);
            break;
        }

        if (!empty($tdata)) {
          if (!empty($tdata->errors)) {
            foreach($tdata->errors as $error) {
              $process = FALSE;
              $tweets = [];
            }
          }
          else {
            if ($feed['query_type'] == 2 || $feed['query_type'] == 3) {
              $end_of_the_line = array_pop($tdata);
              array_unshift($tdata, $end_of_the_line);
              $max_id = $end_of_the_line->id_str;
              $tweet_data = $tdata;
            }
            else {
              $tweet_data = $tdata->statuses;
            }

            // If this is FALSE, then we have hit an error and need to stop processing
            if (isset($tweet_data['tweets']) && $tweet_data['tweets'] === FALSE) {
              $process = FALSE;
              break;
            }

            if (count($tweet_data) > 0) {
              $duplicate = 0;
              foreach ($tweet_data as $key => $tweet) {
                if ($tweet_count >= $number_to_get) {
                  $process = FALSE;
                  continue;
                }
                $saved = $this->saveTweet($tweet, $feed);
                // If we have three duplicates in a row, assume we've reached the last imported
                // tweets and stop here.
                if ($saved == FALSE) {
                  $duplicate++;
                  if ($duplicate >= 3) {
                    $process = FALSE;
                    break 2;
                  }
                  continue;
                }
                else {
                  $duplicate = 0;
                }
                $tweet_count++;
                if (($tweet_count % 50) == 0) {
                  \Drupal::logger("tweet_feed")->notice(dt('Total Tweets Processed: ') . $tweet_count . dt('. Max to Import: ') . $number_to_get);
                }
              }
            }
            else {
              $process = FALSE;
            }
          }
        }

        if ($process == TRUE) {
          $params['max_id'] = $max_id-1;
        }
      }
      \Drupal::logger("tweet_feed")->notice(dt('Tweet Feed import of the feed: ') . $feed_machine_name . dt(' completed. ' . $tweet_count . ' Tweets imported.'));
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

      $tid = [];
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($taxonomy);

      if (!empty($terms)) {
        foreach ($terms as $term) {
          if ($term->name == $taxonomy_name) {
            $tid[0]['value'] = $term->tid;
            break;
          }
        }
      }
      if (empty($tid)) {
        $new_term = \Drupal\taxonomy\Entity\Term::create([
          'vid' => $taxonomy,
          'name' => $taxonomy_name,
        ]);
        $new_term->save();
        $tid = $new_term->tid->getValue();
      }
      $tids[] = $tid[0]['value'];
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
   * @param bool $add_date
   *   Do we add the year and month on to the end of the path?
   * @return object file
   *   The file object for the retrieved image or NULL if unable to retrieve
   */
  private function processTwitterImage($url, $type, $id, $add_date = FALSE) {
    // I hate myself for this next line.
    $image = @file_get_contents($url);
    if (!empty($image)) {
      $file = FALSE;
      list($width, $height) = getimagesize($url);
      $this->checkPath('public://tweet-feed-' . $type . '-images/', $add_date);
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
   * Make sure the directory exists. If not, create it
   *
   * @param string $uri
   *   The URI location of the path to be created.
   * @param bool $add_date
   *   Do we add the year and month on to the end of the path?
   * @return bool $exists
   *   If the real_path exists, then return TRUE
   */
  private function checkPath($uri, $add_date = FALSE) {
    $date = (!empty($add_date)) ? '/' . date('Y-m') : NULL;
    $real_path = \Drupal::service('file_system')->realpath($uri) . $date;
    if (!file_exists($real_path)) {
      mkdir($real_path, 0777, TRUE);
    }
    return file_exists($real_path);
  }
}
