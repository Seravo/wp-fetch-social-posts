<?php

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

print_r($response);
