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
   * Set and save images to our locat public file system as they are brought in from Twitter.
   *
   * @param array $images
   *   An array of images broughtin by a single tweet. Usually just one but cannot be too careful.
   *
   * @return book $succes
   *   An iindication of success or failure of the operation.
   */
  public function setLinkedImages($images);

}
