Tweet Feed v2.0
==========

The Tweet Feed module enables importing a feed using Twitter’s API v1.1. Highlights include:

 * ability to import multiple tweet feeds
 * tweets and tweet data are saved as nodes
 * option to delete existing data when new tweets are imported 
 * option to import a node for each user in your tweet feed
 * creates linked URLs from URLs, hash tags, and usernames inside the feed itself
 * views integration
 * contextual filters integration for views

Tweet Feed was designed and written by Michael Bagnall: https://www.drupal.org/u/elusivemind.

This module exists thanks to the generous support of HighWire Press and Stanford University.

Contextual views inspiration and refinement compliments of Ashley Hall in conjunction with the development of the Symposiac conference platform, supported by the Institute for the Arts and Humanities and UNC.

Dependencies
------------
 * ctools
 * date
 * entity
 * entityreference
 * features
 * oauth 
 * strongarm
 * views
 
The following access tokens from Twitter are also required:
 * API Key
 * API Secret Key
 * Access Token
 * Access Token Secret

Install
-------
Install the Tweet Feed module:

1) Copy the tweet_feed folder to the modules folder in your installation.

2) Download and enable all dependencies.

3) Enable the module using Administer -> Modules (/admin/build/modules).


Configuration
-------------

Configure the Tweet Feed module using Administer -> Configuration -> Web Services -> Tweet Feed.

The Tweet Feed configuration page has three tabs. 

1) On the first tab you can select whether or not you want to create a node for each unique tweeter found in your tweet feed. 

2) On the second tab, you can add one (or more) twitter accounts. This is where you will enter the access tokens provided by Twitter. First, click the “Add Account” link. You will be asked to provide a name for the Twitter account you are using and you will need to enter the API Key (Consumer Key), the API Secret Key (Consumer Secret), the Access Token (Oauth Token), and Access Token Secret (Oauth Token Secret). 

3) On the third tab, you can add one (or more) twitter feeds. First, click the “Add Feed” link.  You will be asked to name the feed, select the API account to use for pulling this feed, the type of twitter query you want to use for this feed, and the number of items to pull each time cron runs. Note that Twitter limits the number of tweets that can be pulled to 1500 every 15 minutes. There are also options to open links in a new window and to remove all previously imported tweets in this feed prior to each import. By default the tweets will remain in the database but this behavior can be adjusted by selecting the appropriate check box at admin/config/services/tweet_feed/feeds/add

 * By default all the fields imported in the twitter feeds are displayed on Tweet Feed nodes. The display options can be adjusted by administering the display of the content type at Administer -> Structure -> Content Types -> Twitter Tweet Feed -> manage display


Views Integration
-----------------
Tweet Feed has full views integration. A basic view of tweets can be created at Administer -> Structure -> Views -> Add New View by selecting Twitter Tweet Feed as the content type to show in the view.

Further filtering can be accomplished using either the standard filters or the contextual filters available in the collapsed advanced fieldset. An example use case of when contextual features would be helpful is to add a block of dynamically filtered tweets based on matching a hashtag entered in a given field on any node.

Disabling the Tweet Feed module
-------------------------------
Disable the Tweet Feed module at Administer -> Modules (/admin/build/modules).

As long as the Tweet Feed module is enabled, there is no delete link presented when viewing the content types at Administer -> Structure Content Types (admin/structure/types). After the Tweet Feed module is disabled, options to delete one or both content types created by the Tweet Feed module become available. By default, content that has been imported using the Tweet Feed module is preserved but it may be deleted.

Upgrading from version 7.x-1.x of Tweet Feed to 7.x-2.x
----------------------------------------------------
Tweet Feed underwent a major rewrite between version 7.x-1.x and 7.x-2.x 

As of version 2 of Tweet Feed, individual tweets are imported as nodes and offers an additional option of creating a profile node for each tweeter appearing in the feed.

There is no direct upgrade path from 7.x-1.x to 7.x-2.x. Any data imported using 7.x-1.x will be lost by upgrading to 7.x-2.x.

1) Begin by disabling the Tweet Feed module on the Administer -Modules page (/admin/build/modules).

2) Next, uninstall the Tweet Feed module using the uninstall tab on the modules page (admin/modules/uninstall). Do not skip this step. If this step is skipped you will get an error when trying to install version 2 of Tweet Feed.

3) After the module has been uninstalled, delete the module files.

4) Version 2 of the Tweet Feed modules has new dependencies. You will be asked if you want to download any unmet dependencies. You will need to download and install all dependencies before completing the upgrade to version 2 of the Tweet Feed module. New dependencies include:
 * entity
 * entity reference
 * date
 * features
 * strongarm

5) Copy the tweet_feed folder for version 7.x-2.x to the modules folder in your installation.

6) Enable the module and configure following the normal installation process (outlined above).
