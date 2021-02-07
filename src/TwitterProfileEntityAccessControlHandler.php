<?php

namespace Drupal\tweet_feed;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Tweet entity entity.
 *
 * @see \Drupal\tweet_feed\Entity\TweetEntity.
 */
class TwitterProfileEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\tweet_feed\Entity\TweetEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished tweet entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published tweet entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit tweet entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete tweet entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add tweet entity entities');
  }

}
