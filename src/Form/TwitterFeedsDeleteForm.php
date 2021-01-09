<?php

namespace Drupal\tweet_feed\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Confirmation of Twitter API deletion.
 *
 * @ingroup tweet_feed
 */
class TwitterFeedsDeleteForm extends ConfirmFormBase {

  /**
   * ID of the item to delete.
   *
   * @var string
   */
  protected $feed_machine_name;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $feed_machine_name = NULL) {
    $this->feed_machine_name = $feed_machine_name;
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('tweet_feed.twitter_feeds');
    $feeds = $config->get('feeds');
    unset($feeds[$this->feed_machine_name]);
    $config->set('feeds', $feeds)->save();
    drupal_set_message($this->t('The Twitter Feed %feed was deleted.', [
      '%feed' => $this->feed_machine_name,
    ]));
    $form_state->setRedirect('tweet_feed.twitter_feeds');
    return;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "tweet_feed_twitter_feeds_confirm_delete";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('tweet_feed.twitter_feeds');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to delete %feed_machine_name?', ['%feed_machine_name' => $this->feed_machine_name]);
  }

}
