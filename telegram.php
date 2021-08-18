<?php

require_once "api_keys.php";

class Telegram
{
    protected string $token = TOKEN;

    function request($method, $datafile): bool|string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot" . $this->token . "/$method");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datafile);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    function sendMessage($chat_id, $text, $parse_mode = NULL,
                         $disable_web_page_preview = TRUE, $entities = NULL, $disable_notification = NULL,
                         $reply_to_message_id = NULL, $allow_sending_without_reply = TRUE,
                         $reply_markup = NULL): bool|string
    {
        $datafile = array(
            "chat_id" => $chat_id,
            "text" => $text,
            "parse_mode" => $parse_mode,
            "disable_web_page_preview" => $disable_web_page_preview,
            "entities" => $entities,
            "disable_notification" => $disable_notification,
            "reply_to_message_id" => $reply_to_message_id,
            "allow_sending_without_reply" => $allow_sending_without_reply,
            "reply_markup" => $reply_markup);
        return $this->request(__FUNCTION__, $datafile);
    }

    function sendDocument($chat_id, $document, $caption = NULL, $parse_mode = NULL,
                          $disable_notification = NULL, $reply_to_message_id = NULL, $reply_markup = NULL): bool|string
    {
        $datafile = array(
            "chat_id" => $chat_id,
            "document" => $document,
            "caption" => $caption,
            "parse_mode" => $parse_mode,
            "disable_notification" => $disable_notification,
            "reply_to_message_id" => $reply_to_message_id,
            "reply_markup" => $reply_markup
        );
        return $this->request(__FUNCTION__, $datafile);
    }


    function sendVideo($chat_id, $video, $caption = NULL, $parse_mode = NULL,
                       $disable_notification = NULL, $reply_to_message_id = NULL, $reply_markup = NULL): bool|string
    {
        $datafile = array(
            "chat_id" => $chat_id,
            "video" => $video,
            "caption" => $caption,
            "parse_mode" => $parse_mode,
            "disable_notification" => $disable_notification,
            "reply_to_message_id" => $reply_to_message_id,
            "reply_markup" => $reply_markup
        );
        return $this->request(__FUNCTION__, $datafile);
    }


    function sendMediagroup($chat_id, $media, $disable_notification = NULL, $reply_to_message_id = NULL): bool|string
    {
        $datafile = array(
            "chat_id" => $chat_id,
            "media" => $media,
            "disable_notification" => $disable_notification,
            "reply_to_message_id" => $reply_to_message_id,
        );
        return $this->request(__FUNCTION__, $datafile);
    }
}
