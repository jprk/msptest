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

/* List all groups and the related info. */
try
{
    /* Initialise the returned array. */
    $groups = array();

    /* As the first step we will fetch all the internal group identifiers for current experiment groups in
       this lecture. */
    $result = dibi::query('SELECT `id`, `group_id` FROM `labtask_group` ' .
                          'WHERE `year`=%u AND `lecture_id`=%u ORDER BY group_id',
                          intval($request_data['schoolyear']), intval($request_data['lecture_id']) );
    /* This will create an object similar to `array(group_id => id)`. */
    $group_map = $result->fetchPairs();

    /* Do not continue in case that there are no experiment groups. */
    if (!empty($group_map))
    {
        /* Query all experiments for all groups. The `%l` is a Dibi shortcut for list. We need to refer to the
           internal group ids from `labtask_group` table (i.e. those ids that change every year). These ids are
           stored as keys in `$group_map`. */
        $result = dibi::query('SELECT `lgrp_id`, `title`, `mtitle`, `ival1` AS `experiment_id` FROM `lgrp_sec` ' .
            'LEFT JOIN `section` ON `id`=`section_id` ' .
            'WHERE `lgrp_id` IN %l ORDER BY `ival1`', array_keys($group_map));
        $experiments = $result->fetchAll();

        /* Merge both arrays together.
           TODO: Look at dibi::fetchAssoc(), probably we could join both queries.  */
        foreach ($experiments as $exp)
        {
            $lgrp_id = $exp['lgrp_id'];
            $exp_id = $exp['experiment_id'];
            $group_id = $group_map[$lgrp_id];
            unset($exp['lgrp_id']);
            unset($exp['experiment_id']);
            if (!array_key_exists($group_id, $groups)) $groups[$group_id] = array();
            $groups[$group_id][$exp_id] = $exp;
        }
    }
}
catch (Dibi\DriverException $de)
{
    /* An error occurred, send back an error message. */
    lectwebapi_exit_with_error(LECTWEBAPI_E_SQL_ERROR, $de->getSql());
}

/*
 * Result is a valid request, containing student id.
 * TODO: Process it and return the experiment id.
 */

$data = array('groups' => $groups);

/* Send the JSON-encoded $result back to the client, appending the current nonce if necessary. */
lectwebapi_get_nonce_send_data($data);

