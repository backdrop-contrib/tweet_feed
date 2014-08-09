<?php

/**
 * implementation of hook_views_default_views()
 */
function tweet_feed_views_default_views() {
  $view = new view;
  $view->name = 'tweet_feed_view';
  $view->description = 'Tweet Feed View';
  $view->tag = '';
  $view->base_table = 'tweet_feed';
  $view->core = 6;
  $view->api_version = '2';
  $view->disabled = TRUE; /* Edit this to true to make a default view disabled initially */
  $handler = $view->new_display('default', 'Defaults', 'default');
  $handler->override_option('fields', array(
    'profile_image_url' => array(
      'label' => '',
      'alter' => array(
        'alter_text' => 1,
        'text' => '<img src="[profile_image_url]" border="0">',
        'make_link' => 0,
        'path' => '',
        'absolute' => 0,
        'link_class' => '',
        'alt' => '',
        'rel' => '',
        'prefix' => '',
        'suffix' => '',
        'target' => '',
        'help' => '',
        'trim' => 0,
        'max_length' => '',
        'word_boundary' => 1,
        'ellipsis' => 1,
        'html' => 0,
        'strip_tags' => 0,
      ),
      'empty' => '',
      'hide_empty' => 0,
      'empty_zero' => 0,
      'hide_alter_empty' => 1,
      'exclude' => 0,
      'id' => 'profile_image_url',
      'table' => 'tweet_feed',
      'field' => 'profile_image_url',
      'relationship' => 'none',
    ),
    'screen_name' => array(
      'label' => '',
      'alter' => array(
        'alter_text' => 0,
        'text' => '',
        'make_link' => 0,
        'path' => '',
        'absolute' => 0,
        'link_class' => '',
        'alt' => '',
        'rel' => '',
        'prefix' => '',
        'suffix' => '',
        'target' => '',
        'help' => '',
        'trim' => 0,
        'max_length' => '',
        'word_boundary' => 1,
        'ellipsis' => 1,
        'html' => 0,
        'strip_tags' => 0,
      ),
      'empty' => '',
      'hide_empty' => 0,
      'empty_zero' => 0,
      'hide_alter_empty' => 1,
      'exclude' => 0,
      'id' => 'screen_name',
      'table' => 'tweet_feed',
      'field' => 'screen_name',
      'relationship' => 'none',
    ),
    'tweet' => array(
      'label' => '',
      'alter' => array(
        'alter_text' => 0,
        'text' => '',
        'make_link' => 0,
        'path' => '',
        'absolute' => 0,
        'link_class' => '',
        'alt' => '',
        'rel' => '',
        'prefix' => '',
        'suffix' => '',
        'target' => '',
        'help' => '',
        'trim' => 0,
        'max_length' => '',
        'word_boundary' => 0,
        'ellipsis' => 0,
        'html' => 0,
        'strip_tags' => 0,
      ),
      'empty' => '',
      'hide_empty' => 0,
      'empty_zero' => 0,
      'hide_alter_empty' => 0,
      'exclude' => 0,
      'id' => 'tweet',
      'table' => 'tweet_feed',
      'field' => 'tweet',
      'relationship' => 'none',
    ),
    'created_at' => array(
      'label' => '',
      'alter' => array(
        'alter_text' => 0,
        'text' => '',
        'make_link' => 0,
        'path' => '',
        'absolute' => 0,
        'link_class' => '',
        'alt' => '',
        'rel' => '',
        'prefix' => '',
        'suffix' => '',
        'target' => '',
        'help' => '',
        'trim' => 0,
        'max_length' => '',
        'word_boundary' => 1,
        'ellipsis' => 1,
        'html' => 0,
        'strip_tags' => 0,
      ),
      'empty' => '',
      'hide_empty' => 0,
      'empty_zero' => 0,
      'hide_alter_empty' => 1,
      'date_format' => 'raw time ago',
      'custom_date_format' => '',
      'exclude' => 0,
      'id' => 'created_at',
      'table' => 'tweet_feed',
      'field' => 'created_at',
      'relationship' => 'none',
    ),
  ));
  $handler->override_option('sorts', array(
    'created_at' => array(
      'order' => 'DESC',
      'id' => 'created_at',
      'table' => 'tweet_feed',
      'field' => 'created_at',
      'relationship' => 'none',
    ),
  ));
  $handler->override_option('access', array(
    'type' => 'none',
  ));
  $handler->override_option('cache', array(
    'type' => 'none',
  ));
  
  // now output the view (or whatever you want to do with it)
  $views[$view->name] = $view;
  return $views;

}