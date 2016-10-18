<?php
/**
 * LectWeb API client sample.
 *
 * This file mimics the process of client requesting experiment id, writing back the points awarded for the
 * entrance test that is bound to this experiment and obtaining the binary information about positive or
 * negative result of this test.
 *
 * @author Jan Přikryl <prikryl@fd.cvut.cz>
 * @version 0.2
 * @package lectweb_api
 */

/* Read the public part of the API.
   The `config.php` is an internal configuration file of LectWeb. When deploying, replace the following two
   lines with a direct require of `lectweb_api.php`. */
require ( 'config.php' );
require(REQUIRE_DIR.'/external/lectweb_api.php');

header('Content-Type: text/plain');

session_start();

/* Fetch the nonce from local session (if it is a result of previous successful `put points` operation) or
   request a new nonce from the server. */
$retval = lectwebapi_get_nonce($nonce);
if ($retval != LECTWEBAPI_E_OK)
{
    $errmsg = lectwebapi_strerror($retval);
    echo "ERROR $retval in lectwebapi_get_nonce(): $errmsg\n";
    exit();
}

echo "***********************************************************************************\n";
echo "got nonce #1: $nonce\n";
echo "***********************************************************************************\n";

$lecture_id = 14;
$student_id = 512432;
//$student_id = 460168;

echo "lecture id is set to $lecture_id\n";
echo "assuming the student $student_id has just logged in, simulating experiment_id request ...\n";
$retval = lectwebapi_get_experiment_group_id($nonce, $student_id, $lecture_id, $data);
if ($retval != LECTWEBAPI_E_OK)
{
    $errmsg = lectwebapi_strerror($retval);
    echo "ERROR $retval in lectwebapi_get_experiment_id(): $errmsg\n";
    session_destroy();
    exit();
}

echo "***********************************************************************************\n";
echo "got nonce #2: $nonce\n";
echo "***********************************************************************************\n";

echo "got some data, checking that the returned blob is bound to the same student_id ...\n";
if ($data['student_id'] != $student_id)
{
    echo "ERROR - student ids did not match in server response requesting the experiment id.\n";
    session_destroy();
    exit();
}

$lgrp_id = intval($data['labtask_group_id']);
$reparation_flag = intval($data['reparation']);
$time_from = $data['from'];
$time_to = $data['to'];
if ($lgrp_id > 0)
{
    echo "student_ids match, student $student_id shall attend the labtask group id $lgrp_id today.\n";
}
else
{
    echo "student_ids match, but there is no information about labtask group for student $student_id and today.\n";
    echo "as this might be a regular session (and not an extra/reparation) we will have to let the student select...\n";
    $lgrp_id = 42;
}
echo "reparation flag is set to `$reparation_flag`, timestamps `$time_from`-`$time_to`\n";

/* This is the place where the experiment id has been identifies and WebLab or some other tool may
   switch to testing or some other activity that will end with awarding the tested student with
   certain number of points. */
echo "##### here is the place where testing occurs ...\n";
echo "##### assuming the testing has finished ...\n";

$points = 2.0;

echo "recording the result for the test of student $student_id, experiment $lgrp_id of lecture $lecture_id ...\n";
$retval = lectwebapi_put_points($nonce, $student_id, $lecture_id, $lgrp_id, $points, 'This is a test', $passed_data);
if ($retval != LECTWEBAPI_E_OK)
{
    $errmsg = lectwebapi_strerror($retval);
    echo "ERROR $retval in lectwebapi_put_points(): $errmsg\n";
    session_destroy();
    exit();
}

if ($passed_data['student_id'] != $student_id)
{
    echo "ERROR - student ids did not match in server response when recording the result of the test.\n";
    session_destroy();
    exit();
}

echo "successfully recorded $points points for student $student_id, experiment $lgrp_id ...\n";
if ($passed_data['passed'])
    echo "RESULT FROM LECTWEB: OK, the student $student_id passed the test.\n";
else
    echo "RESULT FROM LECTWEB: NOK, the student $student_id did not pass the test.\n";

echo "***********************************************************************************\n";
echo "got nonce #3: $nonce\n";
echo "***********************************************************************************\n";
