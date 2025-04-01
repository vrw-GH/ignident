<?php
namespace aviaFramework\widgets;

/**
 * AVIA TWEETBOX
 *
 * Widget that creates a list of latest tweets
 *
 * Twitter widget only for compatibility reasons with older themes present. no longer used since API will be shut down by Twitter
 *
 * @since ???
 * @since 4.9			Code was moved from class-framework-widgets.php
 * @deprecated since 4.9
 * @since 4.9
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }


if( ! class_exists( __NAMESPACE__ . 'avia_tweetbox', false ) )
{
	/**
	 * @deprecated 4.9
	 */
	class avia_tweetbox extends WP_Widget
	{
		/**
		 * @deprecated 4.9
		 */
		function __construct()
		{
			_deprecated_constructor( 'avia_tweetbox', '4.9' );

			//Constructor
			$widget_ops = array('classname' => 'tweetbox', 'description' => 'A widget to display your latest Twitter messages' );
			parent::__construct( 'tweetbox', THEMENAME.' Twitter Widget', $widget_ops );
		}

		/**
		 * Output the widget in frontend
		 *
		 * @param array $args
		 * @param array $instance
		 */
		function widget($args, $instance) {
			// prints the widget

			extract($args, EXTR_SKIP);
			echo $before_widget;

			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
			$count = empty($instance['count']) ? '' : $instance['count'];
			$username = empty($instance['username']) ? '' : $instance['username'];
			$exclude_replies = empty($instance['exclude_replies']) ? '' : $instance['exclude_replies'];
			$time = empty($instance['time']) ? 'no' : $instance['time'];
			$display_image = empty($instance['display_image']) ? 'no' : $instance['display_image'];

			if ( !empty( $title ) ) { echo $before_title . "<a href='http://twitter.com/$username/' title='".strip_tags($title)."'>".$title ."</a>". $after_title; };

			$messages = tweetbox_get_tweet($count, $username, $widget_id, $time, $exclude_replies, $display_image);
			echo $messages;

			echo $after_widget;


		}

		function update($new_instance, $old_instance)
		{
			//save the widget
			$instance = $old_instance;
			foreach($new_instance as $key=>$value)
			{
				$instance[$key]	= strip_tags($new_instance[$key]);
			}

			delete_transient(THEMENAME.'_tweetcache_id_'.$instance['username'].'_'.$this->id_base."-".$this->number);
			return $instance;
		}

		function form($instance)
		{
			//widgetform in backend

			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Latest Tweets', 'count' => '3', 'username' => avia_get_option('twitter') ) );
			$title = 			isset($instance['title']) ? strip_tags($instance['title']): "";
			$count = 			isset($instance['count']) ? strip_tags($instance['count']): "";
			$username = 		isset($instance['username']) ? strip_tags($instance['username']): "";
			$exclude_replies = 	isset($instance['exclude_replies']) ? strip_tags($instance['exclude_replies']): "";
			$time = 			isset($instance['time']) ? strip_tags($instance['time']): "";
			$display_image = 	isset($instance['display_image']) ? strip_tags($instance['display_image']): "";
	?>
			<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'avia_framework'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>

			<p><label for="<?php echo $this->get_field_id('username'); ?>">Enter your Twitter username:
			<input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo esc_attr($username); ?>" /></label></p>

			<p>
				<label for="<?php echo $this->get_field_id('count'); ?>">How many entries do you want to display: </label>
				<select class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>">
					<?php
					$list = "";
					for ($i = 1; $i <= 20; $i++ )
					{
						$selected = "";
						if($count == $i) $selected = 'selected="selected"';

						$list .= "<option $selected value='$i'>$i</option>";
					}
					$list .= "</select>";
					echo $list;
					?>


			</p>

			<p>
				<label for="<?php echo $this->get_field_id('exclude_replies'); ?>">Exclude @replies: </label>
				<select class="widefat" id="<?php echo $this->get_field_id('exclude_replies'); ?>" name="<?php echo $this->get_field_name('exclude_replies'); ?>">
					<?php
					$list = "";
					$answers = array('yes','no');
					foreach ($answers as $answer)
					{
						$selected = "";
						if($answer == $exclude_replies) $selected = 'selected="selected"';

						$list .= "<option $selected value='$answer'>$answer</option>";
					}
					$list .= "</select>";
					echo $list;
					?>


			</p>

			<p>
				<label for="<?php echo $this->get_field_id('time'); ?>">Display time of tweet</label>
				<select class="widefat" id="<?php echo $this->get_field_id('time'); ?>" name="<?php echo $this->get_field_name('time'); ?>">
					<?php
					$list = "";
					$answers = array('yes','no');
					foreach ($answers as $answer)
					{
						$selected = "";
						if($answer == $time) $selected = 'selected="selected"';

						$list .= "<option $selected value='$answer'>$answer</option>";
					}
					$list .= "</select>";
					echo $list;
					?>


			</p>

			<p>
				<label for="<?php echo $this->get_field_id('display_image'); ?>">Display Twitter User Avatar</label>
				<select class="widefat" id="<?php echo $this->get_field_id('display_image'); ?>" name="<?php echo $this->get_field_name('display_image'); ?>">
					<?php
					$list = "";
					$answers = array('yes','no');
					foreach ($answers as $answer)
					{
						$selected = "";
						if($answer == $display_image) $selected = 'selected="selected"';

						$list .= "<option $selected value='$answer'>$answer</option>";
					}
					$list .= "</select>";
					echo $list;
					?>
			</p>



		<?php
		}
	}
}

if(!function_exists('tweetbox_get_tweet'))
{
	function tweetbox_get_tweet($count, $username, $widget_id, $time='yes', $exclude_replies='yes', $avatar = 'yes')
	{
			$filtered_message = "";
			$output = "";
			$iterations = 0;

			$cache = get_transient(THEMENAME.'_tweetcache_id_'.$username.'_'.$widget_id);

			if($cache)
			{
				$tweets = get_option(THEMENAME.'_tweetcache_'.$username.'_'.$widget_id);
			}
			else
			{
				//$response = wp_remote_get( 'http://api.twitter.com/1/statuses/user_timeline.xml?screen_name='.$username );
				$response = wp_remote_get( 'http://api.twitter.com/1/statuses/user_timeline.xml?include_rts=true&screen_name='.$username );
				if (!is_wp_error($response))
				{
					$xml = @simplexml_load_string($response['body']);
					//follower: (int) $xml->status->user->followers_count

					if( empty( $xml->error ) )
				    {
				    	if ( isset($xml->status[0]))
				    	{

				    	    $tweets = array();
				    	    foreach ($xml->status as $tweet)
				    	    {
				    	    	if($iterations == $count) break;

				    	    	$text = (string) $tweet->text;
				    	    	if($exclude_replies == 'no' || ($exclude_replies == 'yes' && $text[0] != "@"))
				    	    	{
				    	    		$iterations++;
				    	    		$tweets[] = array(
				    	    			'text' => tweetbox_filter( $text ),
				    	    			'created' =>  strtotime( $tweet->created_at ),
				    	    			'user' => array(
				    	    				'name' => (string)$tweet->user->name,
				    	    				'screen_name' => (string)$tweet->user->screen_name,
				    	    				'image' => (string)$tweet->user->profile_image_url,
				    	    				'utc_offset' => (int) $tweet->user->utc_offset[0],
				    	    				'follower' => (int) $tweet->user->followers_count

				    	    			));
				    			}
				    		}

				    		set_transient(THEMENAME.'_tweetcache_id_'.$username.'_'.$widget_id, 'true', 60*30);
				    		update_option(THEMENAME.'_tweetcache_'.$username.'_'.$widget_id, $tweets);
				    	}
				    }
				}
			}



			if(!isset($tweets[0]))
			{
				$tweets = get_option(THEMENAME.'_tweetcache_'.$username.'_'.$widget_id);
			}

		    if(isset($tweets[0]))
		    {
		    	$time_format = apply_filters( 'avia_widget_time', get_option('date_format')." - ".get_option('time_format'), 'tweetbox' );

		    	foreach ($tweets as $message)
		    	{
		    		$output .= '<li class="tweet">';
		    		if($avatar == "yes") $output .= '<div class="tweet-thumb"><a href="http://twitter.com/'.$username.'" title=""><img src="'.$message['user']['image'].'" alt="" /></a></div>';
		    		$output .= '<div class="tweet-text avatar_'.$avatar.'">'.$message['text'];
		    		if($time == "yes") $output .= '<div class="tweet-time">'.date_i18n( $time_format, $message['created'] + $message['user']['utc_offset']).'</div>';
		    		$output .= '</div></li>';
				}
		    }


			if($output != "")
			{
				$filtered_message = "<ul class='tweets'>$output</ul>";
			}
			else
			{
				$filtered_message = "<ul class='tweets'><li>No public Tweets found</li></ul>";
			}

			return $filtered_message;
	}
}

if(!function_exists('tweetbox_filter'))
{
	function tweetbox_filter($text) {
	    // Props to Allen Shaw & webmancers.com & Michael Voigt
	    $text = preg_replace('/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"$1\" class=\"twitter-link\">$1</a>", $text);
	    $text = preg_replace('/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"http://$1\" class=\"twitter-link\">$1</a>", $text);
	    $text = preg_replace("/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i","<a href=\"mailto://$1\" class=\"twitter-link\">$1</a>", $text);
	    $text = preg_replace("/#([\p{L}\p{Mn}]+)/u", "<a class=\"twitter-link\" href=\"http://search.twitter.com/search?q=\\1\">#\\1</a>", $text);
	    $text = preg_replace("/@([\p{L}\p{Mn}]+)/u", "<a class=\"twitter-link\" href=\"http://twitter.com/\\1\">@\\1</a>", $text);

	    return $text;
	}
}

