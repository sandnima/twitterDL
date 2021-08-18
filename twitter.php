<?php

require "vendor/autoload.php";
require_once "api_keys.php";

use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter extends TwitterOAuth
{
    public function __construct()
    {
        parent::__construct(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
    }


    function getTweets($ids): object|array
    {
        return $this->get('statuses/lookup', [
            'id' => $ids,
            'include_entities' => true,
            'tweet_mode' => 'extended'
        ]);
    }


    function tweetURL($tweet_author, $tweet_id): string
    {
        return "https://twitter.com/$tweet_author/status/$tweet_id";
    }


    function shortlink_clean($text): string|null
    {
        if (preg_match_all('/(?<short_url>https:\/\/t.co\/\S+)/', $text, $all_matches)) {
            foreach ($all_matches['short_url'] as $short_url) {
                $text = str_replace($short_url, "", $text);
            }
        }
        return $text;
    }
}
