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

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Tweet entity name.
   *
   * @return string
   *   Name of the Tweet entity.
   */
  public function getTweetID();

  /**
   * Sets the Tweet entity name.
   *
   * @param string $name
   *   The Tweet entity name.
   *
   * @return \Drupal\tweet_feed\Entity\TweetEntityInterface
   *   The called Tweet entity entity.
   */
  public function setTweetID($tweet_id);

  /**
   * Gets the Tweet entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Tweet entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Tweet entity creation timestamp.
   *
   * @param int $timestamp
   *   The Tweet entity creation timestamp.
   *
   * @return \Drupal\tweet_feed\Entity\TweetEntityInterface
   *   The called Tweet entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Tweet entity published status indicator.
   *
   * Unpublished Tweet entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Tweet entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Tweet entity.
   *
   * @param bool $published
   *   TRUE to set this Tweet entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\tweet_feed\Entity\TweetEntityInterface
   *   The called Tweet entity entity.
   */
  public function setPublished($published);

}
