<?php

namespace Drupal\tweet_feed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Form controller for Tweet entity edit forms.
 *
 * @ingroup tweet_feed
 */
class TwitterAccountsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tweet_feed.twitter_accounts',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tweet_feed_twitter_accounts';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    
    //$this->config('probo.probosettings')
    //  ->set('jira_enabled', $form_state->getValue('jira_enabled'))
    //  ->save();
  }
}
