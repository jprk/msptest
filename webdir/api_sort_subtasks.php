<?php
/**
 * Store new order of subtasks into `subtasks` table.
 */

/* Global configuration */
require('config.php');

/* Read implementation of all classes that will be needed by our code. */
require(REQUIRE_DIR . 'CPPSmarty.class.php');
require(REQUIRE_DIR . 'BaseBean.class.php');
require(REQUIRE_DIR . 'DatabaseBean.class.php');

// require ( REQUIRE_DIR . 'PointsBean.class.php');
require(REQUIRE_DIR . 'LectureBean.class.php');
require(REQUIRE_DIR . 'SchoolYearBean.class.php');
require(REQUIRE_DIR . 'SessionDataBean.class.php');
require(REQUIRE_DIR . 'SubtaskBean.class.php');
require(REQUIRE_DIR . 'UserBean.class.php');

/* Fetch / initialize session.
   In order to prevent mixing of sessions for differens base URLS (live
   and testing web applications on the same machine, or different applications
   on the same machine), we will use a named session. The name of the
   session will be identical to the base directory of the application. */
session_name(BASE_DIR);
session_start();

/* Initialise session defaults in case that the session data storage does not
   contain the variables we would need later. */
SessionDataBean::conditionalInit(SchoolYearBean::getSchoolYearStart());

/* Construct a Smarty instance. Configuration has been specified in config.php. */
$smarty = new CPPSmarty ($config, false);

/* Initialise error message and status. */
$status = 2;
$errorMessage = '';

/* Initialise database connection */
try
{
    $smlink = $smarty->dbOpen();
} catch (Exception $e)
{
    /* Make sure smlink has some value. */
    $smlink = NULL;
    $status = 1;
    $errorMessage = $e->getMessage();
}

if (UserBean::isRoleAtLeast(SessionDataBean::getUserRole(), USR_LECTURER))
{
    try
    {
        /* Create an instance of database interface to the `subtasks` table. */
        $subtaskBean = new SubtaskBean(NULL, $smarty, NULL, NULL);
        /* If submitted correctly, $_POST array will contain a single key `subtasks` holding an
           array of subtask ids after the user moved a subtask item to another position. */
        error_log(print_r($_POST, true));
        $subtaskBean->shuffleEntries($_POST['subtask']);
        $status = 0;
    } catch (Exception $e)
    {
        $status = 1;
        $errorMessage = $e->getMessage();
    }
}
else
{
    $status = 1;
    $errorMessage = 'Nedostatečná oprávnění (k této akci potřebujete alespoň práva učitele).';
}
/* Status shall be one of
   0 ... saved successfully,
   1 ... error saving data (in this case the user will get an alert
         containing the text in $errorMessage)
   2 ... no change (changed the same number of points to the same number)
*/
$result = array('status' => $status, 'message' => $errorMessage);
echo json_encode($result);

/* Close the dadtabase connection */
$smarty->dbClose($smlink);

?>
