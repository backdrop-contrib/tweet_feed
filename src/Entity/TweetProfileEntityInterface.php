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
interface TweetProfileEntityInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Load a Twitter profile based on the Twitter user_id, not the internal reference.
   * 
   * @param string $user_id
   *   The twitter user_id.
   * 
   * @return TweetProfileEntity $profile
   *   The profile of the user requested. Returns FALSE if none exists.
   */
  public function __construct($user_id);

  /**
   * Gets the Tweet entity name.
   *
   * @return string
   *   Name of the Tweet entity.
   */
  public function getName();

  /**
   * Sets the Tweet entity name.
   *
   * @param string $name
   *   The Tweet entity name.
   *
   * @return \Drupal\tweet_feed\Entity\TweetEntityInterface
   *   The called Tweet entity entity.
   */
  public function setName($name);

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



}
