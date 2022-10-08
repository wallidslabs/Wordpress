<?php
/**
 * Plugin Name: Wallids - Cloud WAF
 * Plugin URI: https://wallids.com
 * Description: Security Layer
 * Version: 1.2.5
 * Author: Wallids LABS
 * Author URI: https://github.com/wallidslabs
 */

add_action('init', 'process_post');

function process_post()
{
#region VERIABLES
    include plugin_dir_path(__FILE__) . '/wp-wallids-settings.php';
    $targetUrl = "https://api.wallids.com/tracing/client";
    $logRequestModel = new stdClass();
    $infoModel = new stdClass();

#region OPTIONS
    $wallids_security_settings_options = get_option('wallids_security_settings_option_name');
    $secret_key_0 = $wallids_security_settings_options['secret_key_0'];
    $monitoring_1 = $wallids_security_settings_options['monitoring_1'];

#region INIT
    $logRequestModel->secretKey = $secret_key_0;
    $logRequestModel->scheme = $_SERVER['REQUEST_SCHEME'];

    $infoModel->url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $infoModel->requestType = $_SERVER['REQUEST_METHOD'];


#region GET REQUEST DATA

    $json_string = json_encode($_POST);

    $requestData = json_decode(file_get_contents("php://input"));

    if (is_null($requestData)) {

        parse_str(file_get_contents("php://input"), $data);
        $data = (object)$data;

        $requestData = $data;
    }

    if ($requestData == new stdClass()) {

        $postData = $_POST;
        parse_str(file_get_contents('php://input'), $requestData);
        $postData = (object)$postData;

        $requestData = $postData;
    }

#region LOAD
    $infoModel->formDatas = $requestData;

    $virtualPost = false;

    if ($_SERVER['REQUEST_METHOD'] == "POST" || $_SERVER['REQUEST_METHOD'] == "PUT") {
        $infoModel->request = $requestData;
    } else {
        if (strlen($_SERVER['QUERY_STRING']) == 0) {
            $infoModel->request = $infoModel->url;
        } else {
            $infoModel->requestType = "POST";
            parse_str($_SERVER['QUERY_STRING'], $queryStringArr);
            $infoModel->request = $queryStringArr;
            $virtualPost = true;
        }
    }

    $infoModel->ip = $_SERVER['REMOTE_ADDR'];

    $infoModel->responseData = "";
    $infoModel->statusCode = 0;
    $infoModel->errorMessage = "";

    $logRequestModel->info = $infoModel;

#region SEND
    $response = wp_remote_post($targetUrl, array(
        'method' => 'POST',
        'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
        'body' => json_encode($logRequestModel),
        'data_format' => 'body',
    )
    );
    $responseJson = wp_remote_retrieve_body($response);

#region PARSE
    $apiResult = json_decode($responseJson, true);
    $urlArr = parse_url($infoModel->url);

    $newUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" . "://$_SERVER[HTTP_HOST]" . $urlArr['path'];

    if ($monitoring_1 == "off") {
        if ($apiResult['attack'] != 0) {
            if ($virtualPost) {
                $_SERVER['QUERY_STRING'] = http_build_query($apiResult['body']);
                $newUrl = $newUrl . "?" . $_SERVER['QUERY_STRING'];
                 // Next Feature
                //header('Location: ' . $newURL);
                //die();
            } else {
                if ($_SERVER['REQUEST_METHOD'] == "GET") {
                    // Next Feature
                    //header('Location: ' . $apiResult['body']);
                    //die();
                } else {
                    $_POST = $apiResult['body'];
                }
            }
        }
    }
}
