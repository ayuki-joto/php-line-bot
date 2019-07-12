<?php

$accessToken = 'Line access token';

//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$json_object = json_decode($json_string);
//取得データ
$replyToken = $json_object->{"events"}[0]->{"replyToken"};        //返信用トークン
$message_type = $json_object->{"events"}[0]->{"message"}->{"type"};    //メッセージタイプ
$return_message_text = "画像を受け取りました．";

if ($message_type == "image") {
    $message_id = $json_object->{"events"}[0]->{"message"}->{"id"};
    //画像保存
    get_message_content($accessToken, $message_id);
    //返信実行
    sending_messages($accessToken, $replyToken, $return_message_text);
}
//返信メッセージ


?>
<?php
//メッセージの送信
function sending_messages($accessToken, $replyToken, $return_message_text)
{
    //レスポンスフォーマット
    $response_format_text = [
        "type" => "text",
        "text" => $return_message_text
    ];

    //ポストデータ
    $post_data = [
        "replyToken" => $replyToken,
        "messages" => [$response_format_text]
    ];

    //curl実行
    $ch = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
    ));
    $result = curl_exec($ch);
    error_log($result);
    curl_close($ch);
}

function get_message_content($accessToken, $messageId)
{
    $ch = curl_init("https://api.line.me/v2/bot/message/" . $messageId . "/content");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
    ));
    $result = curl_exec($ch);
    curl_close($ch);

    //画像を保存
    file_put_contents('./images/' . date("Ymd-His") . '-' . mt_rand() . '.jpg', $result);
}

?>