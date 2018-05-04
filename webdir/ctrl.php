<?php
/**
 * Controller of the CPPSmarty application.
 *
 * (c) 2009,2010,2011,2012 Jan Prikryl, FD CVUT Praha
 *
 * Version: $Id: ctrl.php 92 2013-03-22 11:24:32Z prikryl $
 */

/* Performance counter needs to know the initial time of invocation. */
$timeStart = microtime(true);

/* Global configuration */
require('config.php');

/* Read implementation of all classes that will be needed by our code. */
require(REQUIRE_DIR . 'CPPSmarty.class.php');
require(REQUIRE_DIR . 'BaseBean.class.php');
require(REQUIRE_DIR . 'DatabaseBean.class.php');

require(REQUIRE_DIR . 'ArticleBean.class.php');
require(REQUIRE_DIR . 'AssignmentsBean.class.php');
require(REQUIRE_DIR . 'DeadlineExtensionBean.class.php');
require(REQUIRE_DIR . 'EvaluationBean.class.php');
require(REQUIRE_DIR . 'EvaluationTasksBean.class.php');
require(REQUIRE_DIR . 'ExerciseBean.class.php');
require(REQUIRE_DIR . 'ExerciseRepBookingBean.class.php');
require(REQUIRE_DIR . 'ExerciseReplacementBean.class.php');
require(REQUIRE_DIR . 'ExerciseListBean.class.php');
require(REQUIRE_DIR . 'ExerciseTutorsBean.class.php');
require(REQUIRE_DIR . 'FileBean.class.php');
require(REQUIRE_DIR . 'FormAssignmentBean.class.php');
require(REQUIRE_DIR . 'FormSolutionsBean.class.php');
require(REQUIRE_DIR . 'ImportBean.class.php');
require(REQUIRE_DIR . 'LabtaskBean.class.php');
require(REQUIRE_DIR . 'LabtaskGroupBean.class.php');
require(REQUIRE_DIR . 'LabtaskGroupSectionBean.class.php');
require(REQUIRE_DIR . 'LDAPConnection.class.php');
require(REQUIRE_DIR . 'LoginBean.class.php');
require(REQUIRE_DIR . 'MenuBean.class.php');
require(REQUIRE_DIR . 'NewsBean.class.php');
require(REQUIRE_DIR . 'NoteBean.class.php');
require(REQUIRE_DIR . 'PointsBean.class.php');
require(REQUIRE_DIR . 'LectureBean.class.php');
require(REQUIRE_DIR . 'LectureLecturerBean.class.php');
require(REQUIRE_DIR . 'LecturerBean.class.php');
require(REQUIRE_DIR . 'SchoolYearBean.class.php');
require(REQUIRE_DIR . 'SectionBean.class.php');
require(REQUIRE_DIR . 'SessionDataBean.class.php');
require(REQUIRE_DIR . 'SolutionBean.class.php');
require(REQUIRE_DIR . 'StudentBean.class.php');
require(REQUIRE_DIR . 'StudentExerciseBean.class.php');
require(REQUIRE_DIR . 'StudentGroupBean.class.php');
require(REQUIRE_DIR . 'StudentLectureBean.class.php');
require(REQUIRE_DIR . 'StudentPassGenBean.class.php');
require(REQUIRE_DIR . 'SubtaskBean.class.php');
require(REQUIRE_DIR . 'SubtaskDatesBean.class.php');
require(REQUIRE_DIR . 'TaskBean.class.php');
require(REQUIRE_DIR . 'TaskDatesBean.class.php');
require(REQUIRE_DIR . 'TaskSubtasksBean.class.php');
require(REQUIRE_DIR . 'URLsBean.class.php');
require(REQUIRE_DIR . 'UserBean.class.php');

/* Smarty plugins. */
//require ( REQUIRE_DIR . 'function.throt.php');

/* Parse command ... it shall have the form of <object>,<action>,<id>. */
$act = $_GET['act'];
/* Using array_pad() we make sure that the array has the expected length of three elements. */
list ($action, $object, $stringId) = array_pad(explode(',', $act), 3, '');

/* Test the parsed values for correctness */
$errorMsg = "";
switch ($object)
{
    case "article":
    case "board":
    case "evaluation":
    case "evltsk":
    case "exercise":
    case "exclist":
    case "extension":
    case "file":
    case "formassign":
    case "formsolution":
    case "lecture":
    case "import":
    case "labtask":
    case "lgrp":
    case "lgrpsec":
    case "lecturer":
    case "login":
    case "news":
    case "note":
    case "points":
    case "replacement":
    case "repbooking":
    case "schoolyear":
    case "section":
    case "solution":
    case "student":
    case "studentgroup":
    case "stupass":
    case "stuexe":
    case "stulec":
    case "subtask":
    case "subtaskdates":
    case "task":
    case "taskdates":
    case "tsksub":
    case "urls":
    case "user":
        break;
    case "home":
        $action = "show";
        break;
    default:
        $errorMsg .= "reference to non-existent object / tento objekt neexistuje<br>";
        $object = "error";
        $action = "error";
}

/* Administrator privileges needed for $action? */
$adminNeeded = false;
$lecturerNeeded = false;
$studentNeeded = false;
$loginNeeded = false;

switch ($action)
{
    case "realdelete":
        if ($object == "repbooking" || $object == "studentgroup")
        {
            $studentNeeded = true;
            break;
        }
    case "edit":
        if ($object == "formsolution" || $object == "repbooking" || $object == "studentgroup")
        {
            $studentNeeded = true;
            break;
        }
    case "save":
        if ($object == "solution" || $object == "formsolution" || $object == "repbooking")
        {
            $studentNeeded = true;
            break;
        }
    case "admin":
        if ($object != "comments" && $object != "order") $lecturerNeeded = true;
        break;
    case "delete":
        if ($object == "login") break;
        if ($object == "repbooking" || $object == "studentgroup")
        {
            $studentNeeded = true;
            break;
        }
        if ($object != "comments" && $object != "order") $lecturerNeeded = true;
    case "show":
        if ($object == "stulec") $lecturerNeeded = true;
        break;
    case "verify":
        if ($object == 'login' || $object == 'article' || $object == 'formsolution') break;
    default:
        $errorMsg .= "action not allowed / nepovolený typ akce<br>";
        $object = "error";
        $action = "error";
}

$id = (integer)$stringId;
if ($stringId != (string)$id or
    $id < 0 or
    ($id == 0 and $action != "edit" and $action != "save" and $object != "home" and
        ($object != 'section' and $action != 'admin'))
)
{
    $errorMsg .= "bad object identifier / nepovolený identifikátor objektu<br>";
    $object = "error";
    $action = "error";
}

/* Fetch / initialize session.
   In order to prevent mixing of sessions for different base URLS (live
   and testing web applications on the same machine, or different applications
   on the same machine), we will use a named session. The name of the
   session will be identical to the base directory of the application. */
session_name(BASE_DIR);
session_start();
/* Check for a sign that we have been connected to an existing session. In case that the session does not contain
   instance data of LectureBean as `lecture`, we will force a redirect to the home page. */
if (!isset($_SESSION['lecture']))
{
    /* New or expired session. Operations on 'section' will typically result in correct display of section. */
    if ($object != 'home' && $object != 'section' && $object != 'file' ||
        ($object == 'section' && $action != 'show') ||
        ($object == 'file' && $action != 'show')
    )
    {
        /* Expired session that will not be . */
        trigger_error("Session invalid, created a new session");
        /* Current URL */
        $current_base = dirname($_SERVER['SCRIPT_URI']);
        header("Location: $current_base");
    }
}

/* Headers. Do not send header for object 'file' and method 'show' as it is
   likely that the file is not an HTML document. Remember the output flag as we
   will use it later when constructing the Smarty instance to temporarily
   switch off the debugging output of the controller when serving a file
   object.
   @todo
   The same header is provided by the file/show method in case of an error
   message in HTML format. It would be nice to find a way how to circumvent
   this and have it in onle single place. */
$isPageOutput = ($object != "file" || ($action != 'show' && $object == "file")) ? true : false;
//$isPageOutput=true;
if ($isPageOutput)
{
    header("Content-Type: text/html; charset=utf-8");
}

/* Check for the forced switch to another schoolyear. */
if (!empty($_GET['schoolyear']))
{
    SessionDataBean::setSchoolYear($_GET['schoolyear']);
}
/* Initialise session defaults in case that the session data storage does not
   contain the variables we would need later. */
SessionDataBean::conditionalInit(SchoolYearBean::getSchoolYearStart());
if ($isPageOutput)
{
    echo '<!-- schoolyear ' . SchoolYearBean::getSchoolYearStart() . ' -->';
    echo "\n";
}

/* Binary flags for user roles */
$isAdmin = isRole(USR_ADMIN);
$isLecturer = isRole(USR_LECTURER);
$isStudent = isRole(USR_STUDENT);

/* Set the anonymous user role. */
$isAnonymous = false;
if (($isAdmin == false) && ($isLecturer == false) && ($isStudent == false))
{
    $isAnonymous = true;
    SessionDataBean::setUserRole(USR_ANONYMOUS);
}

/* Allow editing and saving only to logged in users. */
if ($adminNeeded)
{
    if ($isAdmin == false)
    {
        $errorMsg .= "<p>\n";
        $errorMsg .= "Pro přístup na tuto stránku potřebujete administrátorská práva.\n";
        $errorMsg .= "Přihlašte se jako uživatel s administrátorským opravněním a zkuste to prosím znovu.<br>\n";
        $errorMsg .= "</p>\n";
        $errorMsg .= "<p><em>\n";
        $errorMsg .= "(session is '" . dumpToString($_SESSION) . "')<br/>\n";
        $errorMsg .= "(isAdmin is '" . dumpToString($isAdmin) . "')<br/>\n";
        $errorMsg .= "(isLecturer is '" . dumpToString($isLecturer) . "')\n";
        $errorMsg .= "</em></p>\n";
        $object = "error";
        $action = "e_noadmn";
    }
}
else if ($lecturerNeeded)
{
    if (($isLecturer == false) && ($isAdmin == false))
    {
        $errorMsg .= "<p>\n";
        $errorMsg .= "Pro přístup na tuto stránku potřebujete alespoň práva učitele.\n";
        $errorMsg .= "Přihlašte se jako uživatel s tímto opravněním a akci opakujte.<br>\n";
        $errorMsg .= "</p>\n";
        $errorMsg .= "<p><em>\n";
        $errorMsg .= "(session is '" . dumpToString($_SESSION) . "')<br/>\n";
        $errorMsg .= "(isAdmin is '" . dumpToString($isAdmin) . "')<br/>\n";
        $errorMsg .= "(isLecturer is '" . dumpToString($isLecturer) . "')\n";
        $errorMsg .= "</em></p>\n";
        $object = "error";
        $action = "e_nolctr";
    }
}
else if ($studentNeeded)
{
    if (($isStudent == false) && ($isLecturer == false) && ($isAdmin == false))
    {
        $object = "error";
        $action = "e_nostdt";
    }
}
else if ($loginNeeded)
{
    if (isLoggedIn())
    {
        $errorMsg .= "<p>\n";
        $errorMsg .= "Pro přístup na tuto stránku musíte být přihlášeni do systému.\n";
        $errorMsg .= "Přihlašte se a zkuste to prosím znovu.<br>\n";
        $errorMsg .= "</p>\n";
        $errorMsg .= "<p><em>\n";
        $errorMsg .= "(session is '" . dumpToString($_SESSION) . "')<br/>\n";
        $errorMsg .= "(isAdmin is '" . dumpToString($isAdmin) . "')<br/>\n";
        $errorMsg .= "(isLecturer is '" . dumpToString($isLecturer) . "')\n";
        $errorMsg .= "</em></p>\n";
        $object = "error";
        $action = "error";
    }
}

/* Construct a Smarty instance. Configuration has been specified in config.php. */
$smarty = new CPPSmarty ($config, $isPageOutput);

/* Initialise database connection */
try
{
    $smlink = $smarty->dbOpen();
} catch (Exception $e)
{
    /* Make sure smlink has some value. */
    $smlink = NULL;
    /* And modify the displayed object and action. */
    $action = "exception";
    $object = "error";
    $smarty->assign('exceptionMsg', $e->getMessage());
}

/* Publish $errorMsg for the case of displaying an error header. */
$smarty->assign('errormsg', $errorMsg);

/* HTML Area header and calendar header and footer shall be loaded only when
   adding or editing data. */
if ($action == 'add' || $action == 'edit')
{
    $smarty->assign('htmlareaheader', "tinymce.header.tpl");
    $smarty->assign('calendarheader', "calendar.header.tpl");
    $smarty->assign('calendarfooter', "calendar.footer.tpl");
}
else
{
    $smarty->assign('htmlareaheader', "empty.tpl");
    $smarty->assign('calendarheader', "empty.tpl");
    $smarty->assign('calendarfooter', "empty.tpl");
}

/* -----------------------------------------------------------------------
   Main dispatcher.
   Creates an instance of an object that will handle the current action.
   Then it calls the appropriate action handler.
   ----------------------------------------------------------------------- */

$bean = NULL;
$haveValidAction = true; // This is modified only in case of SectionBean object. */

switch ($object)
{
    case "article":
        $bean = new ArticleBean ($id, $smarty, $action, $object);
        break;
    case "error":
        $smarty->assign('leftcolumn', "leftempty.tpl");
        break;
    case "evaluation":
        $bean = new EvaluationBean ($id, $smarty, $action, $object);
        break;
    case "evltsk":
        $bean = new EvaluationTasksBean ($id, $smarty, $action, $object);
        break;
    case "exercise":
        $bean = new ExerciseBean ($id, $smarty, $action, $object);
        break;
    case "exclist":
        $bean = new ExerciseListBean ($id, $smarty, $action, $object);
        break;
    case "extension":
        $bean = new DeadlineExtensionBean ($id, $smarty, $action, $object);
        break;
    case "file":
        $bean = new FileBean ($id, $smarty, $action, $object);
        break;
    case "formassign":
        $bean = new FormAssignmentBean ($id, $smarty, $action, $object);
        break;
    case "formsolution":
        $bean = new FormSolutionsBean ($id, $smarty, $action, $object);
        break;
    case "home":
        $bean = new SectionBean (0, $smarty, "show", "section");
        $haveValidAction = ($bean->prepareLectureHomePage($id) == RET_OK);
        break;
    case "import":
        $bean = new ImportBean ($id, $smarty, $action, $object);
        break;
    case "labtask":
        $bean = new LabtaskBean ($id, $smarty, $action, $object);
        break;
    case "lecture":
        $bean = new LectureBean ($id, $smarty, $action, $object);
        break;
    case "lecturer":
        $bean = new LecturerBean ($id, $smarty, $action, $object);
        break;
    case "lgrp":
        $bean = new LabtaskGroupBean ($id, $smarty, $action, $object);
        break;
    case "lgrpsec":
        $bean = new LabtaskGroupSectionBean ($id, $smarty, $action, $object);
        break;
    case "login":
        $bean = new LoginBean ($smarty, $action, $object);
        break;
    case "news":
        $bean = new NewsBean ($id, $smarty, $action, $object);
        break;
    case "note":
        $bean = new NoteBean ($id, $smarty, $action, $object);
        break;
    case "points":
        $bean = new PointsBean ($id, $smarty, $action, $object);
        break;
    case "repbooking":
        $bean = new ExerciseRepBookingBean ($id, $smarty, $action, $object);
        break;
    case "replacement":
        $bean = new ExerciseReplacementBean ($id, $smarty, $action, $object);
        break;
    case "schoolyear":
        $bean = new SchoolYearBean ($smarty, $action, $object);
        break;
    case "section":
        $bean = new SectionBean ($id, $smarty, $action, $object);
        break;
    case "solution":
        $bean = new SolutionBean ($id, $smarty, $action, $object);
        break;
    case "student":
        $bean = new StudentBean ($id, $smarty, $action, $object);
        break;
    case "studentgroup":
        $bean = new StudentGroupBean ($id, $smarty, $action, $object);
        break;
    case "stuexe":
        $bean = new StudentExerciseBean ($id, $smarty, $action, $object);
        break;
    case "stulec":
        $bean = new StudentLectureBean ($id, $smarty, $action, $object);
        break;
    case "stupass":
        $bean = new StudentPassGenBean ($id, $smarty, $action, $object);
        break;
    case "subtask":
        $bean = new SubtaskBean ($id, $smarty, $action, $object);
        break;
    case "subtaskdates":
        $bean = new SubtaskDatesBean ($id, $smarty, $action, $object);
        break;
    case "task":
        $bean = new TaskBean ($id, $smarty, $action, $object);
        break;
    case "taskdates":
        $bean = new TaskDatesBean ($id, $smarty, $action, $object);
        break;
    case "tsksub":
        $bean = new TaskSubtasksBean ($id, $smarty, $action, $object);
        break;
    case "urls":
        $bean = new URLsBean ($id, $smarty, $action, $object);
        break;
    case "user":
        $bean = new UserBean ($id, $smarty, $action, $object);
        break;
    default:
        /* Set $errormsg ... */
        $smarty->assign('errormsg', "No handler for '" . $object . "' has been set up.<br>");
        $object = "error";
        $action = "error";
}

/* Log the parameters of this controller invocation. This is necessary to easily handle claims of
   students that they accomplished something (booking, exercise submission) while the system
   shows otherwise. */
if (SessionDataBean::getUserRole() != USR_ANONYMOUS || $object == 'login')
{
    /* The rest of the log entry will be composed out of the contents of
       the globals $_GET, $_POST and $_SESSION. */
    $smarty->dbLog($timeStart, $object, $action);
}

/* This fills in all the data needed by appropriate templates into the
   Smarty instance passed in by the constructor. */
if ($bean)
{
    if ($haveValidAction)
    {
        /* Call an action handler. The handler may cause an exception, in that
              case we will change the action and object to generic type that allows
           displaying an error message. */
        try
        {
            $ret = $bean->actionHandler();
            $action = $bean->getAction();
            $object = $bean->getObject();
        } catch (Exception $e)
        {
            /* Override the object and action. */
            $action = "exception";
            $object = "error";
            $smarty->assign('exceptionMsg', $e->getMessage());
            $html = "<p>Exception occured: <tt>" . $e->getMessage() . "</tt></p>";
            logSystemError($html, $e->getTrace());
            /* Log also the information about the exception to the activity log of the current user. */
            if (SessionDataBean::getUserRole() != USR_ANONYMOUS)
            {
                /* The rest of the log entry will be composed out of the contents of
                   the globals $_GET, $_POST and $_SESSION. */
                $smarty->dbLogException($timeStart, $e->getMessage());
            }
        }
    }
    else
    {
        /* Non-standard action (error page, most probably) would cause the
           action handler to complain.
           @TODO this is probably not necessary at all, see the code for
           section->prepareHomePage() which is the only piece that modifies
           the action before calling actionHandler(). */
        $action = $bean->getAction();
        $object = $bean->getObject();
    }
}

/* Handle admin/section/0 which occurs in case when user tries to log in after
   session timeout. */
/*if ( $id == 0 and $action == 'admin' and $object == 'section' )
{
	$action = 'show';
	$object = 'home';	
}*/

/* Publish user login - this has to be done _after_ the call to LoginBean's
   action handler as this call fills in the proper data into _SESSION in
   case of login verifiaction. */
if (isLoggedIn())
{
    $smarty->assign('login', SessionDataBean::getUserLogin());
    $smarty->assign('uid', SessionDataBean::getUserId());

    /* And once again go through user roles. */
    $isAdmin = isRole(USR_ADMIN);
    $isLecturer = isRole(USR_LECTURER);
    $isStudent = isRole(USR_STUDENT);
    if (($isAdmin == false) && ($isLecturer == false) && ($isStudent == false))
    {
        $isAnonymous = true;
        SessionDataBean::setUserRole(USR_ANONYMOUS);
    }
    else
    {
        $isAnonymous = false;
    }
}
else
{
    $smarty->assign('login', "anonymní");

    /* Reset indicators for just logged-out users */
    $isAdmin = false;
    $isLecturer = false;
    $isStudent = false;
    $isAnonymous = true;
}

/* Publish user role */
$smarty->assign('isAdmin', $isAdmin);
$smarty->assign('isLecturer', $isLecturer);
$smarty->assign('isStudent', $isStudent);
$smarty->assign('isAnonymous', $isAnonymous);

/* Left-hand menu depends on 'action' */
switch ($action)
{
    case 'admin' :
    case 'verify' :
        /* In case of login / session timeout, we will have lecture.id == 0 after
           a succesful login. Any display of administrative menu will lead to
           complains from the ctrl.php about invalid identifier. We will therefore
           display a drop-down selection of possible lectures. */
    case 'edit' :
    case 'save' :
        $leftcolumn = ($isStudent) ? 'leftmenu.tpl' : 'leftadmin.tpl';
        break;
    case 'delete' :
        /* Action "delete.login" performs user logout from the system. It is
           pointless to show administrative menu afterwards. */
        if ($object != 'login')
        {
            $leftcolumn = ($isStudent) ? 'leftmenu.tpl' : 'leftadmin.tpl';
            break;
        }
    case 'show' :
        /* Get header items. Has to be done _after_ all modifications to
           sections have been accomplished in order to reflect possible
           'mtitle' changes. */

        /* Check that information about the last section id exists, assign
           a reasonable default value otherwise. */
        $lastSectionId = SessionDataBean::getLastSectionId();
        if ($lastSectionId == NULL)
        {
            /* Session timeout or whatever else. We do not have a valid
               lecture id as well, so let us set the lecture id to default
               as well. */
            $lastSectionId = 0;
            SessionDataBean::setLastSectionId($lastSectionId);
            SessionDataBean::setDefaultLecture();
        }

        /* Menu shows in fact a part of section hierarchy. Let's construct
           an appropriate section object first. */
        $menu = new SectionBean ($lastSectionId, $smarty, $object, $action);

        /* Now fetch the menu items that will form the menu. They will be
           stored as a smarty variable 'menuHierList'. The session variable
              holding lecture data has been initialised in the call to section->show
              handler. In case the session data is empty, no menu will be displayed. */
        $rootSection = SessionDataBean::getRootSection();
        if ($rootSection)
        {
            $menu->assignMenuHierarchy($rootSection);
            /* This is the template that will display menu data */
            $leftcolumn = 'leftmenu.tpl';
        }
        else
        {
            $leftcolumn = 'leftmenu.tpl';
        }
        break;
    default:
        /* Even in case that an error occured, do not erase the administrative
           menu for lecturers and administrators. */
        $leftcolumn = ($isAdmin || $isLecturer) ? 'leftmenu.tpl' : 'leftempty.tpl';
        break;
}
$smarty->assign('leftcolumn', $leftcolumn);

/* Show footer only for "normal" pages. */
$footer = ($action != "show") ? "empty.tpl" : "footer.tpl";
$smarty->assign('footer', $footer);

/* This will go into page main column.  */
$maincolumn = $object . "." . $action . ".tpl";
$smarty->assign('maincolumn', $maincolumn);
$maincolumntitle = $object . "." . $action . ".tit";
$smarty->assign('maincolumntitle', $maincolumntitle);

/* This is an approximate time of execution of this script. */
$time = sprintf("%7.4f", microtime(true) - $timeStart);
$smarty->assign('exectime', $time);

/* This is an approximate time of execution of this script. */
$currentTime = date('Y-m-d H:i:s');
$smarty->assign('currentTime', $currentTime);

/* Publish the public host name. */
$smarty->assign('HOST_NAME', HOST_NAME);

/* Detect secure HTTP. */
$smarty->assign('isHTTPS', !empty ($_SERVER['HTTPS']));

/* Tell Smarty about the active lecture. */
$smarty->assign('lecture', SessionDataBean::getLecture());

/* Tell Smarty about the active school year. */
$smarty->assign(
    'schoolyear',
    SessionDataBean::getSchoolYear() . '/' . (SessionDataBean::getSchoolYear() + 1));

/* Tell Smarty about the locale requested by the current lecture. */
$smarty->setLocale(SessionDataBean::getLectureLocale());

/* Display the page */
$smarty->display('main.tpl');

/* Close the dadtabase connection */
$smarty->dbClose($smlink);

if ($smarty->debug)
{
    /* This is an approximate time of execution of the whole page. */
    $time = microtime(true) - $timeStart;
    echo "<!-- Total " . $time . " sec -->\n";
    echo "<!-- SMARTY \n";
    print_r($smarty);
    echo "-->\n";
    echo "<!-- _GET\n";
    print_r($_GET);
    echo "-->\n";
    echo "<!-- _POST\n";
    print_r($_POST);
    echo "-->\n";
    echo "<!-- session (SID=" . session_id() . ")\n";
    print_r($_SESSION);
    echo "-->\n";
    echo "<!-- _SERVER\n";
    print_r($_SERVER);
    echo "-->\n";
}
?>
