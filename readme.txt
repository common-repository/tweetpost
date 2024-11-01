=== TweetPost ===
Contributors: bbqiguana
Donate link: http://www.bbqiguana.com/donate/
Tags: wordpress, twitter, twitter integration, twitter poster, twitter, tweet, tweet posts, posts, plugin, admin, Post, tweetpost, multiuser, multi-user, bitly, bit.ly, supr, su.pr, stumbleupon, button, tweet button, twitter button
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: 1.3

Multi-user aware Twitter plugin adds a tweet button to posts and/or automatically tweets bit.ly or su.pr links to new posts. 

== Description ==

TweetPost is a multiuser plugin which  allows wordpress publishers to automatically tweet their new 
posts to their Twitter account. Tweets consist of a message ("New post from @user") including a 
reference to the author's Twitter name, the title of the post, and a shortened link to the
post from bit.ly or su.pr.

It can also be used to add Twitter's new Tweet Button to your posts.


Currently, the TweetPost consists of the following features.

* Compatible with Twitter's OAuth API
* Connect a "global" Twitter account to post
* Specify a Bit.ly or Su.pr login and API key to associate with the site
* Adds a "Twitter" property to user details, so users can manage their own Twitter name
* Automatically submits the permalink to bit.ly and adds that to the tweet
* Adds a reference to the author's Twitter account in the tweet
* Fits the tweet into Twitter's 140-character limit.
* Adds a Tweet button to posts
* Button can be floated left or right

= License =

This Twitter Poster plugin and Wordpress Plugin Framework are being developed under the GNU General Public License, version 2.

[GNU General Public License, version 2](http://www.gnu.org/licenses/old-licenses/gpl-2.0.html "GNU General Public License, version 2")

== Installation ==

1. Unzip the archive file.
2. Verify the name of the unzipped folder to be "tweetpost"
3. Upload the "tweetpost" folder to the root of your Wordpress "plugins" folder.
4. Activate the "tweetpost" plugin in your website's plugin administration page.
5. Navigate to the "Settings" ~ "TweetPost" administration page, to add account info.

== Frequently Asked Questions ==

= Do I have to register my blog with Twitter? =

Yes. Due to details of Twitter's implementation of the OAuth protocol, it is necessary to register your blog as a Twitter API consumer application. It's silly, but fortunately it's not difficult to do.

== Change Log ==

= 1.3 =
* Fixed a bug that caused admin notice to display even after the plugin is configured
* Added button style property to config

= 1.2 =
* Bug fixes with Twitter auth
* Bug fixes related to updating old settings

= 1.1 =
* Completely rewired the options page to use WP's Settings API
* Options are now stored in a single array instead of individual settings strings
* All UI showing/hiding moved to javascript
* Added settings icon
* Added Twitter's new Tweet Button

= 1.0 =
* Added support for Twitter's OAuth API.
* Removed support for Twitter's (now defunct) Basic API.
* Added messaging for missing config

= 0.8 =
* Default URL to permalink when shortener fails. Now uses su.pr's new simpleshorten API.

= 0.7 =
* Fixed a bug in the regular expression for Su.pr URLs

= 0.6 =
* Added support for Su.pr url shortening

= 0.5 =
* Added a customizable "intro text" to each tweet

= 0.4 =
* Fixed future_to_publish event

= 0.3 =
*Initialization settings had erroneous preference names

= 0.2 =
* Fixes a bad path in Javascript for validating settings

= 0.1 =
* Initial version

