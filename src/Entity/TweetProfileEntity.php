<?php

namespace Drupal\tweet_feed\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Tweet Feed Profile entity.
 *
 * @ingroup tweet_feed
 *
 * @ContentEntityType(
 *   id = "tweet_profile_entity",
 *   label = @Translation("Tweet Feed Profiles"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tweet_feed\TweetProfileEntityListBuilder",
 *     "views_data" = "Drupal\tweet_feed\Entity\TweetProfileEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\tweet_feed\Form\TweetProfileEntityForm",
 *       "add" = "Drupal\tweet_feed\Form\TweetProfileEntityForm",
 *       "edit" = "Drupal\tweet_feed\Form\TweetProfileEntityForm",
 *       "delete" = "Drupal\tweet_feed\Form\TweeProfiletEntityDeleteForm",
 *     },
 *     "access" = "Drupal\tweet_feed\TweetProfileEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\tweet_feed\TweetProfileEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "tweet_profile_entity",
 *   admin_permission = "administer tweet feed profile entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "user_id",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/tweet_profile_entity/{tweet_profile_entity}",
 *     "add-form" = "/admin/structure/tweet_profile_entity/add",
 *     "edit-form" = "/admin/structure/tweet_profile_entity/{tweet_profile_entity}/edit",
 *     "delete-form" = "/admin/structure/tweet_profile_entity/{tweet_profile_entity}/delete",
 *     "collection" = "/admin/structure/tweet_profile",
 *   },
 *   field_ui_base_route = "tweet_profile_entity.settings"
 * )
 */
class TweetProfileEntity extends ContentEntityBase implements TweetProfileEntityInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct($user_id) {

  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserID() {
    return $this->get('user_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setUserID($user_id) {
    $this->set('user_id', $user_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Contact entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Contact entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Twitter User ID'))
      ->setDescription(t('The Twitter ID for this profile.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name used on this profile.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['screen_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Screen Name'))
      ->setDescription(t('The screen name for this twitter profile.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['location'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Listed Profile Location'))
      ->setDescription(t('The location of the user of this profile.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Description'))
      ->setDescription(t('The description/information text under the profile name for this profile.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['followers_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Number of Followers'))
      ->setDescription(t('The number of followers this profile has.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['friends_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Number of Friends'))
      ->setDescription(t('The number of friends of this profile.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['listed_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Listed Count'))
      ->setDescription(t('Number of times this profile is listed?'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 7,
      ])
      ->setDisplayOptions('form', [
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 8,
      ])
      ->setDisplayOptions('form', [
        'weight' => 8,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['favourites_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Number of Favorites'))
      ->setDescription(t('The number of favorites for this profile.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 9,
      ])
      ->setDisplayOptions('form', [
        'weight' => 9,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['verified'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Twitter Author Verified'))
      ->setDescription(t('Is this author verified?'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'boolean',
        'weight' => 10,
      ])
      ->setDisplayOptions('form', [
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['statuses_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Tweet Count'))
      ->setDescription(t('The number of tweets for this profile.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 11,
      ])
      ->setDisplayOptions('form', [
        'weight' => 11,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['profile_image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Profile Image'))
      ->setDescription(t('The user profile image.'))
      ->setSettings([
        'uri_scheme' => 'public',
        'file_directory' => 'tweet_feed/[date:custom:Y]-[date:custom:m]',
        'alt_field_required' => FALSE,
        'file_extensions' => 'png jpg jpeg gif',
      ])
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'default',
        'weight' => 12,
      ))
      ->setDisplayOptions('form', array(
        'weight' => 12,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['banner_image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Banner Image'))
      ->setDescription(t('The banner image for this user\'s profile.'))
      ->setSettings([
        'uri_scheme' => 'public',
        'file_directory' => 'tweet_feed/[date:custom:Y]-[date:custom:m]',
        'alt_field_required' => FALSE,
        'file_extensions' => 'png jpg jpeg gif',
      ])
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'default',
        'weight' => 13,
      ))
      ->setDisplayOptions('form', array(
        'weight' => 13,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['followers'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Followers'))
      ->setDescription(t('Current followers.'))
      ->setRevisionable(FALSE)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting('handler_settings', [
        'target_bundles' => [
          'twitter_followers' => 'twitter_followers',
        ],
      ])
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'weight' => 14,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 14,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['former_followers'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Former Followers'))
      ->setDescription(t('They were followers, but they are not anymore!'))
      ->setRevisionable(FALSE)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting('handler_settings', [
        'target_bundles' => [
          'former_followers' => 'former_followers',
        ],
      ])
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'weight' => 15,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 15,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }
}
