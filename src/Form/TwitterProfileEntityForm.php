<?php

namespace Drupal\tweet_feed\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Tweet entity edit forms.
 *
 * @ingroup tweet_feed
 */
class TwitterProfileEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\tweet_feed\Entity\TwitterProfile */
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
        $this->message->addMessage(
          t('Created the %label Twitter profile entity.', [
            '%label' => $entity->label(),
            ]
          )
        );
        break;

      default:
        $this->message->addMessage(
          t('Saved the %label Twitter profile entity.', [
            '%label' => $entity->label(),
            ]
          )
        );
        break;
    }
    $form_state->setRedirect('entity.twitter_profile.canonical', ['twitter_profile' => $entity->id()]);
  }

}
