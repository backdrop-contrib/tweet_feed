<?php

namespace Drupal\tweet_feed\Commands;

use Abraham\TwitterOAuth\TwitterOAuth;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class TweetFeedCommands extends DrushCommands {

  /**
   * Import the latest batch of tweets.
   *
   * @param $feed
   *   The machine name of the feed to be imported
   * @usage tweet_feed:import feed1
   *   Import the feeds as configured in machine name feed1.
   *
   * @command tweet_feed:import
   * @aliases tfi
   */
  public function import($feed) {
    $config = \Drupal::service('config.factory')->get('tweet_feed.twitter_accounts');
    $feeds = $config->get('accounts');
    if (!empty($feeds)) {
      foreach ($feeds as $key=>$value) {
        if ($key == $feed) {
          $connection = new TwitterOAuth($value['consumer_key'], $value['consumer_secret'], $value['oauth_token'], $value['oauth_token_secret']);
          $content = $connection->get("account/verify_credentials");
          print_r($content);
        }
      }
    }
  }

  /**
   * An example of the table output format.
   *
   * @param array $options An associative array of options whose values come from cli, aliases, config, etc.
   *
   * @field-labels
   *   group: Group
   *   token: Token
   *   name: Name
   * @default-fields group,token,name
   *
   * @command tweet_feed:token
   * @aliases token
   *
   * @filter-default-field name
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   */
  public function token($options = ['format' => 'table']) {
    $all = \Drupal::token()->getInfo();
    foreach ($all['tokens'] as $group => $tokens) {
      foreach ($tokens as $key => $token) {
        $rows[] = [
          'group' => $group,
          'token' => $key,
          'name' => $token['name'],
        ];
      }
    }
    return new RowsOfFields($rows);
  }
}
