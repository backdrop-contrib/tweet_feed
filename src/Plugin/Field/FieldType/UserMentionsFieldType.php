<?php

namespace Drupal\tweet_feed\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'user_mentions_field_type' field type.
 *
 * @FieldType(
 *   id = "user_mentions_field_type",
 *   label = @Translation("User mentions"),
 *   description = @Translation("A collection of fields that make up user mentions."),
 *   default_widget = "user_mentions_widget_type",
 *   default_formatter = "user_mentions_formatter_type"
 * )
 */
class UserMentionsFieldType extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'max_length' => 255,
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['mention_name'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('User Mentioned'))
      ->setDescription(new TranslatableMarkup('The name of a user mentioned in this tweet.'))
      ->setRequired(FALSE);

    $properties['mention_screen_name'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Screen Name'))
      ->setDescription(new TranslatableMarkup('The screen name name of a user mentioned in this tweet.'))
      ->setRequired(FALSE);

    $properties['mention_id'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('User Mentioned'))
      ->setDescription(new TranslatableMarkup('The internal Twttter ID  of a user mentioned in this tweet.'))
      ->setRequired(FALSE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'mention_name' => [
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ],
        'mention_screen_name' => [
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ],
        // We have to leave mentions at varchar because of windows lack of handling
        // of 64 bit integers. Otherwise we run into problems with it.
        'mention_id' => [
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();

    if ($max_length = $this->getSetting('max_length')) {
      $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
      $constraints[] = $constraint_manager->create('ComplexData', [
        'mention_name' => [
          'Length' => [
            'max' => $max_length,
            'maxMessage' => t('%name: may not be longer than @max characters.', [
              '%name' => $this->getFieldDefinition()->getLabel(),
              '@max' => $max_length
            ]),
          ],
        ],
      ]);
    }

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $elements = [];

    $elements['max_length'] = [
      '#type' => 'number',
      '#title' => t('Maximum length'),
      '#default_value' => $this->getSetting('max_length'),
      '#required' => TRUE,
      '#description' => t('The maximum length of the field in characters.'),
      '#min' => 1,
      '#disabled' => $has_data,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $mention_name = $this->get('mention_name')->getValue();
    $mention_screen_name = $this->get('mention_screen_name')->getValue();
    $mention_id = $this->get('mention_id')->getValue();
    return empty($mention_name) && empty($mention_screen_name) && empty($mention_id);
  }

}
