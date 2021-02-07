<?php

namespace Drupal\tweet_feed\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Twitt3er Profile entity.
 *
 * @ingroup tweet_feed
 *
 * @ContentEntityType(
 *   id = "twitter_profile",
 *   label = @Translation("Twitter Profiles"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tweet_feed\TwitterProfileEntityListBuilder",
 *     "views_data" = "Drupal\tweet_feed\Entity\TwitterProfileEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\tweet_feed\Form\TwitterProfileEntityForm",
 *       "add" = "Drupal\tweet_feed\Form\TwitterProfileEntityForm",
 *       "edit" = "Drupal\tweet_feed\Form\TwitterProfileEntityForm",
 *       "delete" = "Drupal\tweet_feed\Form\TwitterProfiletEntityDeleteForm",
 *     },
 *     "access" = "Drupal\tweet_feed\TwitterProfileEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\tweet_feed\TwitterProfileEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "twitter_profiles",
 *   admin_permission = "administer twitter profile profile entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "screen_name",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/twitter_profiles/{twitter_profiles}",
 *     "add-form" = "/admin/structure/twitter_profiles/add",
 *     "edit-form" = "/admin/structure/twitter_profiles/{twitter_profiles}/edit",
 *     "delete-form" = "/admin/structure/twitter_profiles/{twitter_profiles}/delete",
 *     "collection" = "/admin/structure/twitter_profiles",
 *   },
 *   field_ui_base_route = "twitter_profile.settings"
 * )
 */
class TwitterProfileEntity extends ContentEntityBase implements TwitterProfileEntityInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
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
  public function getId() {
    return $this->get('id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getUuid($uuid) {
    return $this->get('uuid')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setUuid($uuid) {
    $this->set('uuid', $uuid);
    return $this;
  }

  public function getTwitterUserId() {
    return $this->get('twitter_user_id')->value;
  }

  public function setTwitterUserId($twitter_user_id) {
    $this->set('twitter_user_id', $twitter_user_id);
    return $this;
  }

  public function getName() {
    return $this->get('name')->value;
  }

  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  public function getScreenName() {
    return $this->get('screen_name')->value;
  }

  public function setScreenName($screen_name) {
    $this->set('screen_name', $screen_name);
    return $this;
  }

  public function getLocation() {
    return $this->get('locatiom')->value;
  }

  public function setLocation($location) {
    $this->set('location', $location);
    return $this;
  }
  
  public function getDescription() {
    return $this->get('description')->value;
  }

  public function setDescription($description) {
    $this->set('description', $description);
    return $this;
  }

  public function getFollowersCount() {
    return $this->get('followers_count')->value;
  }

  public function setFollowersCount($followers_count) {
    $this->set('followers_count', $followers_count);
    return $this;
  }

  public function getFriendsCount() {
    return $this->get('friends_count')->value;
  }

  public function setFriendsCount($friends_count) {
    $this->set('friends_count', $friends_count);
    return $this;
  }

  public function getListedCount() {
    return $this->get('listed_count')->value;
  }

  public function setListedCount($listed_count) {
    $this->set('listed_count', $listed_count);
    return $this;
  }

  public function getFavoritesCount() {
    return $this->get('favorites_count')->value;
  }

  public function setFavoritesCount($favorites_count) {
    $this->set('favorites_count', $favorites_count);
    return $this;
  }

  public function getVerified() {
    return $this->get('verified')->value;
  }

  public function setVerified($verified) {
    $this->set('verified', $verified);
    return $this;
  }

  public function getStatusesCount() {
    return $this->get('statuses_count')->value;
  }

  public function setStatusesCount($stauses_count) {
    $this->set('statuses_count', $statuses_count);
    return $this;
  }

  public function getHash() {
    return $this->get('hash')->value;
  }

  public function setHash($hash) {
    $this->set('hash', $hash);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setProfileImage($image) {
    $this->set('profile_image', $image);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getProfileImage() {
    $files = $this->get('profile_image')->getValue();
    $images = [];
    foreach ($files as $file) {
      $fo = File::load($file['target_id']);
      $images[] = $fo;
    }
    return $images;
  }

  /**
   * {@inheritdoc}
   */
  public function setBannerImage($image) {
    $this->set('banner_image', $image);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBannerImage() {
    $files = $this->get('banner_image')->getValue();
    $images = [];
    foreach ($files as $file) {
      $fo = File::load($file['target_id']);
      $images[] = $fo;
    }
    return $images;
  }

  public function setFollowers($followers) {
    $tags = [];
    foreach ($followers as $follower) {
      $tags[]['target_id'] = $follower;
    }
    $this->set('followers', $tags);
    return $this;
  }

  public function getFollowers() {
    return $this->getTags('followers');
  }

  public function setFormerFollowers($former_followers) {
    $tags = [];
    foreach ($former_followers as $former_follower) {
      $tags[]['target_id'] = $former_follower;
    }
    $this->set('former_followers', $tags);
    return $this;
  }

  public function getFormerFollowers() {
    return $this->getTags('former_followers');
  }

  /**
   * {@inheritdoc}
   */
  private function getTags($tags) {
    $hashtags = $this->get($tags)->getValue();
    $tags = [];
    if (!empty($hashtags)) {
      foreach($hashtags as $key => $term) {
        $tag = $this->entityTypeManager()->getStorage('taxonomy_term')->load($term['target_id'])->values;
        $tags[]['name'] = $tag['name']['x-default'];
        $tags[]['tid'] = $tag['tid']['x-default'];
      }
    }
    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the tweet entity.'))
      ->setReadOnly(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    // Standard field, unique outside of the scope of the current project.
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last changed.'));

    // Standard field, unique outside of the scope of the current project.
    $fields['user_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('User Id'))
      ->setReadOnly(FALSE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the tweet entity.'))
      ->setReadOnly(TRUE);

    $fields['twitter_user_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Twitter User ID'))
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
      ->setDescription(t('The screen name for this profile.'))
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

    $fields['description'] = BaseFieldDefinition::create('string')
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

    $fields['favorites_count'] = BaseFieldDefinition::create('integer')
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

    $fields['hash'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Hash'))
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
      ->setCardinality(-1)
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
      ->setCardinality(-1)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }
}
