<?php
require(__DIR__ . '/lectweb_api.php');

/**
 * Private (server) part of Lectweb API for WebLab.
 *
 * This file contains the client interface for collaboration between the Lectweb application and WebLab student testing
 * system, used at ČVUT, Faculty of Transportation Sciences.
 *
 * Note: We try to minimise communication between client (the Weblab application) and server (Lectweb) but the current
 * implementation stores allows for many client sessions on the WebLab server (basically: every internet browser in
 * the lab opens a new session to WebLab server) to request_data unique nonce values from the Lectweb server. In general,
 * this will generate many `get nonce` requests and not so many nonces will be generated as a part of client-server
 * communication. For the time being, this is a feature.
 *
 * @author Jan Přikryl <prikryl@fd.cvut.cz>
 * @version 2.1
 * @copyright 2015-2016 Czech Technical University in Prague
 * @package lectweb_api
 */

/**
 * No constant arrays until PHP 5.6
 */
$LECTWEBAPI_IP_WHITELIST = array(
    '147.231.13.188',
    '147.32.72.206',
    '147.32.72.150',
    '147.32.100.237',
    '147.32.100.240',
    '147.32.100.241',
    '147.32.100.242',
    '127.0.0.1');

/**
 * Compute a new nonce that will be used for validating the data written through the API.
 * This nonce is used as an additional security measure against replaying attacks when
 * submitting student evaluation by WebLab.
 * @return string JSON-encoded string containing the actual nonce value.
 */
function _get_nonce()
{
    $nonce = md5(microtime());
    if (isset($_SESSION['nonce']))
        $nonce .= $_SESSION['nonce'];
    $nonce = hash('sha256', $nonce);
    $_SESSION['nonce'] = $nonce;
    return $nonce;
}

/**
 * Validate a client request and decode it into an array.
 * Uses the nonce value stored in the server session. Fails if the session does not contain a nonce value or
 * if the hash code of the message concatenated with the nonce does not correspond to the hash code provided
 * by the client.
 * @param array $request_data Associative array specifying the request parameters from the client.
 * @return int Error code or LECTWEBAPI_E_OK in case of no error.
 */
function lectwebapi_get_request_data(&$request_data)
{
    /* Initialise the $request_data to be empty. */
    $request_data = null;
    /* Get the current nonce.
       In case that no nonce has been generated, fail. */
    if (isset($_SESSION['nonce']))
    {
        /* Nonce exists, fetch it. */
        $nonce = $_SESSION['nonce'];

        /* Get the client data. */
        $json_data = file_get_contents('php://input');

        /* Concatenate that data with nonce and compute the hash code. */
        $server_hash = _get_hash($json_data, $nonce);

        /* Get the client hash code.
           Warning: this is going to work only in Apache environment, no nginx and other servers provide this. */
        $request_headers = apache_request_headers();
        $client_hash = $request_headers['X-Hash'];

        if (DEBUG)
        {
            echo "lectwebapi_get_request_data()\n";
            echo "client hash is $client_hash\n";
            echo "server nonce is $nonce\n";
            echo "server hash is $server_hash\n";
            print_r($json_data);
            echo "\n";
        }

        if ($server_hash == $client_hash)
        {
            $request_data = json_decode($json_data, true);
            return LECTWEBAPI_E_OK;
        }
        else
            return LECTWEBAPI_E_HASH;
    }

    return LECTWEBAPI_E_NONCE;
}

/**
 * Check the request_data parameters and decide if the request_data may be processed.
 * Checks the IP address of the request_data origin and, if instructed, also the HTTP mode (if required, the request_data
 * is allowed only over a secure HTTP connection).
 * @return int An error code or LECTWEBAPI_E_OK in case of no error.
 */
function lectwebapi_request_check()
{
    /* The array with IP whitelist values cannot be constant in PHP version < 5.6 */
    global $LECTWEBAPI_IP_WHITELIST;
    /* Test if the IP address is permitted. */
    if (!in_array($_SERVER['REMOTE_ADDR'], $LECTWEBAPI_IP_WHITELIST))
    {
        return LECTWEBAPI_E_ADDR_BLOCKED;
    }
    /* Check whether the configuration requires a secure HTTP connection. */
    if (LECTWEBAPI_FORCE_HTTPS)
    {
        /* Secure HTTP required. Did the request_data arrive over secure HTTP? */
        if (!isset($_SERVER['HTTPS']))
        {
            return LECTWEBAPI_E_HTTPS_REQUIRED;
        }
    }

    return LECTWEBAPI_E_OK;
}

/**
 * Server-side code that provides first nonce value for the client.
 * This is called every time a new client connects.
 */
function lectwebapi_handle_nonce()
{
    lectwebapi_get_nonce_send_data();
}

/**
 * Send an error message to the client and exit the script.
 * Sends a JSON-encoded error message to the client and exits.
 * @param int    $errno    Error code.
 * @param string $user_msg Detailed description of the error.
 */
function lectwebapi_exit_with_error($errno, $user_msg=null)
{
    if (is_null($user_msg))
    {
        $user_msg = lectwebapi_strerror($errno);
    }
    $data = array('errno' => $errno, 'user_msg' => $user_msg);
    lectwebapi_send_data($data);
    exit();
}

/**
 * Send JSON-encoded response to the client and generate a new nonce if necessary.
 * Sends a packet containing keys and values from an associative array $data as a JSON-encoded packet to the
 * client. If $data does not contain the `nonce` key, a new nonce will be generated and stored as $data['nonce']
 * before encoding.
 * @param array $data Associative PHP array with response parameters.
 */
function lectwebapi_get_nonce_send_data($data = array())
{
    if (!isset($data['nonce']))
    {
        $data['nonce'] = _get_nonce();
    }
    lectwebapi_send_data($data);
}

/**
 * Send JSON-encoded response to the client.
 * Sends a packet containing keys and values from an associative array $data as a JSON-encoded packet to the
 * client. If $data does not contain the `errno` key, a new element $data['errno'] = LECTWEBAPI_E_OK will be added to
 * the array before encoding.
 * @param array $data Associative PHP array with response parameters.
 */
function lectwebapi_send_data($data = array())
{
    header('Content-Type: application/json');
    if (!isset($data['errno']))
    {
        $data['errno'] = LECTWEBAPI_E_OK;
    }
    $json_data = json_encode($data);
    echo $json_data;
}
