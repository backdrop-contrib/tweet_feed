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
  public function buildForm(array $form, FormStateInterface $form_state, $account_machine_name = NULL) {
    $config = $this->config('tweet_feed.twitter_accounts');
    // Set up our settings form for this particular account (new or update)
    if (!empty($account_machine_name)) {
      $accounts = $config->get('accounts');
      $account = $accounts[$account_machine_name];
      $form['account_machine_name'] = [
        '#type' => 'hidden',
        '#value' => $account_machine_name,
      ];
      $form['account_update'] = [
        '#type' => 'hidden',
        '#value' => 1,
      ];
      $account_name = $account['account_name'];
      $consumer_key = $account['consumer_key'];
      $consumer_secret = $account['consumer_secret'];
      $oauth_token = $account['oauth_token'];
      $oauth_token_secret = $account['oauth_token_secret'];
    }
    else {
      $account_name = $consumer_key = $oauth_token = NULL;
      $consumer_secret = $oauth_token_secret = NULL;
    }

    $form['tweet_feed_account'] = array(
      '#type' => 'fieldset',
      '#title' => t('Tweet Feed Twitter API Account Information Form'),
      '#description' => t('Provide information about the Twitter API account you wish to add. These can be used to get the feeds for any of our configurable options.'),
    );
    $form['tweet_feed_account']['account_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Account Name'),
      '#max_length' => 128,
      '#required' => TRUE,
      '#default_value' => $account_name,
    );
    $form['tweet_feed_account']['consumer_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Consumer Key'),
      '#max_length' => 255,
      '#required' => TRUE,
      '#default_value' => $consumer_key,
    );
    $form['tweet_feed_account']['consumer_secret'] = array(
      '#type' => 'textfield',
      '#title' => t('Consumer Secret'),
      '#max_length' => 255,
      '#required' => TRUE,
      '#default_value' => $consumer_secret,
    );
    $form['tweet_feed_account']['oauth_token'] = array(
      '#type' => 'textfield',
      '#title' => t('Oauth Token'),
      '#max_length' => 255,
      '#required' => TRUE,
      '#default_value' => $oauth_token,
    );
    $form['tweet_feed_account']['oauth_token_secret'] = array(
      '#type' => 'textfield',
      '#title' => t('Oauth Token Secret'),
      '#max_length' => 255,
      '#required' => TRUE,
      '#default_value' => $oauth_token_secret,
    );

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
    
    $values = $form_state->getValues();
    $config = $this->config('tweet_feed.twitter_accounts');
    $accounts = $config->get('accounts');
    $account_machine_name = preg_replace('/[^a-z0-9]+/', '_', strtolower($values['account_name']));

    if (empty($values['account_update']) && !empty($accounts[$account_machine_name])) {
      $suffix = 1;
      do {
        $new_account_machine_name = $account_machine_name . '_' . $suffix;
        $suffix++;
      }
      while (!empty($accounts[$new_account_machine_name]));
      $account_machine_name = $new_account_machine_name;
    }
    
    if (empty($accounts[$account_machine_name])) {
      $accounts[$account_machine_name] = [];
    }
    else {
      $account_machine_name = $values['account_machine_name'];
    }
    $accounts[$account_machine_name]['account_name'] = $values['account_name'];
    $accounts[$account_machine_name]['consumer_key'] = $values['consumer_key'];
    $accounts[$account_machine_name]['consumer_secret'] = $values['consumer_secret'];
    $accounts[$account_machine_name]['oauth_token'] = $values['oauth_token'];
    $accounts[$account_machine_name]['oauth_token_secret'] = $values['oauth_token_secret'];
    $this->config('tweet_feed.twitter_accounts')
      ->set('accounts', $accounts)
      ->save();

    parent::submitForm($form, $form_state);
  }
}