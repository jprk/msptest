<?php
/**
 * Serve the list of students to the client.
 */
/* Global configuration */
require('config.php');
require(REQUIRE_DIR.'external/lectweb_api.private.php');
require(REQUIRE_DIR.'external/dibi/loader.php');

/* Start the session handling code at the server. */
session_start();

/* Check the parameters of the request. */
$retval = lectwebapi_request_check();
if ($retval != LECTWEBAPI_E_OK)
{
    /* An error occured, send back an error message. */
    lectwebapi_exit_with_error($retval);
}

/* If the request comes from the expected client and has the correct nonce and a correct format,
   this will decode the JSON-encoded request data. */
$retval = lectwebapi_get_request_data($request_data);
if ($retval != LECTWEBAPI_E_OK)
{
    /* An error occurred, send back an error message. */
    lectwebapi_exit_with_error($retval);
}

/* Now we have to connect to the database. */
dibi::connect([
                  'driver'   => 'mysql',
                  'host'     => 'localhost',
                  'username' => $config['db']['user'],
                  'password' => $config['db']['pass'],
                  'database' => $config['db']['data'],
                  'charset'  => 'utf8',
              ]);

/* Find a lecture with the appropriate code string. */
$result = dibi::query('SELECT id,code,title,locale FROM [lecture] WHERE code=%s', $request_data['lecture_str']);
if (count($result) == 0)
{
    /* Nothing found, unknown lecture code. */
    lectwebapi_exit_with_error(LECTWEBAPI_E_LECTURE_INVALID);
}
elseif (count($result) > 1)
{
    /* More than a single row returned, this indicates inconsistency in lectweb lectures. */
    lectwebapi_exit_with_error(LECTWEBAPI_E_LECTURE_DUPLICATE);
}

$lecture = $result->fetchAll()[0];

//echo "lecture:\n-----\n";
//print_r($lecture);
//echo "\n-----\n";

/*
 * Result is a valid request, containing student id.
 * TODO: Process it and return the experiment id.
 */

$data = array('lecture' => $lecture);

/* Send the JSON-encoded $result back to the client, appending the current nonce if necessary. */
lectwebapi_get_nonce_send_data($data);

