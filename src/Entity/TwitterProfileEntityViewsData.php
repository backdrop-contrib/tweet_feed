<?php

namespace Drupal\tweet_feed\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Tweet entity entities.
 */
class TwitterProfileEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
