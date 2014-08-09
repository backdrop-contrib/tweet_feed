<?php

function tweet_feed_views_handlers() {
  return array(
    'info' => array(
      'path' => drupal_get_path('module', 'tweet_feed') . '/inc',
    ),
    'handlers' => array(
      // The name of my handler
      'tweet_feed_handler_filter_hashtag_string' => array(
        // The name of the handler we are extending.
        'parent' => 'views_handler_filter_string',
      ),
      'tweet_feed_handler_argument_hashtag' => array(
        // The name of the handler we are extending.
        'parent' => 'views_handler_argument_string',
      ),
      'tweet_feed_handler_tweet_field' => array(
        // The name of the handler we are extending.
        'parent' => 'views_handler_field',
      ),
      'tweet_feed_handler_profile_image_field' => array(
        'parent' => 'views_handler_field',
      ),
    ),
  );
}

/**
 * implementation of views_data()
 */
function tweet_feed_views_data() {
  return array(
    'tweet_feed' => array(
      'table' => array(
        'group' => 'Tweet Feed',
        'help' => t('Create a view of Tweets.'),
        'base' => array(
          'field' => 'tweet_id',
          'title' => 'Twitter Feed Posts',
          'weight' => -10,
        ),
      ),
      'tweet_id' => array(
        'title' => t('Tweet ID'),
        'help' => t('The Twitter Tweet ID of the tweet'),
        'field' => array(
          'handler' => 'views_handler_field_numeric',
          'click sortable' => TRUE,
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_numeric',
        ),
        'sort' => array(
          'handler' => 'views_handler_sort',
        ),
      ),
      'tweet' => array(
        'title' => t('Tweet Text'),
        'help' => t('The actual 140 character tweet text.'),
        'field' => array(
          'handler' => 'tweet_feed_handler_tweet_field',
          'click sortable' => FALSE,
        ),
        'sort' => array(
          'handler' => 'views_handler_sort',
        ),
      ),
      'created_at' => array(
        'title' => t('Tweet Creation Time'),
        'help' => t('The creation time stamp of the tweet.'),
        'field' => array(
          'handler' => 'views_handler_field_date',
        ),
        'sort' => array(
          'handler' => 'views_handler_sort',
        ),
      ),
      'user_id' => array(
        'title' => t('User ID'),
        'help' => t('The user_id of the person making the tweet'),
        'field' => array(
          'handler' => 'views_handler_field_numeric',
          'click sortable' => TRUE,
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_numeric',
        ),
        'sort' => array(
          'handler' => 'views_handler_sort',
        ),
      ),
      'profile_image_url' => array(
        'title' => t('Profile Image URL'),
        'help' => t('The url to the profile image of the tweeter.'),
        'field' => array(
          'handler' => 'tweet_feed_handler_profile_image_field',
        ),
      ),
      'screen_name' => array(
        'title' => t('Screen Name'),
        'help' => t('The screen name of the tweeter.'),
        'field' => array(
          'handler' => 'views_handler_field',
        ),
      ),
      'hashtags' => array(
        'title' => t('Hashtags'),
        'help' => t('The Hashtags associated with a tweet.'),
        'field' => array(
          'handler' => 'views_handler_field',
        ),
        'argument' => array(
          'handler' => 'tweet_feed_handler_argument_hashtag',
        ),
        'filter' => array(
          'handler' => 'tweet_feed_handler_filter_hashtag_string',
        ),
      ),
    ),
  );
}