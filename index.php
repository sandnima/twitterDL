<?php

date_default_timezone_set('Asia/Tehran');

require_once('twitter.php');
require_once('telegram.php');

$telegram = new Telegram();
$twitter = new Twitter();

$update_raw = file_get_contents('php://input');
$update = json_decode($update_raw);

// ============= Saving logs to Allmessage.json ==============={
// if(!file_exists('data'))
// {mkdir('data');}

// $logs=file_get_contents('php://input');
// $storge=fopen('data/AllMessages.json','a');
// fwrite($storge,$logs."\n");
// fclose($storge);
// ============= Saving logs to Allmessage.json ===============}


$message = $update->message ?? null;

if ($message) {
    $message_id = $update->message->message_id;
    $chat_id = $update->message->chat->id;
    // $from_id = $update->message->from->id;
    $text = $update->message->text ?? null;

    if ($text) {
        if ($text == '/start') {
            $telegram->sendMessage($chat_id, "Please send me a tweet link.", reply_to_message_id: $message_id);
        } elseif (preg_match_all('/twitter.com\/(?<tweet_owner>\w+)\/status\/(?<status_id>\d+)/', $text, $all_matches)) {
            $statuses = '';
            foreach ($all_matches['status_id'] as $status_id) {
                $statuses = $statuses . $status_id . ',';
            }
            $statuses = trim($statuses, ",");
            $tweets = $twitter->getTweets($statuses);

            // if ($tweets) {
            //     $temp = fopen('data/tweet.json','w');
            //     fwrite($temp, json_encode($tweets));
            //     $telegram->sendDocument($chat_id, new CURLFile('data/tweet.json'));
            //     fclose($temp);
            // }

            if (preg_match('/(\s|\A)\/m\W/', $text)) {
                $merge_all = True;
                $merged_text = '';
                $merged_album = array();
            }
            else {
                $merge_all = False;
            }

            foreach ($tweets as $tweet) {
                $full_text = $twitter->shortlink_clean($tweet->full_text);

                $response_text = $full_text
                    . "\n\n"
                    . '<a href="' . $twitter->tweetURL($tweet->user->screen_name, $tweet->id) . '">' . $tweet->user->name . '</a>';

                // If tweet is only text
                if (!property_exists($tweet, 'extended_entities')) {
                    if (!$merge_all) {
                        $telegram->sendMessage($chat_id, $response_text, 'HTML');
                    }
                    else {
                        $merged_text = $merged_text.$response_text."\n\n";
                    }
                } // If tweet is not just text
                else {
                    // If tweet includes media
                    if (property_exists($tweet->extended_entities, 'media')) {
                        // If tweet is video
                        if ($tweet->extended_entities->media[0]->type == 'video') {
                            $media = $tweet->extended_entities->media[0];
                            $variants = sizeof($media->video_info->variants);
                            for ($index_counter = 0; $index_counter < $variants; $index_counter++) {
                                $media_url = $media->video_info->variants[$index_counter]->url;

                                if (!$merge_all) {
                                    $res = $telegram->sendVideo($chat_id, $media_url, $response_text, 'HTML');
                                }
                                else {
                                    // Will be modified soon
                                    $res = $telegram->sendVideo($chat_id, $media_url, $response_text, 'HTML');
                                }

                                if (json_decode($res)->ok) {
                                    break;
                                }
                            }

                        } // If tweet is photos
                        else {
                            $index_counter = 0;
                            foreach ($tweet->extended_entities->media as $media) {
                                $media_type = $media->type;
                                if ($media->type == 'photo') {
                                    $media_url = $media->media_url;
                                    $medias[] = [
                                        'type' => $media_type,
                                        'media' => $media_url,
                                        'caption' => $index_counter == 0 ? $response_text : '',
                                        'parse_mode' => 'HTML'
                                    ];
                                }
                                $index_counter = $index_counter + 1;
                            }
                            if (!$merge_all) {
                                $res = $telegram->sendMediagroup($chat_id, json_encode($medias));
                            }
                            else {
                                foreach ($medias as $media) {
                                    $merged_album[] = $media;
                                }
                                unset($medias);
                                $merged_text = $merged_text.$response_text."\n\n";
                            }
                        }
                    }
                }
            }

            if ($merge_all) {
                if (sizeof($merged_album) < 1) {
                    $telegram->sendMessage($chat_id, $merged_text, 'HTML');
                }
                else {
                    foreach ($merged_album as &$media) {
                        $media['caption'] = '';
                    }
                    $merged_album[0]['caption'] = $merged_text;
                    $res = $telegram->sendMediagroup($chat_id, json_encode($merged_album));
                }
            }
        }
    }
}
