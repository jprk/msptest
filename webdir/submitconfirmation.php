<?php
/**
 * Store grade confirmation to the database.
 */

/* Performance counter needs to know the initial time of invocation. */
$timeStart = microtime(true);

/* Global configuration */
require('config.php');

/* Read implementation of all classes that will be needed by our code. */
require(REQUIRE_DIR . 'LectwebSmarty.class.php');
require(REQUIRE_DIR . 'BaseBean.class.php');
require(REQUIRE_DIR . 'DatabaseBean.class.php');

require(REQUIRE_DIR . 'ExerciseBean.class.php');
require(REQUIRE_DIR . 'LectureLecturerBean.class.php');
require(REQUIRE_DIR . 'StudentBean.class.php');
require(REQUIRE_DIR . 'StudentLectureBean.class.php');
require(REQUIRE_DIR . 'LectureBean.class.php');
require(REQUIRE_DIR . 'SchoolYearBean.class.php');
require(REQUIRE_DIR . 'SessionDataBean.class.php');
require(REQUIRE_DIR . 'UserBean.class.php');

/* Fetch / initialize session.
   In order to prevent mixing of sessions for differens base URLS (live
   and testing web applications on the same machine, or different applications
   on the same machine), we will use a named session. The name of the
   session will be identical to the base directory of the application.
   Note: Do not forget to change the code of all related AJAX service-points
   that shall use the same session (e.g. submitpoints.php).
   TODO: Provide unified session starter for all service points. */
session_name('session_' . str_replace('/', '_', trim(BASE_DIR, '/')));
session_start();

/* Initialise session defaults in case that the session data storage does not
   contain the variables we would need later. */
SessionDataBean::conditionalInit(SchoolYearBean::getSchoolYearStart());

/* Construct a Smarty instance. Configuration has been specified in config.php. */
$smarty = new LectwebSmarty ($config, false);

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

$smarty->dbLog($timeStart, 'submitconfirmation', 'ajax');

if (UserBean::isRoleAtLeast(SessionDataBean::getUserRole(), USR_STUDENT))
{
    /* Create an instance of the database interface to the `student_lecture` table. */
    $slb = new StudentLectureBean(NULL, $smarty, NULL, NULL);
    /* Get the user id and lecture id and schoolyear */
    $lecture_id = SessionDataBean::getLectureId();
    /* Write data to the database. */
    try
    {
        $slb->studentAcceptsGrade(
            $_POST['grade'],
            SessionDataBean::getUserId(),
            SessionDataBean::getLectureId(),
            SessionDataBean::getSchoolYear()
        );
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
    $errorMessage = 'Nedostatečná oprávnění (k této akci musíte být přihlášení do systému).';
}
/* Status shall be one of
   0 ... saved successfully,
   1 ... error saving data (in this case the user will get an alert
         containing the text in $errorMessage)
   2 ... no change (changed the same number of points to the same number)
*/
$result = array('status' => $status, 'message' => $errorMessage);
$smarty->dbLog($timeStart, 'submitconfirmation', 'ajax_out', $result);
echo json_encode($result);

/* Close the dadtabase connection */
$smarty->dbClose($smlink);

?>
