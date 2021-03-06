<?php
add_action('widgets_init', 'cs_tweets_load_widgets');

function cs_tweets_load_widgets()
{
	register_widget('CS_Tweets_Widget');
}

class CS_Tweets_Widget extends WP_Widget {

	function CS_Tweets_Widget()
	{
		$widget_ops = array('classname' => 'tweets', 'description' => '');

		$control_ops = array('id_base' => 'cs-tweets-widget');

		parent::__construct('cs-tweets-widget', __('CS Twitter',THEMENAME), $widget_ops, $control_ops);
	}

	function widget($args, $instance)
	{
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		$consumer_key = $instance['consumer_key'];
		$consumer_secret = $instance['consumer_secret'];
		$access_token = $instance['access_token'];
		$access_token_secret = $instance['access_token_secret'];
		$twitter_id = $instance['twitter_id'];
		$show_date = $instance['show_date'];
		$count = (int) $instance['count'];

		echo $before_widget;

		if($title) {
			echo $before_title.$title.$after_title;
		}

		if($twitter_id && $consumer_key && $consumer_secret && $access_token && $access_token_secret && $count) {
		$transName = 'list_tweets_'.$args['widget_id'];
		$cacheTime = 10;
		if(false === ($twitterData = get_transient($transName))) {

			$token = get_option('cfTwitterToken_'.$args['widget_id']);

			// get a new token anyways
			delete_option('cfTwitterToken_'.$args['widget_id']);

			// getting new auth bearer only if we don't have one
			if(!$token) {
				// preparing credentials
				$credentials = $consumer_key . ':' . $consumer_secret;
				$toSend = base64_encode($credentials);

				// http post arguments
				$args = array(
					'method' => 'POST',
					'httpversion' => '1.1',
					'blocking' => true,
					'headers' => array(
						'Authorization' => 'Basic ' . $toSend,
						'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
					),
					'body' => array( 'grant_type' => 'client_credentials' )
				);

				add_filter('https_ssl_verify', '__return_false');
				$response = wp_remote_post('https://api.twitter.com/oauth2/token', $args);

				$keys = json_decode(wp_remote_retrieve_body($response));

				if($keys) {
					// saving token to wp_options table
					$token = $keys->access_token;
				}
			}
			// we have bearer token wether we obtained it from API or from options
			$args = array(
				'httpversion' => '1.1',
				'blocking' => true,
				'headers' => array(
					'Authorization' => "Bearer $token"
				)
			);

			add_filter('https_ssl_verify', '__return_false');
			$api_url = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name='.$twitter_id.'&count='.$count;
			$response = wp_remote_get($api_url, $args);

			set_transient($transName, wp_remote_retrieve_body($response), 60 * $cacheTime);
		}
		@$twitter = json_decode(get_transient($transName), true);
		if($twitter && is_array($twitter)) {
			//var_dump($twitter);
		?>
		<div class="twitter-box">
			<div class="twitter-holder">
				<div class="b">
					<div class="tweets-container" id="tweets_<?php echo $args['widget_id']; ?>">
						<ul id="jtwt">
							<?php foreach($twitter as $tweet): ?>
							<li class="jtwt_tweet">
								<p class="jtwt_tweet_text">
								<?php
								$latestTweet = $tweet['text'];;
								$latestTweet = preg_replace('/http:\/\/([a-z0-9_\.\-\+\&\!\#\~\/\,]+)/i', '&nbsp;<a href="http://$1" target="_blank">http://$1</a>&nbsp;', $latestTweet);
								$latestTweet = preg_replace('/@([a-z0-9_]+)/i', '&nbsp;<a href="http://twitter.com/$1" target="_blank">@$1</a>&nbsp;', $latestTweet);
								echo $latestTweet;
								?>
								</p>
								<?php
								$twitterTime = strtotime($tweet['created_at']);
								$timeAgo = $this->ago($twitterTime);
								?>
								<?php if($show_date == 'yes'): ?>
								<a href="http://twitter.com/<?php echo $tweet['user']['screen_name']; ?>/statuses/<?php echo $tweet['id_str']; ?>" class="jtwt_date"><?php echo $timeAgo; ?></a>
								<?php endif; ?>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
			<span class="arrow"></span>
		</div>
		<?php }}

		echo $after_widget;
	}

	function ago($time)
	{
	   $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
	   $lengths = array("60","60","24","7","4.35","12","10");

	   $now = time();

	       $difference     = $now - $time;
	       $tense         = "ago";

	   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
	       $difference /= $lengths[$j];
	   }

	   $difference = round($difference);

	   if($difference != 1) {
	       $periods[$j].= "s";
	   }

	   return "$difference $periods[$j] ago ";
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['consumer_key'] = $new_instance['consumer_key'];
		$instance['consumer_secret'] = $new_instance['consumer_secret'];
		$instance['access_token'] = $new_instance['access_token'];
		$instance['access_token_secret'] = $new_instance['access_token_secret'];
		$instance['twitter_id'] = $new_instance['twitter_id'];
		$instance['show_date'] = $new_instance['show_date'];
		$instance['count'] = $new_instance['count'];

		return $instance;
	}

	function form($instance)
	{
		$defaults = array('title' => 'Recent Tweets', 'twitter_id' => '', 'count' => 3, 'consumer_key' => '', 'consumer_secret' => '', 'access_token' => '', 'access_token_secret' => '');
		$instance = wp_parse_args((array) $instance, $defaults); ?>

		<p><a href="<?php echo esc_url("http://dev.twitter.com/apps"); ?>">Find or Create your Twitter App</a></p>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('consumer_key'); ?>">Consumer Key:</label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('consumer_key'); ?>" name="<?php echo $this->get_field_name('consumer_key'); ?>" value="<?php echo $instance['consumer_key']; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('consumer_secret'); ?>">Consumer Secret:</label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('consumer_secret'); ?>" name="<?php echo $this->get_field_name('consumer_secret'); ?>" value="<?php echo $instance['consumer_secret']; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('access_token'); ?>">Access Token:</label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('access_token'); ?>" name="<?php echo $this->get_field_name('access_token'); ?>" value="<?php echo $instance['access_token']; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('access_token_secret'); ?>">Access Token Secret:</label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('access_token_secret'); ?>" name="<?php echo $this->get_field_name('access_token_secret'); ?>" value="<?php echo $instance['access_token_secret']; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('twitter_id'); ?>">Twitter ID:</label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('twitter_id'); ?>" name="<?php echo $this->get_field_name('twitter_id'); ?>" value="<?php echo $instance['twitter_id']; ?>" />
		</p>

			<label for="<?php echo $this->get_field_id('show_date'); ?>">Show Date:</label>
			<select id="<?php echo $this->get_field_id('show_date'); ?>" class="widefat" name="<?php echo $this->get_field_name('show_date'); ?>">
				<option value="no" <?php if($instance['show_date'] == 'no'){ echo "selected='selected'"; } ?>><?php echo __('No',THEMENAME); ?></option>
				<option value="yes" <?php if($instance['show_date'] == 'yes'){ echo "selected='selected'"; } ?>><?php echo __('Yes',THEMENAME); ?></option>
			</select>
		</p>

			<label for="<?php echo $this->get_field_id('count'); ?>">Number of Tweets:</label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" value="<?php echo $instance['count']; ?>" />
		</p>

	<?php
	}
}
?>