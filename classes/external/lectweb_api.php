<?php
/**
 * Lectweb API for WebLab.
 *
 * This file contains the client interface for collaboration between the Lectweb application and WebLab student testing
 * system, used at ČVUT, Faculty of Transportation Sciences.
 *
 * Note: We try to minimise communication between client (the Weblab application) and server (Lectweb) but the current
 * implementation stores allows for many client sessions on the WebLab server (basically: every internet browser in
 * the lab opens a new session to WebLab server) to request unique nonce values from the Lectweb server. In general,
 * this will generate many `get nonce` requests and not so many nonces will be generated as a part of client-server
 * communication. For the time being, this is a feature.
 *
 * @author Jan Přikryl <prikryl@fd.cvut.cz>
 * @version 2.1
 * @copyright 2015-2016 Czech Technical University in Prague
 * @package lectweb_api
 */

/**
 * Configuration variables.
 */
define('LECTWEBAPI_KEY', 'Y*/K7C)-T8l7riAR%2qA\'K<O.iGx7yij"Cip%"]O:V%hb#""]e/`CHi`WjZ|Mp6[');
define('LECTWEBAPI_URL_BASE', 'https://zolotarev.fd.cvut.cz/msaptest/');
define('LECTWEBAPI_URL_PUT_POINTS', LECTWEBAPI_URL_BASE . 'weblab_put_points.php');
define('LECTWEBAPI_URL_GET_NONCE', LECTWEBAPI_URL_BASE . 'weblab_get_nonce.php');
define('LECTWEBAPI_URL_GET_LECTURE_INFO', LECTWEBAPI_URL_BASE . 'weblab_get_lecture_info.php');
define('LECTWEBAPI_URL_GET_EXPERIMENT_GROUP_ID', LECTWEBAPI_URL_BASE . 'weblab_get_experiment_group_id.php');
define('LECTWEBAPI_URL_GET_EXPERIMENT_GROUPS', LECTWEBAPI_URL_BASE . 'weblab_get_experiment_groups.php');
define('LECTWEBAPI_URL_GET_STUDENT_LIST', LECTWEBAPI_URL_BASE . 'weblab_get_student_list.php');
define('LECTWEBAPI_COOKIE_FILE', '/tmp/lectweb_cookies.txt');
define('LECTWEBAPI_FORCE_HTTPS', true);

/**
 * Error codes.
 */
define('LECTWEBAPI_E_OK', 0); //> No error.
define('LECTWEBAPI_E_NONCE', -1000); //> No nonce present in the request.
define('LECTWEBAPI_E_HASH', -1001); //> The server and client hash codes do not match.
define('LECTWEBAPI_E_ADDR_BLOCKED', -1002); //> The client address is not allowed due to IP address filtering.
define('LECTWEBAPI_E_HTTPS_REQUIRED', -1003); //> The communication has to occur over secure HTTP.
define('LECTWEBAPI_E_FORMAT', -1004); //> The JSON message format does not contain required fields.
define('LECTWEBAPI_E_FIELD_FORMAT', -1005); //> Some of the fields in decoded message have an incompatible format.
define('LECTWEBAPI_E_SQL_ERROR', -1050); //> SQL exception ocurred.
define('LECTWEBAPI_E_LECTURE_INVALID', -1100); //> The submitted lecture code does not identify a lecture in lectweb.
define('LECTWEBAPI_E_LECTURE_DUPLICATE', -1101); //> The submitted lecture code is duplicated in lectweb database.
define('LECTWEBAPI_E_LAB_DUPLICATE', -1201); //> The requested lab for the student is duplicated in lectweb database.

/**
 * Note that debugging produces invalid JSON output.
 */
define('DEBUG', false);

/**
 * Return nonce value stored in the session or NULL in case that no nonce has been stored.
 * @return string|null Nonce string or NULL.
 */
function _get_nonce_from_client_session()
{
    if (isset($_SESSION['nonce']))
        return $_SESSION['nonce'];
    return null;
}

/**
 * Store the updated nonce value into client session.
 * @param string $nonce Nonce string.
 */
function _put_nonce_to_client_session($nonce)
{
    $_SESSION['nonce'] = $nonce;
}

/**
 * Compute the SHA-256 HMAC code for the data concatenated with nonce.
 * @param string $json_data JSON-encoded packet of data.
 * @param string $nonce     Nonce value expected by the server to accept this packet.
 * @return string HMAC hash code of the $json_data packet when combined with $nonce.
 */
function _get_hash($json_data, $nonce)
{
    return hash_hmac('sha256', $json_data . $nonce, LECTWEBAPI_KEY);
}

/**
 * Return string describing error number.
 * @param int $errno Error code.
 * @return string Description of the error code.
 */
function lectwebapi_strerror($errno)
{
    switch ($errno)
    {
        case LECTWEBAPI_E_OK:
            return 'No error.';
        case LECTWEBAPI_E_ADDR_BLOCKED:
            return 'This IP address is not allowed to connect.';
        case LECTWEBAPI_E_HTTPS_REQUIRED:
            return 'Secure HTTP required to fulfill this request.';
        case LECTWEBAPI_E_FORMAT:
            return 'Unsupported JSON message format.';
        case LECTWEBAPI_E_FIELD_FORMAT:
            return 'Some of the fields in decoded message have an incompatible format.';
        case LECTWEBAPI_E_HASH:
            return 'The hash codes do not match.';
        case LECTWEBAPI_E_NONCE:
            return 'Nonce has not been generated yet (or session has been restarted).';
        case LECTWEBAPI_E_SQL_ERROR:
            return 'SQL exception ocurred.';
        case LECTWEBAPI_E_LECTURE_INVALID:
            return 'A lecture with this code does not exist in lectweb.';
        case LECTWEBAPI_E_LECTURE_DUPLICATE:
            return 'Duplicate lectures with this code found in lectweb.';

    }

    return 'Unknown error code ' . strval($errno) . '.';
}


/**
 * Return an optional message to the user from server.
 * @param array $data Associative array that possibly has `user_msg` key.
 * @return string|null
 */
function lectwebapi_usermsg($data)
{
    return isset($data['user_msg']) ? $data['user_msg'] : null ;
}

/**
 * Generic client send/receive data from LectWeb.
 * @param string $url    LectWeb request url for the given action
 * @param string $nonce  In-out server-side nonce value generated by LectWeb and used to sign the packet.
 * @param array  $data   Output associative array with server response data. On error, contains the full server response.
 * @param bool   $session_start  True if a new cUrl session shall be started.
 * @return int Error code or LECTWEBAPI_E_OK if no error.
 */
function lectwebapi_request($url, &$nonce, &$data = null, $session_start = false)
{
    /* Construct request header */
    $headers = array(
        'Content-Type: application/json'
    );

    if ($data !== null)
    {
        /* Convert the associative array $data to JSON format. */
        $json_data = json_encode($data);
        /* Compute the hash of the data. */
        $hash = _get_hash($json_data, $nonce);

        if (DEBUG)
        {
            echo "content: $json_data\n";
            echo "nonce: $nonce\n";
            echo "hash: $hash\n\n";
        }

        /* Append the computed hash value to headers. */
        $headers[] = 'X-Hash: ' . $hash;
    }
    else
    {
        $json_data = null;
    }

    // see http://stackoverflow.com/questions/14192837/ssl-peer-certificate-or-ssh-remote-key-was-not-ok
    // see http://stackoverflow.com/questions/3757071/php-debugging-curl
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    /* The is used only when user posted some data. */
    if ($json_data !== null)
    {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    /* This is used only when requesting the first nonce. */
    if ($session_start) curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, LECTWEBAPI_COOKIE_FILE);
    curl_setopt($ch, CURLOPT_COOKIEFILE, LECTWEBAPI_COOKIE_FILE);

    /* Activate verbose cUrl logging when DEBUG is true. */
    $verbose_log = NULL;
    if (DEBUG)
    {
        $verbose_log = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose_log);
    }

    $result_json = curl_exec($ch);

    if (DEBUG)
    {
        var_dump($result_json);
        if ($result_json === FALSE)
        {
            printf("cUrl error (#%d): %s\n", curl_errno($ch), htmlspecialchars(curl_error($ch)));
        }

        rewind($verbose_log);
        $verbose_stream = stream_get_contents($verbose_log);

        echo "Verbose information:\n", htmlspecialchars($verbose_stream), "\n";
    }

    curl_close($ch);

    if (DEBUG)
    {
        echo "get_experiment_id() result\n==========\n" . print_r($result_json, true) . "\n==========\n";
    }

    /* The value of $result_json contains a new nonce, student id and experiment id. */
    $data = json_decode($result_json, true);

    if (isset($data['errno']))
    {
        $errno = $data['errno'];
        return $errno;
    }

    $nonce = null;
    $data = $result_json;
    return LECTWEBAPI_E_FORMAT;
}


/**
 * Fetch a fresh nonce value from the lectweb server.
 * This client code optionally asks lectweb server to generate a new nonce value that will be used to sign
 * `put points` messages from the client. In case that the nonce is available in the client session variable,
 * it is assumed that this value is a result of the previous `put points` and it is returned without asking
 * the server to generate a new one.
 * @param string $nonce Output variable holding the active nonce or null in case of an error.
 * @return int Error code or LECTWEBAPI_E_OK if no error.
 */
function lectwebapi_get_nonce(&$nonce)
{
    /* Check whether the local $_SESSION (that is, the connection between the WebLab client browser and WebLab!) does
       contain information about some already generated nonce. */
    $nonce = _get_nonce_from_client_session();
    if ($nonce !== null)
    {
        if (DEBUG) echo "Got a nonce stored in a client session.";
        /* There is a nonce value stored from previous operation with the client, i.e. a second or third test is
           running in the same client browser. */
        return LECTWEBAPI_E_OK;
    }

    /* No active nonce exists, we have to fetch it from the server. We will also start a new cUrl session. */
    $retval = lectwebapi_request(LECTWEBAPI_URL_GET_NONCE, $nonce, $data, true);
    if ($retval != LECTWEBAPI_E_OK)
    {
        /* An error occurred. */
        return $retval;
    }
    if (isset($data['nonce']))
    {
        $nonce = $data['nonce'];
        _put_nonce_to_client_session($nonce);
        return LECTWEBAPI_E_OK;
    }

    $nonce = null;
    return LECTWEBAPI_E_FORMAT;
}

/**
 * Return the experiment group id for the given student as recorded inside lectweb system.
 * For every week of labs, the student has to work on a predefined experiment, following a specific roster. The first
 * experiment is assigned by the lab lecturers/tutors, the sequence of the remaining experiments is given by the
 * experiment roster which is defined at the beginning of the semester. The function call updates the $nonce value if
 * the experiment group id request was successful.
 * @param string $nonce       In-out server-side nonce value generated by lectweb and used to sign the packet.
 * @param int    $student_id  Database id (ČVUT ID) of the student.
 * @param int    $lecture_id  Identifier of the lecture obtained by calling lectwebapi_get_lecture_info().
 * @param array  $data        Output associative array with new nonce, current student id and experiment id.
 *                            On error, contains the full server response.
 * @return int                Error code or LECTWEBAPI_E_OK if no error.
 */
function lectwebapi_get_experiment_group_id(&$nonce, $student_id, $lecture_id, &$data)
{
    $data = array('student_id' => $student_id, 'lecture_id' => $lecture_id);
    $retval = lectwebapi_request(LECTWEBAPI_URL_GET_EXPERIMENT_GROUP_ID, $nonce, $data);
    if ($retval != LECTWEBAPI_E_OK)
    {
        /* An error occurred. */
        return $retval;
    }
    if (isset($data['nonce']) && isset($data['student_id']) && isset($data['labtask_group_id']) &&
        isset($data['reparation']))
    {
        $nonce = $data['nonce'];
        _put_nonce_to_client_session($nonce);
        return LECTWEBAPI_E_OK;
    }
    $nonce = null;
    return LECTWEBAPI_E_FORMAT;
}

/**
 * Return current experiment groups as defined for the given lecture.
 * The dictionary of groups defines mapping from the group identifier (e.g. S1) to the identifiers of particular
 * experiments for that group. The experiment id value returned by `lectwebapi_get_experiment_id()` returns the
 * group id from this list. The function call updates the $nonce value if the request was successful.
 * @param string $nonce       In-out server-side nonce value generated by lectweb and used to sign the packet.
 * @param int    $lecture_id  Identifier of the lecture obtained by calling lectwebapi_get_lecture_info().
 * @param int    $schoolyear  Starting year of the requested school year (i.e. 2015 for 2015/2016).
 * @param array  $data        Output associative array with new nonce, current student id and experiment id.
 *                            On error, contains the full server response.
 * @return int                Error code or LECTWEBAPI_E_OK if no error.
 */
function lectwebapi_get_experiment_groups(&$nonce, $lecture_id, $schoolyear, &$data)
{
    $data = array('lecture_id' => $lecture_id, 'schoolyear' => $schoolyear);
    $retval = lectwebapi_request(LECTWEBAPI_URL_GET_EXPERIMENT_GROUPS, $nonce, $data);
    if ($retval != LECTWEBAPI_E_OK)
    {
        /* An error occurred. */
        return $retval;
    }

    if (isset($data['nonce']) && isset($data['groups']))
    {
        $nonce = $data['nonce'];
        _put_nonce_to_client_session($nonce);
        return LECTWEBAPI_E_OK;
    }
    $nonce = null;
    return LECTWEBAPI_E_FORMAT;
}

/**
 * Client code to submit a points record to lectweb as a JSON message.
 * The code uses a server-generated nonce to prevent replay attacks, the integrity of the JSON packed is verified by
 * using a HMAC with SHA-256 as a hashing function.
 * @param string $nonce       Server-side nonce value generated by lectweb (in/out).
 * @param int    $student_id  Database id (ČVUT ID) of the student.
 * @param int    $lecture_id  Identifier of the lecture obtained by calling lectwebapi_get_lecture_info().
 * @param int    $lgrp_id     Identifier of the experiment and also of the test that the student has to absolve.
 * @param float  $points      Points obtained from the test.
 * @param string $comment     Commentary.
 * @param array  $data        Output associative array with new nonce, current student id and experiment id.
 *                            On error, contains the full server response.
 * @return int                Error code or LECTWEBAPI_E_OK if no error.
 */
function lectwebapi_put_points(&$nonce, $student_id, $lecture_id, $lgrp_id, $points, $comment, &$data)
{
    $data = array(
        'student_id' => $student_id, 'lecture_id' => $lecture_id, 'labtask_group_id' => $lgrp_id,
        'points' => $points, 'comment' => $comment);
    $retval = lectwebapi_request(LECTWEBAPI_URL_PUT_POINTS, $nonce, $data);
    if ($retval != LECTWEBAPI_E_OK)
    {
        /* An error occurred. */
        return $retval;
    }
    if (isset($data['nonce']) && isset($data['student_id']) && isset($data['passed']))
    {
        $nonce = $data['nonce'];
        _put_nonce_to_client_session($nonce);
        return LECTWEBAPI_E_OK;
    }
    $nonce = null;
    return LECTWEBAPI_E_FORMAT;
}

/**
 * Client code to request the lecture information for the lecture with the given code.
 * The code uses a server-generated nonce to prevent replay attacks, the integrity of the JSON packed is verified by
 * using a HMAC with SHA-256 as a hashing function.
 * @param string $nonce         Server-side nonce value generated by lectweb (in/out).
 * @param string $lecture_str   String identifier of the lecture (20SK, 11FY1, 11MSP, 17TDL, etc.).
 * @param array  $data          Output associative array with new nonce and lecture info.
 *                              On error, contains the full server response.
 * @return int                  Error code or LECTWEBAPI_E_OK if no error.
 */
function lectwebapi_get_lecture_info(&$nonce, $lecture_str, &$data)
{
    $data = array('lecture_str' => $lecture_str);
    $retval = lectwebapi_request(LECTWEBAPI_URL_GET_LECTURE_INFO, $nonce, $data);
    if ($retval != LECTWEBAPI_E_OK)
    {
        /* An error occurred. */
        return $retval;
    }

    if (isset($data['lecture']) && isset($data['nonce']))
    {
        $nonce = $data['nonce'];
        _put_nonce_to_client_session($nonce);
        return LECTWEBAPI_E_OK;
    }
    $nonce = null;
    return LECTWEBAPI_E_FORMAT;
}

/**
 * Client code to request the list of students for the given lecture.
 * The code uses a server-generated nonce to prevent replay attacks, the integrity of the JSON packed is verified by
 * using a HMAC with SHA-256 as a hashing function.
 * @param string $nonce         Server-side nonce value generated by lectweb (in/out).
 * @param int    $lecture_id    Identifier of the lecture obtained by calling lectwebapi_get_lecture_info().
 * @param int    $schoolyear    Starting year of the requested school year (i.e. 2015 for 2015/2016).
 * @param array  $data          Output associative array with new nonce and the student list.
 *                              On error, contains the full server response.
 * @return int                  Error code or LECTWEBAPI_E_OK if no error.
 */
function lectwebapi_get_student_list(&$nonce, $lecture_id, $schoolyear, &$data)
{
    $data = array('lecture_id' => $lecture_id, 'schoolyear' => $schoolyear);
    $retval = lectwebapi_request(LECTWEBAPI_URL_GET_STUDENT_LIST, $nonce, $data);

    if ($retval != LECTWEBAPI_E_OK)
    {
        /* An error occurred. */
        return $retval;
    }

    if (isset($data['students']) && isset($data['nonce']))
    {
        $nonce = $data['nonce'];
        _put_nonce_to_client_session($nonce);
        return LECTWEBAPI_E_OK;
    }
    $nonce = null;
    return LECTWEBAPI_E_FORMAT;
}
