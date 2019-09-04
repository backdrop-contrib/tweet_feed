<?php

namespace Drupal\tweet_feed\Controller;

use Drupal\Core\Link;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class ProboRepositoryController.
 */
class TwitterFeedsController extends ControllerBase {

  /**
   * display_feeds().
   *
   * @return array
   *   Return render array of a table of elements that make up the list
   *   of available twitter feeds or an empty list. Designed to be
   *   handled by Drupal's configuration management system.
   */
  public function display_feeds() {
    $config = $this->config('tweet_feed.twitter_feeds');
    
    $header = [
      ['data' => 'Feed Name'],
      ['data' => 'Type'],
      ['data' => 'Feed Criteria'],
      ['data' => '# Per Pull'],
      ['data' => 'Edit'],
      ['data' => 'Delete'],
      ['data' => 'Import'],
    ];

    $rows = [];
    $feeds = $config->get('feeds');
    foreach ($feeds as $key => $feed) {
      $edit_link = Link::createFromRoute($this->t('Edit'), 'tweet_feed.edit_feed', ['id' => $feed->id]);
      $delete_link = Link::createFromRoute($this->t('Delete'),'tweet_feed.delete_feed', ['id' => $feed->id]);
      $import_link = Link::createFromRoute($this->t('Import'),'tweet_feed.import_feed', ['id' => $feed->id]);
      $row = [
        ['data' => $account->name],
        ['data' => $account->machine_name],
        ['data' => $edit_link],
        ['data' => $delete_link],
        ['data' => $import_link],
      ];
      $rows[] = $row;
    }
    return [
      '#type' => 'table',
      '#attributes' => ['class' => ['table table-striped']],
      '#prefix' => NULL,
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => 'THERE ARE NO TWITTER FEEDS CURRENTLY CREATED.',
    ];
  }

}