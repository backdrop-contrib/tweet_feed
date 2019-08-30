<?php

namespace Drupal\tweet_feed\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'user_mentions_widget_type' widget.
 *
 * @FieldWidget(
 *   id = "user_mentions_widget_type",
 *   label = @Translation("User mentions widget type"),
 *   field_types = {
 *     "user_mentions_field_type"
 *   }
 * )
 */
class UserMentionsWidgetType extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => 60,
      'placeholder' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['size'] = [
      '#type' => 'number',
      '#title' => t('Size of textfield'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 1,
    ];
    $elements['placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Textfield size: @size', ['@size' => $this->getSetting('size')]);
    if (!empty($this->getSetting('placeholder'))) {
      $summary[] = t('Placeholder: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['mention_name'] = [
      '#type' => 'textfield',
      '#title' => 'The name of a person mentioned in this tweet.',
      '#default_value' => isset($items[$delta]->mention_name) ? $items[$delta]->mention_name : NULL,
      '#size' => 60,
      '#maxlength' => 255,
    ];
    $element['mention_screen_name'] = [
      '#type' => 'textfield',
      '#title' => 'The screen name of the person mentioned in this tweet.',
      '#default_value' => isset($items[$delta]->mention_screen_name) ? $items[$delta]->mention_screen_name : NULL,
      '#size' => 60,
      '#maxlength' => 255,
    ];
    $element['mention_id'] = [
      '#type' => 'textfield',
      '#title' => 'The twitter numerical id of the person mentioned in this tweet.',
      '#default_value' => isset($items[$delta]->mention_id) ? $items[$delta]->mention_id : NULL,
      '#size' => 60,
      '#maxlength' => 255,
    ];

    // If cardinality is 1, ensure a label is output for the field by wrapping
    // it in a details element.
    if ($this->fieldDefinition->getFieldStorageDefinition()->getCardinality() == 1) {
      $element += [
        '#type' => 'fieldset',
        '#attributes' => array('class' => array('container-inline')),
      ];
    }
    return $element;
  }

}
