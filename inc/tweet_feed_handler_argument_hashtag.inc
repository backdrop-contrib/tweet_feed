<?php

/**
 * override the string handler for handling hash tags as we need to change the query 
 * so we can look through the string with a LIKE instead of an =
 */
class tweet_feed_handler_argument_hashtag extends views_handler_argument_string {

	function options_form(&$form, &$form_state) {
		$form['match_exact'] = array(
			'#type' => 'checkbox',
			'#title' => t('Exact Match Mode'),
			'#description' => t('Match the argument exactly. Not as a potential substring'),
			'#default_value' => $this->options['match_exact'],
			'#fieldset' => 'more',
		);
	}

  function query($group_by = FALSE) {  
  	if (empty($this->options['match_exact'])) {
  		$argument = '%' . $this->argument . '%';
    }
    else {
    	$argument = ' ' . $this->argument . ' ';
    }
    
    $this->value = array($argument);
  	$this->operator = 'or';
  
    $this->ensure_my_table();
    $formula = FALSE;

		$field = "$this->table_alias.$this->real_field";
 
    if (count($this->value) > 1) {
      $operator = 'IN';
      $argument = $this->value;
    }
    else {
    	if (empty($this->options['match_exact'])) {
      	$operator = 'LIKE';
      } 
      else {
      	$operator = 'REGEXP';
      }
    }
    
    $this->query->add_where(0, "$field $operator '%s'", $argument);
  }
}