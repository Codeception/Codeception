<?php
function RESTServer()
{
    // find the function/method to call
    $callback = NULL;
    if (preg_match('/rest\/([^\/]+)/i', $_SERVER['REQUEST_URI'], $m)) {
        if (isset($GLOBALS['RESTmap'][$_SERVER['REQUEST_METHOD']][$m[1]])) {
            $callback = $GLOBALS['RESTmap'][$_SERVER['REQUEST_METHOD']][$m[1]];
        }
    }


    if ($callback) {

        // get the request data
        $data = NULL;
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $data = $_GET;
        } else if ($tmp = file_get_contents('php://input')) {
            $data = json_decode($tmp);
        }

        $response = call_user_func($callback, $data);
        if (is_scalar($response)) {
            print $response;
            return;
        }
        print json_encode($response);
    }
}
