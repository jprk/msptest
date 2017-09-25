<?php
/**
 * Provide the client with experiment id for the given student.
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
    /* An error occurred, send back an error message. */
    lectwebapi_exit_with_error($retval);
}

/* This will determine the experiment id assigned to the student and provide an JSON-encoded packet of data. */
$retval = lectwebapi_get_request_data($experiment_data);
if ($retval != LECTWEBAPI_E_OK)
{
    /* An error occured, send back an error message. */
    lectwebapi_exit_with_error($retval);
}

/* Result is a valid request, containing student id and lecture id. */
$lecture_id = $experiment_data['lecture_id'];
$student_id = $experiment_data['student_id'];

/* Process it and return the experiment id. */
dibi::connect([
                  'driver'   => 'mysql',
                  'host'     => 'localhost',
                  'username' => $config['db']['user'],
                  'password' => $config['db']['pass'],
                  'database' => $config['db']['data'],
                  'charset'  => 'utf8',
              ]);

/* Get the replacement */
$result = dibi::query('SELECT *, '.
                      'ADDTIME(rd.date, IFNULL(rd.mfrom,ex.from)) AS time_from, '.
                      'ADDTIME(rd.date, IFNULL(rd.mto,ex.to)) AS time_to '.
                      'FROM [replacement_dates] rd '.
                      'LEFT JOIN [repl_stud] rs ON rd.id = rs.replacement_id '.
                      'LEFT JOIN [labtask_group] lg ON rs.lgrp_id = lg.id '.
                      'LEFT JOIN [exercise] ex ON ex.id = rd.exercise_id '.
                      'WHERE rd.date = CURDATE() '.
                      'AND rs.dateto IS NULL '.
                      'AND NOW() > (ADDTIME(rd.date, IFNULL(rd.mfrom,ex.from)) - INTERVAL 5 MINUTE) '.
                      'AND NOW() < (ADDTIME(rd.date, IFNULL(rd.mto,ex.to)) - INTERVAL 15 MINUTE) '.
                      'AND rs.student_id=%s '.
                      'AND ex.lecture_id=%s',
                      $student_id, $lecture_id);
if (count($result) == 0)
{
    $lgrp_id = 0;
    $reparation = 0;
    $time_from = '';
    $time_to = '';
}
elseif (count($result) > 1)
{
    /* Cannot have students visiting two replacement labs concurrently. */
    lectwebapi_exit_with_error(LECTWEBAPI_E_LAB_DUPLICATE);
    return;
}
else
{
    $experiment = $result->fetchAll()[0];
    $lgrp_id = $experiment['group_id'];
    $time_from = $experiment['time_from'];
    $time_to = $experiment['time_to'];
    $reparation = 1;
}
$data = array(
    'student_id' => $student_id, 'labtask_group_id' => $lgrp_id, 'reparation' => $reparation,
    'from' => $time_from, 'to' => $time_to);

/* Send the JSON-encoded $result back to the client, appending the current nonce if necessary. */
lectwebapi_get_nonce_send_data($data);


