<?php
/**
 * @param array $response
 */
function json_p(array $response)
{
    exit(
        $_GET['callback'] . '(' . json_encode($response) . ')'
    );
}

/**
 * @param string $msg
 */
function fail($msg = 'Failed')
{
    json_p([
        'success' => false,
        'message' => $msg
    ]);
}

/**
 * @param string $msg
 */
function success($msg = 'Success')
{
    json_p([
        'success' => true,
        'message' => $msg
    ]);
}

/**
 * @param $data
 */
function json_response($data)
{
    if (isset( $_GET['callback'] )) {
        json_p($data);
    }

    exit( json_encode($data) );
}