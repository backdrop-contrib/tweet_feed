# Tweet Feed 4.x - Overview

The Tweet Feed module is an advanced importing, displaying and data association
module that allows you to pull in tweets by search, user, or list. The
parameters of what is pulled in falls under the guidelines of
[Twitter's REST API](https://dev.twitter.com/rest/public/rate-limiting)

Tweets and Twitter profiles are stored as entities that can be viewed and deleted,
but not modified via the Drupal UI. This is by design as editing tweets is not a
feature currently supported by Twitter and can lead to an artificial representation
of Twitter content. Tweets can be deleted for the purposes of curating the content
on the site. Through the use of views and feed configuration, Tweets can be viewed
and grouped by hashtag or user mention. All hash tags and user mentions are stored
as references in Tweet entities to their corresponding taxonomy vocabulary. This
gives you great power in terms of displaying tweets with specific content in specific
places by leveraging the power of contextual filters and taxonomies.

## Highlights include:

- The ability to import multiple tweet feeds
- Tweets and tweet data are saved as entities outside of node types.
- Option to delete existing data when new tweets are imported
- Option to import a the profiles of Twitter users whose tweets are imported.
- Creates linked URLs from URLs, hash tags, and usernames inside the feed itself
- Views integration
- Contextual filters integration for views

This module exists thanks to the generous support of HighWire Press and
Stanford University.

Contextual views inspiration and refinement compliments of Ashley Hall in
conjunction with the development of the Symposiac conference platform, supported
by the Institute for the Arts and Humanities and UNC.

## Requirements

The following access tokens from Twitter are also required:

- API Key
- API Secret Key
- Access Token
- Access Token Secret

You can get additional information on this by visiting these links in the Twitter
API documentation. An API/Developer account is required to be able to access the
Twitter API.

[Developer Portal](https://developer.twitter.com/en/portal/dashboard)  
[Developer Portal Add New App](https://developer.twitter.com/en/portal/apps/new)

## Current Maintainers

- [Michael Bagnall](https://www.drupal.org/u/elusivemind)

## Credits

- Originally written by [Michael Bagnall](https://github.com/ElusiveMind)
- Initial development sponsored by [Highwire Press](https://highwirepress.com)
- Continuing development sponsored by [ITCON Services, LLC](https://itcon-inc.com)

## License

This project is GPL v2 software.
See the LICENSE.txt file in this directory for complete text.
The TwitterOauth is licensed under the MIT license.
