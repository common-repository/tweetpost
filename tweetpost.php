<?php

/* 
Copyright 2009  Randy Hunt  (email : bbqiguana@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

Plugin Name: TweetPost
Plugin URI: http://www.bbqiguana.com/wordpress-plugins/tweetpost/
Description: Tweets a link to your post when a new post is made on your blog
	and/or adds a Tweet button to your posts
Author: Randy Hunt
Version: 1.3
*/

require_once('twitter/OAuth.php');
require_once('twitter/twitteroauth.php');

register_activation_hook  (__FILE__, 'tweetpost_activate'  );
register_deactivation_hook(__FILE__, 'tweetpost_deactivate');

function tweetpost_activate () {
	$options = get_option('tweetpost');
	if (!$options['showbutton']) {
		$options = array();
		$options['twitter_key'   ] = get_option('tweetpost_twitter_key'   );
		$options['twitter_secret'] = get_option('tweetpost_twitter_secret');
		$options['twitter_uid'   ] = get_option('tweetpost_twitter_uid'   );
		$options['twitter_user'  ] = get_option('tweetpost_screen_name'   );
		$options['bitly_username'] = get_option('tweetpost_bitly_user'    );
		$options['bitly_apikey'  ] = get_option('tweetpost_bitly_apikey'  );
		$options['supr_username' ] = get_option('tweetpost_supr_user'     );
		$options['supr_apikey'   ] = get_option('tweetpost_supr_apikey'   );
		$options['url_shortener' ] = get_option('tweetpost_url_shortener' );
		$options['intro_text'    ] = get_option('tweetpost_opt_introtxt'  );
		if (!$options['shortener']) $options['shortener'] = 'bit.ly';
		if (!$options['introtext']) $options['introtext'] = 'New post from @username: ';
		$options['showbutton' ] = 'no';
		$options['buttonclass'] = 'tweet_button';
		$options['buttonfloat'] = 'right';
		$options['countstyle']  = 'vertical';
		add_option('tweetpost', $options);
	}
}

function tweetpost_deactivate() {
	//
}

function tweetpost_accept_twitter () {
	$options = get_option('tweetpost');	
	$twitter_key    = $options['twitter_key'   ];
	$twitter_secret = $options['twitter_secret'];
	$request_token  = $options['request_token' ];
	$request_secret = $options['request_secret'];
	$connection = new TwitterOAuth($twitter_key, $twitter_secret, $request_token, $request_secret);
	$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

	$options['request_token' ] = '';
	$options['request_secret'] = '';
	$options['access_token'  ] = $access_token['oauth_token'       ];
	$options['access_secret' ] = $access_token['oauth_token_secret'];
	$options['twitter_uid'   ] = $access_token['user_id'           ];
	$options['twitter_user'  ] = $access_token['screen_name'       ];
	update_option('tweetpost', $options);
	header('Location: ' . TWEETPOST_PAGE);
	die();
}

function tweetpost_authenticate_twitter () {
	$options = get_option('tweetpost');
	$connection = new TwitterOAuth($options['twitter_key'], $options['twitter_secret']);
	$request_token = $connection->getRequestToken(TWEETPOST_PAGE);
	if($connection->http_code = '200' && is_array($request_token)) {
		$token = $request_token['oauth_token'];
		$options['request_token' ] = $token;
		$options['request_secret'] = $request_token['oauth_token_secret'];
		update_option('tweetpost', $options);
		//update_option('tweetpost_request_token', $token);
		//update_option('tweetpost_request_secret', $request_token['oauth_token_secret']);
		$url = $connection->getAuthorizeUrl($token);
		header('Location: ' . $url);
		die();
	} else {
		echo '<strong>error connecting to Twitter.</strong>';
	}
}

if ( is_admin() ) 
{
	define('TWEETPOST_PATH', get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)));
	define('TWEETPOST_PAGE', get_option('siteurl') . '/wp-admin/options-general.php?page=tweetpost');
	
	if ($_GET['page'] == 'tweetpost' && $_GET['oauth']=='authenticate') {
		tweetpost_authenticate_twitter();
	}
	if ($_REQUEST['page'] == 'tweetpost' && $_REQUEST['action'] == 'clearuser') {
		tweetpost_clear_user();
	}
	if (!empty($_REQUEST['oauth_token']) && !empty($_REQUEST['oauth_verifier'])) {
		tweetpost_accept_twitter();
	}

	add_action('admin_menu', 'tweetpost_menu');
	add_action('admin_init', 'tweetpost_init');
	
	wp_enqueue_script("jquery");
	wp_enqueue_script("tweetpost", TWEETPOST_PATH . '/tweetpost.js', array('jquery'), '1.0');

	function tweetpost_menu () {
		if ( function_exists('add_options_page') ) {
			add_options_page('TweetPost', 'TweetPost', 8, 'tweetpost', 'tweetpost_options');
		}
	}
	
	function tweetpost_init () {
		register_setting('tweetpost', 'tweetpost');
		add_settings_section('twitter', 'Twitter Settings', 'tweetpost_render_fn', __FILE__);
		add_settings_field('key', 'Twitter Consumer Key',    'tweetpost_render_twitter_key',    __FILE__, 'twitter');
		add_settings_field('sec', 'Twitter Consumer Secret', 'tweetpost_render_twitter_secret', __FILE__, 'twitter');
		add_settings_field('usr', 'Global Twitter Account',  'tweetpost_render_twitter_user',             __FILE__, 'twitter');
		add_settings_field('intro', 'Intro text', 'tweetpost_render_introtext', __FILE__, 'opts');

		add_settings_section('url', 'URL Shortener', 'tweetpost_render_fn', __FILE__);
		add_settings_field('shortener', 'URL Shortener',   'tweetpost_render_shortener', __FILE__, 'url');
		add_settings_field('bitlyname', 'Bit.ly username', 'tweetpost_render_bitly_username', __FILE__, 'url');
		add_settings_field('bitlykey',  'Bit.ly API key',  'tweetpost_render_bitly_apikey',   __FILE__, 'url');
		add_settings_field('suprname',  'Su.pr username',  'tweetpost_render_supr_username',  __FILE__, 'url');
		add_settings_field('suprkey',   'Su.pr API key',   'tweetpost_render_supr_apikey',    __FILE__, 'url');

		//add_settings_section('opts', 'Options', 'tweetpost_render_fn', __FILE__);
		add_settings_section('button', 'Tweet Button', 'tweetpost_render_fn', __FILE__);
		add_settings_field('showbutton',  'Show Tweet button', 'tweetpost_render_showbutton',  __FILE__, 'button');
		add_settings_field('buttonfloat', 'Button float',      'tweetpost_render_buttonfloat', __FILE__, 'button');
		add_settings_field('buttonclass', 'Button DIV class',  'tweetpost_render_buttonclass', __FILE__, 'button');
		add_settings_field('countstyle',  'Count alignment',   'tweetpost_render_countstyle',  __FILE__, 'button');
	}

	function tweetpost_adminnotice () {
 		$options = get_option('tweetpost');
		if ($_REQUEST['page'] != 'tweetpost' && empty($options)) {
			$twitter_user = get_option('tweetpost_twitter_user');
			if(!empty($twitter_user)) {
				echo '<div class="error"><p>The settings for Tweetpost have changed. <a href="'.TWEETPOST_PAGE.'">Click here</a> to configure the plugin.</p></div>';
			} else {
				echo '<div class="updated"><p>Tweetpost is not configured. <a href="'.TWEETPOST_PAGE.'">Click here</a> to configure the plugin.</p></div>';
			}
		}
	}

	function tweetpost_options () {
		echo "<div class=\"wrap\">";
		echo "<div class=\"icon32\" id=\"icon-options-general\"><br></div>";
		echo "<h2>Tweetpost</h2>";

		echo '<form name="tweetpost-options" method="post" action="options.php">';
		settings_fields('tweetpost');
		do_settings_sections(__FILE__);
		echo '<p class="submit">';
		echo '<input name="Submit" type="submit" class="button-primary" value="' . __('Save Changes') . '" />';
		echo '</p>';
		echo '</form>';
		echo '</div>';

		?><div class="wrap">
		<big>Donate</big>
		<p>If you like this plugin consider donating a small amount to the author using PayPal to support further plugin development.</p>
		<div align="center"><form name="_xclick" action="https://www.paypal.com/us/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_xclick"><input type="hidden" name="business" value="bbqiguana@gmail.com"><input type="hidden" name="item_name" value="Donations for TweetPost plugin"><input type="hidden" name="currency_code" value="USD"><input type="image" src="http://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!"></form></div>
		<p>If you think donating money is somehow impersonal you could also choose items from my <a href="http://www.amazon.com/registry/wishlist/18LMHOMRM49P8/ref=cm_wl_act_vv">Amazon.com wishlist</a>.</p>
	</div>
		<?php
	}
	
	function tweetpost_render_fn () {
		//
	}

	function tweetpost_render_twitter_key () {
		$options = get_option('tweetpost');
		echo "<input type=\"text\" class=\"regular-text\" id=\"twitter_key\" name=\"tweetpost[twitter_key]\" value=\"{$options['twitter_key']}\">";
	}

	function tweetpost_render_twitter_secret () {
		$options = get_option('tweetpost');
		echo "<input type=\"text\" class=\"regular-text\" id=\"twitter_secret\" name=\"tweetpost[twitter_secret]\" value=\"{$options['twitter_secret']}\">";
	}

	function tweetpost_render_twitter_user () {
		$options = get_option('tweetpost');
		$screen_name = $options['twitter_user'];
		echo '<span' . ($screen_name ? ' style="display:none"' : '') . '>';
		echo '<a href="' . TWEETPOST_PAGE . '&oauth=authenticate" title="Authorize"><img src="' . TWEETPOST_PATH . '/authorize.png" alt="authorize"></a></span>';
		echo ($screen_name ? "<strong>@{$screen_name}</strong> <a href=\"" .TWEETPOST_PAGE."&action=clearuser\">change</a>" : '');
	}

	function tweetpost_render_shortener () {
		$options = get_option('tweetpost');
		echo '<select id="url_shortener" name="tweetpost[url_shortener]">';
		echo '<option value="bit.ly">bit.ly</option>';
		echo '<option value="su.pr">su.pr</option>';
		echo '</select>';
	}

	function tweetpost_render_bitly_username () {
		$options = get_option('tweetpost');
		echo "<input type=\"text\" class=\"regular-text\" id=\"bitly_username\" name=\"tweetpost[bitly_username]\" value=\"{$options['bitly_username']}\"><br>";
	}

	function tweetpost_render_bitly_apikey () {
		$options = get_option('tweetpost');
		echo "<input type=\"text\" class=\"regular-text\" id=\"bitly_apikey\" name=\"tweetpost[bitly_apikey]\" value=\"{$options['bitly_apikey']}\"><br>";
	}

	function tweetpost_render_supr_username () {
		$options = get_option('tweetpost');
		echo "<input type=\"text\" class=\"regular-text\" id=\"supr_username\" name=\"tweetpost[supr_username]\" value=\"{$options['supr_username']}\"><br>";
	}

	function tweetpost_render_supr_apikey () {
		$options = get_option('tweetpost');
		echo "<input type=\"text\" class=\"regular-text\" id=\"supr_apikey\" name=\"tweetpost[supr_apikey]\" value=\"{$options['supr_apikey']}\"><br>";
	}

	function tweetpost_render_introtext () {
		$options = get_option('tweetpost');
		echo "<input type=\"text\" class=\"regular-text\" id=\"introtext\" name=\"tweetpost[introtext]\" value=\"{$options['introtext']}\"><br>";
	}

	function tweetpost_render_showbutton () {
		$options = get_option('tweetpost');
		echo "<label><input type=\"radio\" id=\"btn_showyes\" name=\"tweetpost[showbutton]\" value=\"yes\"" . ($options['showbutton'] == 'yes' ? " checked" : "") . "> Yes</label>";
		echo "<label><input type=\"radio\" id=\"btn_showno\" name=\"tweetpost[showbutton]\" value=\"no\"" . ($options['showbutton'] == 'yes' ? "" : " checked") . "> No</label>";
		echo "<br>";
	}

	function tweetpost_render_buttonfloat () {
		$options = get_option('tweetpost');
		$list = array("None"=>"", "Left"=>"left", "Right"=>"right");
		tweetpost_render_select("buttonfloat", "tweetpost[buttonfloat]", $list, $options['buttonfloat']);
		//echo "<select id=\"buttonfloat\" name=\"tweetpost[buttonfloat]\">";
		//echo "<option value=\"\">None</option>";
		//echo "<option value=\"left\""  . ($options['buttonfloat'] == 'left'  ? ' selected' : '') . ">Left</option>";
		//echo "<option value=\"right\"" . ($options['buttonfloat'] == 'right' ? ' selected' : '') . ">Right</option>";
		//echo "</select>";
	}

	function tweetpost_render_buttonclass () {
		$options = get_option('tweetpost');
		echo "<input type=\"text\" class=\"regular-text\" id=\"buttonclass\" name=\"tweetpost[buttonclass]\" value=\"{$options['buttonclass']}\"><br>";
	}

	function tweetpost_render_countstyle () {
		$options = get_option('tweetpost');
		$list = array("Vertical"=>"vertical", "Horizontal"=>"horizontal", "No count"=>"none");
		tweetpost_render_select('countstyle', 'tweetpost[countstyle]', $list, $options['countstyle']);
	}
	
	function tweetpost_render_select ($id, $name, $options, $value) {
		echo "<select id=\"{$id}\" name=\"{$name}\">";
		foreach ($options as $key=>$val) {
			$selected = ($value == $val) ? ' selected' : '';
			echo "<option value=\"{$val}\"{$selected}>{$key}</option>";
		}
		echo "</selelct>";
	}

	function tweetpost_clear_user () {
		$options = get_option('tweetpost');
		$options['access_token' ] = '';
		$options['access_secret'] = '';
		$options['twitter_uid'  ] = '';
		$options['twitter_user' ] = '';
		update_option('tweetpost', $options);
	}

}

function tweetpost_savepost ($post_id) {

    if (is_int($post_id)) $post = get_post($post_id);
    else $post = $post_id;

	//ignore revisions
	if ($post->post_type == 'revision') return;

	//ignore non-published data and password protected data
	if ($post->post_status != 'publish' || $post->post_password != '' ) return;

	$title = $post->post_title;
	//$link = get_permalink($post_id);
    if ($post->post_parent)
        $link = get_permalink($post->post_parent);
    else
        $link = get_permalink($post->ID);

	$shortener = get_option('tweetpost_url_shortener');
	if($shortener == 'bit.ly') {
		$shorturl = tweetpost_bitly($link);
	} else 
	if($shortener == 'su.pr') {
		$shorturl = tweetpost_supr($link);
	} else
		$shorturl = $link;
	if (!$shorturl) $shorturl = $link;

	//get settings
	$twitter_username = get_option('tweetpost_twitter_user');
	$twitter_password = get_option('tweetpost_twitter_pass');
	$twitterer = get_usermeta($post->post_author, 'twitter');
	if ($twitterer) {
		$twitterer = '@'.$twitterer;
	} else {
		$user=get_userdata($post->post_author);
		$twitterer = $user->user_nicename;
	}

	//create Tweet...
	$consumer_key    = get_option('tweetpost_twitter_key'   );
	$consumer_secret = get_option('tweetpost_twitter_secret');
	$access_token    = get_option('tweetpost_access_token'   );
	$access_secret   = get_option('tweetpost_access_secret'  );

	$tweet = str_replace('@username', $twitterer, get_option('tweetpost_opt_introtxt')).' ';
	$limit = 140 - (strlen($tweet) + strlen($shorturl) + 3);
	if (strlen($title) > $limit) {
		$title = substr($title, 0, $limit - 3) . '...';
	}
	$tweet = $tweet . $title . ' - ' . $shorturl;
	$connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_secret);
	$parameters = array('status' => $tweet);
	$status = $connection->post('statuses/update', $parameters);
}

function tweetpost_bitly ($url) {
	$user = get_option('tweetpost_bitly_user');
	$apik = get_option('tweetpost_bitly_apikey');
	
	$endpoint = 'http://api.bit.ly/shorten?version=2.0.1&history=1&format=xml&login='.$user.'&apiKey='.$apik.'&longUrl='.$url;
	$xml = file_get_contents($endpoint);

	preg_match('/<errorcode>([^<]+)<\/errorcode>/i', $xml, $matches);
	if($matches && $matches[1]!='0') return false;
	
	preg_match('/<shorturl>([^<]+)<\/shorturl>/i', $xml, $matches);
	
	if($matches && $matches[1]) return $matches[1];
	else return false;
}

function tweetpost_supr ($url) {
	$user = get_option('tweetpost_supr_user');
	$apik = get_option('tweetpost_supr_apikey');

	$endpoint = 'http://su.pr/api/simpleshorten?url='.$url.'&login='.$user.'&apiKey='.$apik;
	$xml = file_get_contents($endpoint);
	if($xml && $xml != 'ERROR') return $xml;
	else return false;
}

function tweetpost_showuser ($user) {
	?>
	<h3>Extra profile information</h3>
	<table class="form-table">
	<tr><th><label for="twitter">Twitter</label></th><td>
	<input type="text" name="twitter" id="twitter" value="<?php echo esc_attr( get_the_author_meta('twitter', $user->ID ) ) ?>" class="regular-text" /><br />
	<span class="description">Please enter your Twitter username.</span>
	</td></tr></table>
	<?php
}

function tweetpost_edituser ($user_id) {
	if ( !current_user_can('edit_user', $user_id) )
		return false;

	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	update_usermeta($user_id, 'twitter', $_POST['twitter'] );
}

function tweetpost_updtwitter ($userid, $twitterid) {
	update_usermeta ($userid, 'twitter', $twitterid);
}

function tweetpost_admin_update () {
	//
}

function tweetpost_tweetbutton ($content) {
	$options = get_option('tweetpost');
	if($options['showbutton'] != 'yes') return $content;

	global $post;
	
    $button = '<div';
    if ($options['buttonfloat'])
        $button .= ' style="float:' . $options['buttonfloat'] . ';margin:5px;"';
    if ($options['buttonclass'])
        $button .= ' class="' . $options['buttonclass'] . '"';
    $button .= '><a href="http://twitter.com/share" class="twitter-share-button"';	

	$button .= ' data-url="' . get_permalink($post->ID) . '"';
	$button .= ' data-text="' . $post->post_title . '"';
	$button .= ' data-count="' . $options['countstyle'] . '"';

	//$auser = new WP_User($post->post_author);
	//' - ' . get_bloginfo('name') .

	//if ($auser->twitter)
	//	$button .= ' data-via="' . $auser->twitter . '"';
	
	$screen_name = get_option('tweetpost_screen_name');
	if ($screen_name) 
		$button .= ' data-via="' . $screen_name . '"';

	$button .= '">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script></div>';

	return $button . $content;
}

add_filter('the_content', 'tweetpost_tweetbutton');

add_action('show_user_profile',        'tweetpost_showuser');
add_action('edit_user_profile',        'tweetpost_showuser');
add_action('personal_options_update',  'tweetpost_edituser');
add_action('edit_user_profile_update', 'tweetpost_edituser');
add_action('admin_notices',      'tweetpost_adminnotice'   );
add_action('new_to_publish',     'tweetpost_savepost', 10, 1);
add_action('draft_to_publish',   'tweetpost_savepost', 10, 1);
add_action('pending_to_publish', 'tweetpost_savepost', 10, 1);
add_action('future_to_publish',  'tweetpost_savepost', 10, 1);

?>
