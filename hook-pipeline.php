<?php
/*
Plugin Name: Hook Pipeline Plugin
Description: A plugin to Hook request using cURL.
Version: 1.0
Author: HEEPOKE
*/

add_action('save_post', 'curlRequest');

function curlRequest() {
    $apiUrl = '';

    $curlOptions = array(
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(array(
            'token' => 'TOKEN',
            'ref' => 'prod'
        )),
        CURLOPT_FAILONERROR => true,
    );

    $ch = curl_init();
    curl_setopt_array($ch, $curlOptions);

    $response = curl_exec($ch);

    if ($response === false) {
        echo 'cURL Error: ' . curl_error($ch);
    } else {
        echo 'cURL Response: ' . $response;
    }

    curl_close($ch);
}
