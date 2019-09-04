<?php

namespace Drupal\tweet_feed\Controller;

use Drupal\Core\Link;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class ProboRepositoryController.
 */
class TwitterAccountsController extends ControllerBase {

  /**
   * display_accounts().
   *
   * @return array
   *   Return render array of a table of elements that make up the list
   *   of available twitter accounts or an empty list. Designed to be
   *   handled by Drupal's configuration management system.
   */
  public function display_accounts() {
    $config = $this->config('tweet_feed.twitter_accounts');
    
    $header = [
      ['data' => $this->t('Account Name')],
      ['data' => $this->t('Account Machine Name')],
      ['data' => $this->t('Edit')],
      ['data' => $this->t('Delete')],
    ];

    $rows = [];
    $accounts = $config->get('accounts');
    foreach ($accounts as $account_machine_name => $account) {
      $edit_link = Link::createFromRoute($this->t('Edit'), 'tweet_feed.edit_account', ['id' => $account_machine_name]);
      $delete_link = Link::createFromRoute($this->t('Delete'), 'tweet_feed.delete_account', ['id' => $account_machine_name]);
      $row = [
        ['data' => $account['account_name']],
        ['data' => $account_machine_name],
        ['data' => $edit_link],
        ['data' => $delete_link],
      ];
      $rows[] = $row;
    }
    return [
      '#type' => 'table',
      '#attributes' => ['class' => ['table table-striped']],
      '#prefix' => NULL,
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => 'THERE ARE NO TWITTER API ACCOUNTS CURRENTLY CREATED.',
    ];
  }
}