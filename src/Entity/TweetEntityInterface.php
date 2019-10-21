<?php

namespace Drupal\tweet_feed\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Tweet entity entities.
 *
 * @ingroup tweet_feed
 */
interface TweetEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Get the tweet ID as it is on Twitter from our tweet.
   *
   * @return string $tweet_id
   *   The tweet ID of the tweet object. String due to the size of the number.
   */
  public function getTweetID();

}
