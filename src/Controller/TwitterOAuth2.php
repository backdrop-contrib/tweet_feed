<?php
namespace Drupal\tweet_feed\Controller;

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterOAuth2 extends TwitterOAuth {
  const API_VERSION = '2';

  protected function getUrl(string $host, string $path) {
    return sprintf('%s/%s/%s.json', $host, self::API_VERSION, $path);
  }
}
