<?php

/**
 * The Tweet Feed Twitter tweet entity.
 * 
 * A number of changes in approach with this reboot of Tweet Feed. Retweet and like counts
 * are no longer stored because they cannot be frequently kept updated and will not reflect
 * the accurate status of the tweet. The goal of Tweet Feed isn't to reproduce twitter or
 * serve as a Twitter "App" per se, but display tweets in the context of a feed using
 * relationships to users, hash tags or other "Twitter centric" criteria. So all of those fields
 * have been removed.
 * 
 * Added are references to quoted tweets and replies so the context of a tweet can be maintained.
 * Note that the quoted or replied-to tweet is kept individually but is not displayed outside
 * the context of the quoted re-tweet or reply.
 */

namespace Drupal\tweet_feed\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Language\LanguageInterface;
use Drupal\user\UserInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

/**
 * Defines the Tweet Feed Tweet entity.
 *
 * @ingroup tweet_feed
 *
 * @ContentEntityType(
 *   id = "tweet_entity",
 *   label = @Translation("Tweet Feed Tweets"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tweet_feed\TweetEntityListBuilder",
 *     "views_data" = "Drupal\tweet_feed\Entity\TweetEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\tweet_feed\Form\TweetEntityForm",
 *       "add" = "Drupal\tweet_feed\Form\TweetEntityForm",
 *       "edit" = "Drupal\tweet_feed\Form\TweetEntityForm",
 *       "delete" = "Drupal\tweet_feed\Form\TweetEntityDeleteForm",
 *     },
 *     "access" = "Drupal\tweet_feed\TweetEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\tweet_feed\TweetEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "tweet_entity",
 *   admin_permission = "administer tweet feed entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "tweet_id",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/tweet_entity/{tweet_entity}",
 *     "add-form" = "/admin/structure/tweet_entity/add",
 *     "edit-form" = "/admin/structure/tweet_entity/{tweet_entity}/edit",
 *     "delete-form" = "/admin/structure/tweet_entity/{tweet_entity}/delete",
 *     "collection" = "/admin/structure/tweet_entity",
 *   },
 *   field_ui_base_route = "tweet_entity.settings"
 * )
 */
class TweetEntity extends ContentEntityBase implements TweetEntityInterface {

  use EntityChangedTrait;

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
  public function getID() {
    return $this->get('id')->entity;
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
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
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
  public function getTweetID() {
    return $this->get('tweet_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTweetID($tweet_id) {
    $this->set('tweet_id', $tweet_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTweetTitle() {
    return $this->get('tweet_title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTweetTite($tweet_title) {
    $this->set('tweet_title', $tweet_title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTweetText() {
    return $this->get('tweet_full_text')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTweetText($tweet_full_text) {
    $this->set('tweet_full_text', $tweet_full_text);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTweetUserProfileID() {
    return $this->get('tweet_user_profile_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTweetUserProfileID($tweet_user_profile_id) {
    $this->set('tweet_user_profile_id', $tweet_user_profile_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTweetUserProfile() {
    $id = $this->get('tweet_user_profile_id');
    return \Drupal::entityTypeManager()->getStorage('user')->load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function getLinkedImages() {
    $files = $this->get('linked_images')->getValue();
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
  public function getLinkedImageURLS() {
    $files = $this->getLinkedImages();
    $urls = [];
    foreach ($files as $file) {
      $file_uri = $file->getFileUri();
      // I can't believe this will survive Drupal 9 but there is no deprecation notice on it yet.
      $urls[] = file_create_url($file_uri);
    }
    return $urls;
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
  public function getHashtags() {
    return $this->getTags('hashtags');
  }

  /**
   * {@inheritdoc}
   */
  public function getUserMentionsTags() {
    return $this->getTags('user_mentions_tags');
  }

  /**
   * {@inheritdoc}
   */
  public function getGeographicCoordinatres() {
    return $this->get('geographic_coordinates')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setGeographicCoordinates($geographic_coordinates) {
    $this->set('geographic_coordinates', $geographic_coordinates);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getGeographicPlace() {
    return $this->get('geographic_place')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setGeographicPlace($geographic_location) {
    $this->set('geographic_place', $geographic_place);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSource() {
    return $this->get('source')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSource($source) {
    $this->set('source', $source);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserMentions() {
    $mentions = $this->get('user_mentions');
    foreach ($mentions as $mentions) {

    }
  }

  /**
   * {@inheritdoc}
   */
  public function isQuotedOrRepliedTweet() {
    return ($this->get('quoted_or_replied_tweet') != 'Off') ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setQuotedOrRepliedTweet($quoted_replied) {
    $this->set('quoted_or_replied_tweet', $quoted_replied);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuotedStatusId() {
    return $this->get('geographic_place')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setQuotedStatusID($geographic_location) {
    $this->set('geographic_place', $geographic_place);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getInReplyToStatusID() {
    return $this->get('in_reply_to_status_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setInReplyToStatusID($in_reply_to_status_id) {
    $this->set('in_reply_to_status_id', $in_reply_to_status_id);
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
      ->setDescription(t('The ID of the tweet entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the tweet entity.'))
      ->setReadOnly(TRUE);

    // Owner field of the contact.
    // Entity reference field, holds the reference to the user object.
    // The view shows the user name field of the user.
    // The form presents a auto complete field for the user name.
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User Name'))
      ->setDescription(t('The Name of the associated user.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => -3,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
        'weight' => -3,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of Contact entity.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['tweet_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tweet ID'))
      ->setDescription(t('The Twitter ID for this tweet.'))
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

    $fields['tweet_title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tweet Title'))
      ->setDescription(t('The cleansed title for this tweet. For administrative use only.'))
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

    $fields['tweet_full_text'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Tweet Full Text'))
      ->setDescription(t('The contents of the tweet. Untruncated.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 7,
      ])
      ->setDisplayOptions('form', [
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
 
    $fields['tweet_user_profile_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tweet Author ID'))
      ->setDescription(t('The Twitter ID of the author of this tweet.'))
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
 
    $fields['linked_images'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Linked Images'))
      ->setDescription(t('Images linked in tweets.'))
      ->setSettings([
        'uri_scheme' => 'public',
        'file_directory' => 'tweet_feed/[date:custom:Y]-[date:custom:m]',
        'alt_field_required' => FALSE,
        'file_extensions' => 'png jpg jpeg gif',
      ])
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'default',
        'weight' => 8,
      ))
      ->setDisplayOptions('form', array(
        'weight' => 8,
      ))
      ->setCardinality(-1)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
 
    $fields['hashtags'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Hashtags Used'))
      ->setDescription(t('Any hashtags that are contained in a tweet.'))
      ->setRevisionable(FALSE)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting('handler_settings', [
        'target_bundles' => [
          'twitter_hashtag_terms' => 'twitter_hashtag_terms',
        ],
      ])
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'weight' => 9,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 9,
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

    $fields['user_mentions'] = BaseFieldDefinition::create('user_mentions_field_type')
      ->setLabel(t('User Mentions'))
      ->setDescription(t('Users mentioned in the tweet'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 11,
      ])
      ->setDisplayOptions('form', [
        'type' => 'user_mentions_field_type',
        'weight' => 11,
      ])
      ->setCardinality(-1)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['geographic_coordinates'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Geographic Coordinates'))
      ->setDescription(t('The geographic coordinates of a tweet if provided.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 13,
      ])
      ->setDisplayOptions('form', [
        'weight' => 13,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['geographic_place'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Geographic Place'))
      ->setDescription(t('The geographic location of a tweet if provided.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 15,
      ])
      ->setDisplayOptions('form', [
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['source'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tweet Source'))
      ->setDescription(t('The name of the application used to generate a tweet.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 16,
      ])
      ->setDisplayOptions('form', [
        'weight' => 16,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['user_mentions_tags'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User Mentions'))
      ->setDescription(t('The users mentioned in a tweet in the form of tags.'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting('handler_settings', [
        'target_bundles' => [
          'twitter_user_mention_terms' => 'twitter_user_mention_terms',
        ],
      ])
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'weight' => 17,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 17,
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

    $fields['quoted_or_replied_tweet'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Quoted or Replied Tweet?'))
      ->setDescription(t('Is this tweet a re-tweet with a comment or a tweet that was replied to? These are not displayed outside the context of the re-tweet.'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'boolean',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  
    $fields['quoted_status_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tweet which was re-tweeted for comments'))
      ->setDescription(t('This is the ID of the tweet which was re-tweeted with comments.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 18,
      ])
      ->setDisplayOptions('form', [
        'weight' => 18,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['in_reply_to_status_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Reply to status id.'))
      ->setDescription(t('This is the ID of the tweet which was being replied to.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 18,
      ])
      ->setDisplayOptions('form', [
        'weight' => 18,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }
}
