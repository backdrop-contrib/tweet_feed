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
      ['data' => 'Export'],
    ];

    $rows = [];
    $types = ['','Search','Timeline', 'List'];
    $feeds = $config->get('feeds');
    foreach ($feeds as $key => $feed) {
      $edit_link = Link::createFromRoute($this->t('Edit'), 'tweet_feed.edit_feed', ['feed_machine_name' => $key]);
      $delete_link = Link::createFromRoute($this->t('Delete'),'tweet_feed.delete_feed', ['feed_machine_name' => $key]);
      $export_link = Link::createFromRoute($this->t('Export'),'tweet_feed.export_feed', ['feed_machine_name' => $key]);
      $row = [
        ['data' => $feed['feed_name']],
        ['data' => $key],
        ['data' => $types[$feed['query_type']]],
        ['data' => $feed['pull_count'] * 100],
        ['data' => $edit_link],
        ['data' => $delete_link],
        ['data' => $export_link],
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


  public function export_feed($id) {

  }

}