<?php
/**
 * Serve a nonce value to the client.
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
    /* An error occured, send back an error message. */
    lectwebapi_exit_with_error($retval);
}

/* This will generate a new nonce and send it to the client. */
lectwebapi_handle_nonce();

