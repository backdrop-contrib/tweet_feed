<?php

/**
 * @file
 * Contains tweet_entity.page.inc.
 *
 * Page callback for Tweet entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Tweet entity templates.
 *
 * Default template: tweet_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_tweet_entity(array &$variables) {
  // Fetch TweetEntity Entity Object.
  $tweet_entity = $variables['elements']['#tweet_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
