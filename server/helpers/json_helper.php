<?php
function response_json_error($app, $http_code, $msg)
{
    $app->response()->status($http_code);
    $app->response()->header('X-Status-Reason', $msg);
    echo json_encode(array('error' => $msg));
}
function array_to_json($array)
{
    return json_decode(json_encode($array));
}
