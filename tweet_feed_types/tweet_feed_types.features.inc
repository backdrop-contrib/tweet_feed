<?php
/**
 * @file
 * tweet_feed_types.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function tweet_feed_types_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function tweet_feed_types_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}

/**
 * Implements hook_node_info().
 */
function tweet_feed_types_node_info() {
  $items = array(
    'twitter_tweet_feed' => array(
      'name' => t('Twitter Tweet Feed'),
      'base' => 'node_content',
      'description' => t('These are the Tweets collected by the Tweet Feed module.'),
      'has_title' => '1',
      'title_label' => t('Tweet Title'),
      'help' => '',
    ),
    'twitter_user_profile' => array(
      'name' => t('Twitter User Profile'),
      'base' => 'node_content',
      'description' => t('The profile of a person who has made a tweet that has been index by your configuration of Tweet Feed. Includes relevant images and Tweet data.'),
      'has_title' => '1',
      'title_label' => t('Name'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}
