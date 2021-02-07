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
interface TwitterProfileEntityInterface extends ContentEntityInterface, EntityChangedInterface {

}
