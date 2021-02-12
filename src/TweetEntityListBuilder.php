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
class TweetEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Tweet ID');
    $header['name'] = $this->t('Tweet Title');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\tweet_feed\Entity\TweetEntity */
    $row['id'] = $entity->getTweetID();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.tweet_entity.canonical',
      ['tweet_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
