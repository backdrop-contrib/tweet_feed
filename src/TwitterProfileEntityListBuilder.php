<?php

namespace Drupal\tweet_feed;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Tweet entity entities.
 *
 * @ingroup tweet_feed
 */
class TwitterProfileEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Tweet Profile Entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\tweet_feed\Entity\TwitterProfileEntity */
    $row['id'] = $entity->getTwitterUserId();
    $row['name'] = Link::createFromRoute(
      $entity->getName() . '/' . $entity->getScreenName(),
      'entity.twitter_profile.edit_form',
      ['twitter_profile' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
