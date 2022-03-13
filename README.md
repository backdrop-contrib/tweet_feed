# Tweet Feed v1.x - Overview

The Tweet Feed module is an advanced importing, displaying and data association
module that allows you to pull in tweets by search, user, or list. The
parameters of what is pulled in falls under the guidelines of
[Twitter's REST API](https://dev.twitter.com/rest/public/rate-limiting)

Tweets can be displayed as nodes or in views as well as displayed by hash tag
or user mention. All hash tags and user mentions are stored as references in
the tweet nodes to their corresponding taxonomy term. This gives you great
power in terms of displaying tweets with specific content in specific places
by leveraging the power of contextual filters and taxonomies.

## Highlights include:

- ability to import multiple tweet feeds
- tweets and tweet data are saved as nodes
- option to delete existing data when new tweets are imported
- option to import a node for each user in your tweet feed
- creates linked URLs from URLs, hash tags, and usernames inside the feed itself
- views integration
- contextual filters integration for views

This module exists thanks to the generous support of HighWire Press and
Stanford University.

Contextual views inspiration and refinement compliments of Ashley Hall in
conjunction with the development of the Symposiac conference platform, supported
by the Institute for the Arts and Humanities and UNC.

## Requirements

This module requires the following modules are also enabled:

- [entityreference](https://github.com/backdrop-contrib/entityreference)
- [oauth](https://github.com/backdrop-contrib/oauth)
- [date](https://github.com/backdrop-contrib/date)
- [ctools](https://github.com/backdrop-contrib/ctools)
- [views](https://github.com/backdrop-contrib/views)

The following access tokens from Twitter are also required:

- API Key
- API Secret Key
- Access Token
- Access Token Secret

## Current Maintainers

- [Michael Bagnall](https://github.com/ElusiveMind)

## Credits

- Ported to Backdrop CMS by [Michael Bagnall](https://github.com/ElusiveMind)
- Originally written for Drupal by [Michael Bagnall](https://github.com/ElusiveMind)
- Initial development sponsored by [Highwire Press](https://highwirepress.com)

## License

This project is GPL v2 software.
See the LICENSE.txt file in this directory for complete text.
The TwitterOauth is licensed under the MIT license.
