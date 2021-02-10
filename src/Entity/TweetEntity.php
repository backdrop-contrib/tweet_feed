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
 *     "label" = "tweet_title",
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

  /**
   * {@inheritdoc}
   */
  public function getFeedMachineName() {
    return $this->get('feed_machine_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setFeedMachineName($feed_machine_name) {
    $this->set('feed_machine_name', $feed_machine_name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTweetId() {
    return $this->get('tweet_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTweetId($tweet_id) {
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
  public function setTweetTitle($tweet_title) {
    $this->set('tweet_title', $tweet_title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTweetFullText() {
    return $this->get('tweet_full_text')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTweetFullText($tweet_full_text) {
    $this->set('tweet_full_text', $tweet_full_text);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTweetUserProfileId() {
    return $this->get('tweet_user_profile_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTweetUserProfileId($tweet_user_profile_id) {
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
  public function getIsVerifiedUser() {
    return ($this->get('is_verified_user') != 'Off') ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setIsVerifiedUser($is_verified_user) {
    $this->set('is_verified_user', $is_verified_user);
    return $this;
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
  public function setLinkedImages($images) {
    $this->set('linked_images', $images);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLinkedImageUrls() {
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
  public function setHashtags($hashtags) {
    $tags = [];
    foreach ($hashtags as $hashtag) {
      $tags[]['target_id'] = $hashtag;
    }
    $this->set('hashtags', $tags);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserMentions() {
    return $this->get('user_mentions');
  }

  /**
   * {@inheritdoc}
   */
  public function setUserMentions($user_mentions) {
    $um = [];
    if (count($user_mentions) > 0) {
      foreach ($user_mentions as $user_mention) {
        $um[] = [
          'mention_name' => tweet_feed_filter_iconv_text(tweet_feed_filter_smart_quotes($user_mention->name)),
          'mention_screen_name' => tweet_feed_filter_iconv_text(tweet_feed_filter_smart_quotes($user_mention->screen_name)),
          'mention_id' => $user_mention->id_str,
        ];
      }
    }
    $this->set('user_mentions', $um);
  }

  public function setUserMentionsTags($user_mentions_tags) {
    $tags = [];
    foreach ($user_mentions_tags as $user_mentions_tag) {
      $tags[]['target_id'] = $user_mentions_tag;
    }
    $this->set('user_mentions_tags', $tags);
    return $this;
  }

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
  public function setGeographicPlace($geographic_place) {
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

  public function setTypeOfTweetReference($reference) {
    $this->set('type_of_tweet_reference', $reference);
    return $this;
  }

  public function getTypeOfTweetReference() {
    return $this->get('type_of_tweet_reference');
  }

  /**
   * {@inheritdoc}
   */
  public function getReferencedTweetId() {
    return $this->get('referenced_tweet_id');
  }

  /**
   * {@inheritdoc}
   */
  public function setReferencedTweetId($id) {
    $this->set('referenced_tweet_id', $id);
    return $this;
  }

  public function getLinkToTweet() {
    return $this->get('link_to_tweet')->value;
  }

  public function setLinkToTweet($link_to_tweet) {
    $this->set('link_to_tweet', $link_to_tweet);
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
      ->setDescription(t('The user id of the owner of this tweet.'))
      ->setReadOnly(FALSE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the tweet entity.'))
      ->setReadOnly(TRUE);

    $fields['feed_machine_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Feed machine name'))
      ->setDescription(t('The machine name of the feed that owns this tweet.'))
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

    $fields['tweet_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tweet ID'))
      ->setDescription(t('The Twitter ID for this tweet.'))
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

    $fields['tweet_title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tweet Title'))
      ->setDescription(t('The cleansed title for this tweet. For administrative use only.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayOptions('form', [
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['tweet_full_text'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Tweet Full Text'))
      ->setDescription(t('The contents of the tweet. Untruncated.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 15,
      ])
      ->setDisplayOptions('form', [
        'weight' => 15,
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
        'weight' => 20,
      ])
      ->setDisplayOptions('form', [
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['is_verified_user'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Is this a verified user?'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'boolean',
        'weight' => 25,
      ])
      ->setDisplayOptions('form', [
        'weight' => 25,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['linked_images'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Linked Images'))
      ->setDescription(t('Images linked in tweets.'))
      ->setSettings([
        'uri_scheme' => 'public',
        'file_directory' => 'tweet-feed-tweet-images/[date:custom:Y]-[date:custom:m]',
        'alt_field_required' => FALSE,
        'file_extensions' => 'png jpg jpeg gif',
      ])
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'default',
        'weight' => 30,
      ))
      ->setDisplayOptions('form', array(
        'weight' => 30,
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
        'weight' => 35,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 35,
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
        'weight' => 40,
      ])
      ->setDisplayOptions('form', [
        'type' => 'user_mentions_field_type',
        'weight' => 40,
      ])
      ->setCardinality(-1)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['geographic_coordinates'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Geographic Coordinates'))
      ->setDescription(t('The geographic coordinates of a tweet if provided.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 45,
      ])
      ->setDisplayOptions('form', [
        'weight' => 45,
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
        'weight' => 50,
      ])
      ->setDisplayOptions('form', [
        'weight' => 50,
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
        'weight' => 55,
      ])
      ->setDisplayOptions('form', [
        'weight' => 55,
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
        'weight' => 60,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 60,
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

    $fields['type_of_tweet_reference'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Type of Tweet Reference'))
      ->setDescription(t('Is this a re-tweet with a comment, a tweet that was replied to, or a quoted tweet? Helps provide minimal context.'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 65,
      ])
      ->setDisplayOptions('form', [
        'weight' => 65,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setSettings([
        'allowed_values' => [
          'replied' => t('Reply'),
          'retweeted' => t('Re-Tweet'),
          'quoted' => t('Quoted Re-Tweet')
        ]
      ])
      ->setDefaultValue([
        ['value' => 'standard'],
      ]);

    $fields['referenced_tweet_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Referenced Tweet ID'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 70,
      ])
      ->setDisplayOptions('form', [
        'weight' => 70,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['link_to_tweet'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tweet Link'))
      ->setDescription(t('The URL that will take the user to the tweet on Twitter.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'weight' => 75,
      ])
      ->setDisplayOptions('form', [
        'weight' => 75,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['profile_image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Profile Image'))
      ->setDescription(t('The Profile Image of the Tweeter.'))
      ->setSettings([
        'uri_scheme' => 'public',
        'file_directory' => 'tweet-feed-tweet-profile-images/[date:custom:Y]-[date:custom:m]',
        'alt_field_required' => FALSE,
        'file_extensions' => 'png jpg jpeg gif',
      ])
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'default',
        'weight' => 85,
      ))
      ->setDisplayOptions('form', array(
        'weight' => 85,
      ))
      ->setCardinality(1)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }
}
