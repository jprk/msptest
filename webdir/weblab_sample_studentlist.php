<?php
/**
 * LectWeb API client sample for requesting the list of students of a lecture.
 *
 * @author Jan Přikryl <prikryl@fd.cvut.cz>
 * @version 0.1
 * @package lectweb_api
 */

/* Read the public part of the API.
   The `config.php` is an internal configuration file of LectWeb. When deploying, replace the following two
   lines with a direct require of `lectweb_api.php`. */
require('config.php');
require(REQUIRE_DIR.'/external/lectweb_api.php');

header('Content-Type: text/plain; charset=utf-8');

session_start();

/* Fetch the nonce from local session (if it is a result of previous successful `put points` operation) or
   request a new nonce from the server. */
$retval = lectwebapi_get_nonce($nonce);
if ($retval != LECTWEBAPI_E_OK)
{
    $error_msg = lectwebapi_strerror($retval);
    echo "ERROR $retval in lectwebapi_get_nonce(): $error_msg\n";
    exit();
}

/* --------------------------------------------------------------------------------------
   PARAMETERS
   -------------------------------------------------------------------------------------- */
$lecture_str = '11FY2';
$schoolyear = 2017;

echo "***********************************************************************************\n";
echo "got nonce #1: $nonce\n";
echo "***********************************************************************************\n";

echo "requesting the lecture info for lecture $lecture_str\n";
$retval = lectwebapi_get_lecture_info($nonce, $lecture_str, $data);
if ($retval != LECTWEBAPI_E_OK)
{
    $error_msg = lectwebapi_strerror($retval);
    echo "ERROR $retval in lectwebapi_get_lecture_info(): $error_msg\n";
    session_destroy();
    exit();
}

$lecture = $data['lecture'];
$lecture_id = $lecture['id'];
echo "Lecture is "; print_r($lecture); echo "\n";

echo "***********************************************************************************\n";
echo "got nonce #2: $nonce\n";
echo "***********************************************************************************\n";

echo "requesting the student list for lecture $lecture_id in school year $schoolyear/" . ($schoolyear+1) . "\n";

$retval = lectwebapi_get_student_list($nonce, $lecture_id, $schoolyear, $data);
if ($retval != LECTWEBAPI_E_OK)
{
    $error_msg = lectwebapi_strerror($retval);
    $user_msg = lectwebapi_usermsg($data);
    echo "ERROR $retval in lectwebapi_get_student_list(): $error_msg\n";
    if (!is_null($user_msg))
    {
        echo "Additional info: $user_msg\n";
    }
    session_destroy();
    exit();
}

echo "got " . count($data['students']) . " students\n";
echo "sample student entry (contents of students[0]):\n";
print_r($data['students'][0]);
echo "\n";

echo "***********************************************************************************\n";
echo "got nonce #3: $nonce\n";
echo "***********************************************************************************\n";

echo "requesting the list of experiment groups for lecture $lecture_id ";
echo "in school year $schoolyear/" . ($schoolyear+1) . "\n";

$retval = lectwebapi_get_experiment_groups($nonce, $lecture_id, $schoolyear, $data);
if ($retval != LECTWEBAPI_E_OK)
{
    $error_msg = lectwebapi_strerror($retval);
    $user_msg = lectwebapi_usermsg($data);
    echo "ERROR $retval in lectwebapi_get_experiment_groups(): $error_msg\n";
    if (!is_null($user_msg))
    {
        echo "Additional info: $user_msg\n";
    }
    session_destroy();
    exit();
}

echo "listing all " . count($data['groups']) . " available experiment groups for lecture $lecture_id\n";
echo "(note: group index is the group number, experiment index is the experiment number):\n";
print_r($data['groups']);
echo "\n";

echo "***********************************************************************************\n";
echo "got nonce 4: $nonce\n";
echo "***********************************************************************************\n";

