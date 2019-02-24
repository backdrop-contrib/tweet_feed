<?php

namespace Drupal\tweet_feed\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Tweet entity edit forms.
 *
 * @ingroup tweet_feed
 */
class TweetEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\tweet_feed\Entity\TweetEntity */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Tweet entity.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Tweet entity.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.tweet_entity.canonical', ['tweet_entity' => $entity->id()]);
  }

}
