<?php
/*
	Question2Answer (c) Gideon Greenspan
	http://www.question2answer.org/

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
	
*/

/*
Twitter Recent posts Widget
*/

	class qa_twitter {
	
		var $directory;
		var $urltoroot;
		var $provider;

		function load_module($directory, $urltoroot, $type, $provider) {
			$this->directory = $directory;
			$this->urltoroot = $urltoroot;
			$this->provider = $provider;
		}	
		function option_default($option)
		{
			if ($option=='qa-twitter-id')
				return 100;
			elseif ($option=='qa-twitter-t-count')
				return 24;
			elseif ($option=='qa-twitter-includereplies')
				return true;
		}

		
		function admin_form()
		{
			$saved=false;
			
			if (qa_clicked('qa_twitter_save_button')) {
				qa_opt('qa_twitter_id', qa_post_text('qa_twitter_id_field'));
				qa_opt('qa_twitter_t_count', (int)qa_post_text('qa_twitter_t_count_field'));
				qa_opt('qa_twitter_title', qa_post_text('qa_twitter_title_field'));
				qa_opt('qa_twitter_ck', qa_post_text('qa_twitter_ck_field'));
				qa_opt('qa_twitter_cs', qa_post_text('qa_twitter_cs_field'));
				qa_opt('qa_twitter_at', qa_post_text('qa_twitter_at_field'));
				qa_opt('qa_twitter_ts', qa_post_text('qa_twitter_ts_field'));
				$saved=true;
			}
			
			return array(
				'ok' => $saved ? 'Twitter Widget settings saved' : null,
				
				'fields' => array(
					array(
						'label' => 'Twitter ID:',
						'type' => 'string',
						'value' => qa_opt('qa_twitter_id'),
						'suffix' => 'ie. qa-themes, james',
						'tags' => 'NAME="qa_twitter_id_field"',
					),
					array(
						'label' => 'Widget Title:',
						'type' => 'string',
						'value' => qa_opt('qa_twitter_title'),
						'suffix' => 'you can leave it empty',
						'tags' => 'NAME="qa_twitter_title_field"',
					),	
					array(
						'label' => 'number of latest tweets:',
						'suffix' => 'tweets',
						'type' => 'number',
						'value' => (int)qa_opt('qa_twitter_t_count'),
						'tags' => 'NAME="qa_twitter_t_count_field"',
					),
					array(
						'label' => 'Consumer key:',
						'type' => 'string',
						'value' => qa_opt('qa_twitter_ck'),
						'tags' => 'NAME="qa_twitter_ck_field"',
					),	
					array(
						'label' => 'Consumer secret:',
						'type' => 'string',
						'value' => qa_opt('qa_twitter_cs'),
						'tags' => 'NAME="qa_twitter_cs_field"',
					),	
					array(
						'label' => 'Access token:',
						'type' => 'string',
						'value' => qa_opt('qa_twitter_at'),
						'tags' => 'NAME="qa_twitter_at_field"',
					),
					array(
						'label' => 'Access token secret:',
						'type' => 'string',
						'value' => qa_opt('qa_twitter_ts'),
						'tags' => 'NAME="qa_twitter_ts_field"',
						'error' => $this->twitter_api_error_html(),
					),	
				),
				'buttons' => array(
					array(
						'label' => 'Save Changes',
						'tags' => 'NAME="qa_twitter_save_button"',
					),
				),
			);
		}

		function twitter_api_error_html()
		{
			return 'To use twitter API you must register your application. to do this visit <a href="https://dev.twitter.com/">twitter development Page</a> and log in with your Twitter credential. then visit <a href="https://dev.twitter.com/apps/">My applications</a> and creat your application and fill these fields from your application API detail. <br /> if these fields are set correctly and your application has permission to work with this domain then you can add Twitter Widget in "Admin > Layouts".'; 
		}
		
		function allow_template($template)
		{
			$allow=false;
			
			switch ($template)
			{
				case 'activity':
				case 'qa':
				case 'questions':
				case 'hot':
				case 'ask':
				case 'categories':
				case 'question':
				case 'tag':
				case 'tags':
				case 'unanswered':
				case 'user':
				case 'users':
				case 'search':
				case 'admin':
				case 'custom':
					$allow=true;
					break;
			}
			
			return $allow;
		}

		
		function allow_region($region)
		{
			return ($region=='side');
		}
		function get_tweets()
		{
			$user = qa_opt('qa_twitter_id');
			$count=(int)qa_opt('qa_twitter_t_count');
			$title=qa_opt('qa_twitter_title');
			
			require_once $this->directory . 'TwitterAPIExchange.php';
			// Setting our Authentication Variables that we got after creating an application
			$settings = array(
				'oauth_access_token' => qa_opt('qa_twitter_at'),
				'oauth_access_token_secret' => qa_opt('qa_twitter_ts'),
				'consumer_key' => qa_opt('qa_twitter_ck'),
				'consumer_secret' => qa_opt('qa_twitter_cs')
			);

			$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
			$requestMethod = "GET";

			$getfield = "?screen_name=$user&count=$count";
			$twitter = new TwitterAPIExchange($settings);
			$tweets = json_decode($twitter->setGetfield($getfield)
				->buildOauth($url, $requestMethod)
					->performRequest(),$assoc = TRUE);
			return $tweets;

		}
		

		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			$user = qa_opt('qa_twitter_id');
			$count=(int)qa_opt('qa_twitter_t_count');
			$title=qa_opt('qa_twitter_title');

			$themeobject->output('<DIV class="qa-tweeter-widget">');
				$themeobject->output('<H2 class="qa-tweeter-header">'.$title.'</H2>');
				
			$file = $this->directory . 'cache/' . $user ."-tweets.txt";
			$modified = @filemtime( $file );
			$now = time();
			$interval = 3600; // 1 hour
			// Cache File
			if ( empty($modified) || ( ( $now - $modified ) > $interval ) ) {
				// read live tweets
				$tweets=$this->get_tweets();
				if ( $tweets ) {
				// cache tweets
					$cache = fopen( $file, 'w' );
					//fwrite( $cache, $tweets );
					fwrite($cache, json_encode($tweets));
					fclose( $cache );
				}
			}else{
				//read tweets from cache
				$tweets = json_decode(file_get_contents( $file ),$assoc = TRUE);
			}

			echo '<ul class="qa-tweeter-list">';
			foreach($tweets as $items)
			{
				// links
				$items['text'] = preg_replace(
					'@(https?://([-\w\.]+)+(/([\w/_\.]*(\?\S+)?(#\S+)?)?)?)@',
					 '<a href="$1">$1</a>',
					$items['text']);
				//users
				$items['text'] = preg_replace(
					'/@(\w+)/',
					'<a href="http://twitter.com/$1">@$1</a>',
					$items['text']);	
				// hashtags
				$items['text'] = preg_replace(
					'/\s+#(\w+)/',
					' <a href="http://search.twitter.com/search?q=%23$1">#$1</a>',
					$items['text']);
					
				//echo "Time and Date of Tweet: ".$items['created_at']."<br />";
				echo '<li class="qa-tweeter-item">'. $items['text'].'</li>';
				//echo "Tweeted by: ". $items['user']['name']."<br />";
				//echo "Screen name: ". $items['user']['screen_name']."<br />";
				//echo "Followers: ". $items['user']['followers_count']."<br />";
				//echo "Friends: ". $items['user']['friends_count']."<br />";
				//echo "Listed: ". $items['user']['listed_count']."<br /><hr />";
			}
			echo '</ul>';

			 $themeobject->output('</DIV>');
		}
	
	}

/*
	Omit PHP closing tag to help avoid accidental output
*/