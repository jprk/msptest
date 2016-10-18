<?php
/**
 * Store JSON formatted evaluation data for one student test from WebLab.
 */

/* Global configuration */
require ( 'config.php' );
require(REQUIRE_DIR.'/external/lectweb_api.private.php');

/* Start the session handling code at the server. */
session_start();

/* Check the parameters of the request. */
$retval = lectwebapi_request_check();
if ($retval != LECTWEBAPI_E_OK)
{
    /* An error occurred, send back an error message. */
    lectwebapi_exit_with_error($retval);
}

/* Process the JSON request and return request data or NULL in case of an error. */
$retval = lectwebapi_get_request_data($point_data);
if ($retval != LECTWEBAPI_E_OK)
{
    /* An error occurred, send back an error message. */
    lectwebapi_exit_with_error($retval);
}

/* The $points is valid inasmuch it could be parsed as a JSON packet and it contains the required fields.
   Now we have to verify that the values of the submitted fields make some sense to us. */
$student_id = $point_data['student_id'];
$lgrp_id = $point_data['labtask_group_id'];
$points = $point_data['points'];
$comment = $point_data['comment'];
if (! is_int($student_id) || ! is_int($lgrp_id) || ! is_numeric($points))
{
    /* The result does not contain meaningful student id.. */
    lectwebapi_exit_with_error(LECTWEBAPI_E_FIELD_FORMAT);
}

/* Send back information about the result of the test. */
$data = array('student_id' => $student_id, 'passed' => false);

/* Send the JSON-encoded $data back to the client, appending the current nonce if necessary. */
lectwebapi_get_nonce_send_data($data);
