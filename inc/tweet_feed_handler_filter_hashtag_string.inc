<?php

class tweet_feed_handler_filter_hashtag_string extends views_handler_filter_string {
  function op_contains($field) {
    $this->query->add_where($this->options['group'], $field, array($this->value), 'IN');
  }
}