<?php

/**
 * Fetches a list of tweets
 */
function fsp_fetch_twitter() {

  // Require the Twitter PHP API
  require_once 'twitter-api-php/TwitterAPIExchange.php';

  // set access tokens
  $settings = array(
      'oauth_access_token' => SSF_TWITTER_OAUTH_TOKEN,
      'oauth_access_token_secret' => SSF_TWITTER_OAUTH_SECRET,
      'consumer_key' => SSF_TWITTER_CONSUMER_KEY,
      'consumer_secret' => SSF_TWITTER_CONSUMER_SECRET,
  );

  $twitter = new TwitterAPIExchange($settings);
  $params = "?screen_name=" . SSF_TWITTER_USERNAME;;
  $response = $twitter->setGetfield($params)
    ->buildOauth('https://api.twitter.com/1.1/statuses/user_timeline.json', 'GET')
    ->performRequest();
  $response = json_decode($response);

  $items = array();

  foreach($response as $tweet) {
    $item = array();

    $item['type'] = 'twitter';
    $item['source'] = 'https://twitter.com/' . $tweet->user->screen_name . '/status/' . $tweet->id;
    $item['uid'] = $tweet->id;
    $item['created'] = strtotime($tweet->created_at);
    $item['title'] = wp_trim_words($tweet->text, 6);
    $item['content'] = $tweet->text;

    // tweet might have an image
    if(isset($tweet->extended_entities->media) && $tweet->extended_entities->media[0]->type == 'photo') {
      $item['image'] = $tweet->extended_entities->media[0]->media_url_https;
    }

    $items[] = $item;
  }

  return $items;
}

