<?php

function send_notification_FCM($notification_id, $title, $message, $id, $type) {
    $accesstoken = env('FCM_KEY');
    $URL = 'https://fcm.googleapis.com/fcm/send';
    $post_data = json_encode([
        'to' => $notification_id,
        'data' => [
            'body' => $message,
            'title' => $title,
            'type' => $type,
            'id' => $id,
        ],
        'notification' => [
            'body' => $message,
            'title' => $title,
            'type' => $type,
            'id' => $id,
            'sound' => 'default',
        ],
    ]);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: ' . $accesstoken,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
