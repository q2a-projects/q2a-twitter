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
				qa_opt('qa_twitter_includereplies', (int)qa_post_text('qa_twitter_includereplies_field'));
				qa_opt('qa_twitter_title', qa_post_text('qa_twitter_title_field'));
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
						'suffix' => 'Recent Posts',
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
						'label' => 'Replys will be included',
						'type' => 'checkbox',
						'value' => qa_opt('qa_twitter_includereplies'),
						'tags' => 'NAME="qa_twitter_includereplies_field"',
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
		

		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			$twitterID = qa_opt('qa_twitter_id');
			$includeReplies = qa_opt('qa_twitter_includereplies');
			$numtweets=(int)qa_opt('qa_twitter_t_count');
			$title=qa_opt('qa_twitter_title');

			$themeobject->output('<DIV class="qa-tweeter-widget">');
				$themeobject->output('<H2 class="qa-tweeter-header">'.$title.'</H2>');
				get_tweets($twitterID,$numtweets,"D jS M y H:i",$includeReplies);
			$themeobject->output('</DIV>');
		}
	
	}
	function twitter_status($twitter_id) { 
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL,
			"http://twitter.com/statuses/user_timeline/$twitter_id.xml");
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($c, CURLOPT_TIMEOUT, 5);
		$response = curl_exec($c);
		$responseInfo = curl_getinfo($c);
		curl_close($c);
		if (intval($responseInfo['http_code']) == 200) {
			if (class_exists('SimpleXMLElement')) {
				$xml = @new SimpleXMLElement($response);
				return $xml;
			} else {
				return $response;
			}
		} else {
			return false;
		}
	}
	 
	/** Method to add hyperlink html tags to any urls, twitter ids or
		hashtags in the tweet */
	function processLinks($text) {
		$text = utf8_decode( $text );
		$text = preg_replace('@(https?://([-\w\.]+)+(d+)?(/([\w/_\.]*(\?\S+)?)?)?)@',
								'<a href="$1">$1</a>',  $text );
		$text = preg_replace("#(^|[\n ])@([^ \"\t\n\r<]*)#ise",
								"'\\1<a href=\"http://www.twitter.com/\\2\" >@\\2</a>'", $text); 
		$text = preg_replace("#(^|[\n ])\#([^ \"\t\n\r<]*)#ise",
								"'\\1<a href=\"http://hashtags.org/search?query=\\2\" >#\\2</a>'", $text);
		return $text;
	}
	 
	/** Main method to retrieve the tweets and return html for display */
	function get_tweets($twitter_id,
						$nooftweets=6,
						$dateFormat="D jS M y H:i",
						$includeReplies=false,
						$dateTimeZone="Europe/London",
						$beforeTweetsHtml="<ul class=\"qa-tweeter-list\"  style=\"list-style-position: inside; padding: 0px;\">",
						$tweetStartHtml="<li class=\"qa-tweeter-items\"><span class=\"qa-tweeter-item-content\">",
						$tweetMiddleHtml="</span><br/><span class=\"qa-tweeter-details\">",
						$tweetEndHtml="</span></li>",
						$afterTweetsHtml="</ul>") {
	 
		date_default_timezone_set($dateTimeZone);
		if ( $twitter_xml = twitter_status($twitter_id) ) {
			$result = $beforeTweetsHtml;
			foreach ($twitter_xml->status as $key => $status) {
				if ($includeReplies == true |
						substr_count($status->text,"@") == 0 |
						strpos($status->text,"@") != 0) {
					$message = processLinks($status->text);
					$result.=$tweetStartHtml.$message.$tweetMiddleHtml.
								date($dateFormat,strtotime($status->created_at)).$tweetEndHtml;
					@++$i;
					if ($i == $nooftweets) break;
					}
				}
				$result.=$afterTweetsHtml;
		}
		else {
			@$result.= $beforeTweetsHtml.
						"<li id='tweet'>Twitter seems to be unavailable at the moment</li>".
						$afterTweetsHtml;
		}  
		echo $result;
	}

/*
	Omit PHP closing tag to help avoid accidental output
*/