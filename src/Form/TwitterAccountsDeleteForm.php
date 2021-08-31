<?php

namespace Drupal\tweet_feed\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\Messenger;

/**
 * Confirmation of Twitter API deletion.
 *
 * @ingroup tweet_feed
 */
class TwitterAccountsDeleteForm extends ConfirmFormBase {

  /**
   * ID of the item to delete.
   *
   * @var string
   */
  protected $account_machine_name;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $account_machine_name = NULL) {
    $this->account_machine_name = $account_machine_name;
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('tweet_feed.twitter_accounts');
    $accounts = $config->get('accounts');
    unset($accounts[$this->account_machine_name]);
    $config->set('accounts', $accounts)->save();
    \Drupal::messenger()->addStatus($this->t('The Twitter API account %account was deleted.', ['%account' => $this->account_machine_name]));
    $form_state->setRedirect('tweet_feed.twitter_accounts');
    return;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "tweet_feed_twitter_accounts_confirm_delete";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('tweet_feed.twitter_accounts');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to delete %account_machine_name?', ['%account_machine_name' => $this->account_machine_name]);
  }

}
