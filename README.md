# Tweet Feeed 4.x

## Advanced Twitter Data Management and Display in Drupal

### INTRODUCTION

* * *

The Tweet Feed module is an advanced importing, displaying and data association tool that allows you to pull in tweets by search criteria, user timeline, or user curated list. The parameters and limits of what is pulled in falls under the guidelines of Twitter's REST API.  

Tweets and Twitter Profiles are stored as their own entities but can be displayed via Views and any other contributed module that hooks into the entity API. All hash tags and user mentions are stored as references in the ewntities to their corresponding taxonomy term. This gives you great power in terms of displaying tweets with specific content in specific places by leveraging the power of contextual filters and taxonomies.  

To install this module and it's corresponding dependencies, you will use composer:  

composer require drupal/tweet_feed  

Once installed you can configure things accordingly. For illustrated documentation on how to configure tweet feed, please see the GitHub WIKI for this project by [clicking here](https://github.com/ElusiveMind/tweet_feed/wiki "GitHub Wiki for Tweet Feed")  

Pre-requisites:  
	1. Drupal 7 for versions 7.1+ and 7.3+  
	2. Drupal 8.8+ or Drupal 9+ for version 4.x  
	3. PHP 7.3 Or Higher  
	4. A Twitter developers account for version 2 of the Twitter API.  
	5. [Twitter OAuth Rest API SDK For PHP](https://github.com/abraham/twitteroauth)  

Complete documentation is on the Drupal.org site or the [GitHub Wiki for Tweet Feed](https://github.com/ElusiveMind/tweet_feed/wiki "GitHub Wiki for Tweet Feed"). Code contributions should be made on the GitHub project site:  

https://github.com/ElusiveMind/tweet_feed  

Issues can be filed there or on the Tweet Feed Issue Queue on Drupal.org.  