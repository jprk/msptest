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
try
{
    $result = dibi::query('SELECT st.id,st.login,st.surname,st.firstname,st.yearno,st.groupno,st.email,st.coeff '.
                          'FROM [student] st LEFT JOIN [stud_lec] sl ON st.id=sl.student_id '.
                          'WHERE sl.year=%s AND sl.lecture_id=%s',
                          intval($request_data['schoolyear']), $request_data['lecture_id'] );
    $students = $result->fetchAll();
}
catch (Dibi\DriverException $de)
{
    /* An error occurred, send back an error message. */
    lectwebapi_exit_with_error($retval);
}

/*
 * Result is a valid request, containing student id.
 * TODO: Process it and return the experiment id.
 */

$data = array('students' => $students);

/* Send the JSON-encoded $result back to the client, appending the current nonce if necessary. */
lectwebapi_get_nonce_send_data($data);

