<?php

class StudentLectureBean extends DatabaseBean
{
    const LIMIT_COUNT = 50;
    const E_INIT_FAILED = -1;
    const E_NO_TASKS = -2;
    const E_NO_SUBTASKS = -3;
    const E_NOT_STUDYING = -4;

    private $studentList;     // List of student ids of this lecture
    private $resType;         // Type of evaluation listing
    private $firstLetter;
    private $fromCount;
    private $student_id;      // Student that will be added to the lecture
    private $relation;
    private $sort;            // Sort type

    /* TODO: SB_SORT_BY_NAME is a global define, should be a constant. */
    function _setDefaults()
    {
        $this->studentList = array();
        $this->resType = SB_STUDENT_ANY; // Defined in StudentBean.class.php
        $this->firstLetter = '';
        $this->fromCount = 0;
        $this->sort = SB_SORT_BY_NAME;
    }

    /* Constructor */
    function __construct($id, &$smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, "stud_lec", $action, $object);
        /* Initialise new properties to their default values. */
        $this->_setDefaults();
    }

    /**
     * Append a single student to the list of lecture students in this school year.
     * @param $student_id
     */
    function dbAppendSingle($student_id)
    {
        /* Assign every student id from relation to this lecture. */
        DatabaseBean::dbQuery(
            "REPLACE stud_lec VALUES ("
            . $student_id . ","
            . $this->id . ","
            . $this->schoolyear . ","
            . "NULL)"
        );
    }

    /**
     * Append a list of student ids to the lecture students in this school year.
     */
    function dbAppend()
    {
        /* Loop over all student ids and store them into database. */
        foreach ($this->studentList as $student_id)
        {
            /* Assign every student id from relation to this lecture. */
            $this->dbAppendSingle($student_id);
        }
    }

    function dbReplace()
    {
        /* Delete all entries for the lecture and the year. */
        DatabaseBean::dbQuery(
            "DELETE FROM stud_lec WHERE lecture_id="
            . $this->id . " AND year=" . $this->schoolyear);
        /* And append the data into the cleared table. */
        $this->dbAppend();
    }

    /**
     * Append (possibly replace) list of students to the lecture.
     * @param $stlist
     * @param bool $replace
     */
    function setStudentList($stlist, $replace = false)
    {
        $this->studentList = $stlist;
        if ($replace)
        {
            $this->dbReplace();
        }
        else
        {
            $this->dbAppend();
        }
    }

    /* Assign POST variables to internal variables of this class and
       remove evil tags where applicable. We shall probably also remove
       evil attributes et cetera, but this will be done later if ever. */
    function processPostVars()
    {
        $this->relation = $_POST['sl_rel'];
    }

    function processGetVars()
    {
        assignGetIfExists($this->resType, $this->rs, 'restype');
        assignGetIfExists($this->firstLetter, $this->rs, 'first');
        assignGetIfExists($this->fromCount, $this->rs, 'from');
        assignGetIfExists($this->student_id, $this->rs, 'student_id');
        assignGetIfExists($this->sort, $this->rs, 'sort');
    }

    function getStudentListForLecture()
    {
        $rs = DatabaseBean::dbQuery(
            "SELECT student_id FROM stud_lec WHERE" .
            " lecture_id=" . $this->id . " AND" .
            " year=" . $this->schoolyear);
        // $this->dumpVar ( 'rs',  $rs );

        $studentList = array();
        if (isset ($rs))
        {
            foreach ($rs as $val)
            {
                $studentList[] = $val['student_id'];
            }
        }
        $this->dumpVar('studentList', $studentList);

        return $studentList;
    }

    /**
     * Verify that a student studies given lecture.
     * @param $student_id
     * @param $lecture_id
     * @param $school_year
     * @return bool
     */
    function studentIsListed($student_id, $lecture_id, $school_year)
    {
        $rs = DatabaseBean::dbQuery(
            "SELECT student_id FROM stud_lec " .
            "WHERE student_id=" . $student_id . " " .
            "AND lecture_id=" . $lecture_id . " " .
            "AND year=" . $school_year);

        return (count($rs) > 0);
    }

    /**
     * @param $grade
     * @param $student_id
     * @param $lecture_id
     * @param $school_year
     * @throws Exception
     */
    function studentAcceptsGrade($grade, $student_id, $lecture_id, $school_year)
    {
        if ($this->studentIsListed($student_id, $lecture_id, $school_year))
        {
            /* Replace the timestamp of the agreement.
               TODO: Check that the grade has not been confirmed yet. */
            DatabaseBean::dbQuery(
                "UPDATE stud_lec SET grade_confirmed=NOW() " .
                "WHERE student_id=$student_id AND lecture_id=$lecture_id AND year=$school_year"
            );
            $rs = DatabaseBean::dbQuery(
                "SELECT grade_confirmed FROM stud_lec " .
                "WHERE student_id=$student_id AND lecture_id=$lecture_id AND year=$school_year"
            );
            /* Send confirmation email */
            $this->sendConfirmationEmail($grade, $rs[0]['grade_confirmed']);
        }
        else
        {
            $message = "Cannot accept evaluation score: Student with ID $student_id does not study lecture $lecture_id in school year $school_year.";
            error_log($message);
            throw new Exception($message, self::E_NOT_STUDYING);
        }
    }

    /**
     * Send a confirmaion e-mail to student that they accepted the grade.
     * @param $grade
     * @param $confirmed_timestamp
     */
    function sendConfirmationEmail($grade, $confirmed_timestamp)
    {
        /* Send an e-mail to the user saying that the password has been changed. */

        $header = "From: " . SENDER_FULL . "\r\n";
        // $header  .= "To: <" . $this->email . ">\r\n";
        $header .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
        $header .= "Errors-To: " . ADMIN_FULL . "\r\n";
        $header .= "Reply-To: " . ADMIN_FULL . "\r\n";
        $header .= "X-Mailer: PHP";

        $userId = SessionDataBean::getUserId();
        $userLogin = SessionDataBean::getUserLogin();
        $userName = SessionDataBean::getUserFullName();
        $lecture_data = SessionDataBean::getLecture();

        $message = "Student $userName ($userLogin, id=$userId) potvrdil přijetí známky $grade\r\n";
        $message .= "z předmětu " . $lecture_data['title'] . ".\r\n\r\n";
        $message .= "Časové razítko transakce: ";
        $message .= $confirmed_timestamp;
        $message .= "\r\n";

        $message_title = '[' . $lecture_data['code'] . '] Student `' . $userLogin . '` přijal známku ' . $grade;
        $subject = "=?utf-8?B?" . base64_encode($message_title) . "?=";

        /* Now send the notification to the student ... */
        if (SEND_MAIL)
        {
            /* Mail the student */
            mail(SessionDataBean::getUserEmail(), $subject, $message, $header);

            /* Mail the lecturers. */
            $llb = new LectureLecturerBean(null, $this->_smarty, null, null);
            $lecturers = $llb->getLecturersForLecture();
            error_log('lecturers: ' . print_r($lecturers, true));
            foreach ($lecturers as $key => $val)
            {
                error_log('lecturer email: ' . $val['email']);
                mail($val['email'], $subject, $message, $header);
            }
        }

        /* ... and send a copy to the administrator. */
        mail(ADMIN_EMAIL, $subject, $message, $header);
    }


    /**
     * Prepare the list of students and their points for the lecture.
     * Throws an Exception with code self::E_INIT_FAILED in case of missing evaluation scheme.
     * Throws an Exception with code self::E_NO_SUBTASKS in case of missing subtasks.
     * This is very similar to prepareExerciseData() in ExerciseBean.
     * @throws Exception in case that the evaluation scheme does not exit or the subtask map is empty.
     */
    function prepareStudentLectureData()
    {
        /* Get the list of students for this exercise. The list will contain
           only student IDs. */
        $studentList = $this->getStudentListForLecture();

        /* Fetch the evaluation scheme from the database.
           @TODO@ Allow for more than one scheme.
           @TODO@ Allow for more than one lecture !!!!! */
        $evalBean = new EvaluationBean (0, $this->_smarty, "x", "x");

        /* This will initialise EvaluationBean with the most recent evaluation
           scheme for lecture given by $this->lecture_id. The function returns
           'true' if the bean has been initialised. */
        $ret = $evalBean->initialiseFor($this->id, $this->schoolyear);
        /* Check the initialisation status. */
        if (!$ret)
        {
            echo "<!-- EvaluationBean: initialisation failed -->\n";
            /* Nope, the id references a nonexistent evaluation scheme. */
            throw new Exception("The evaluation scheme for the given lecture and year does not exist.",
                self::E_INIT_FAILED);
        }

        /* Get the list of tasks for evaluation of this exercise. The list will
           contain only task IDs and we will have to fetch task and subtask
           information by ourselves later. */
        $taskList = $evalBean->getTaskList();

        /* Fetch a verbose list of tasks. */
        $taskBean = new TaskBean (0, $this->_smarty, "x", "x");

        /* This will both create a full list of tasks corresponding to the
           evaluation scheme and assign this list to the Smarty variable
           'taskList'. */
        $fullTaskList = $taskBean->assignFullTaskList($taskList);

        /* This will both create a full list of subtasks corresponding to the
           tasks of the chosen evaluation scheme and assign this list to the
           Smarty variable 'subtaskList'. */
        $tsBean = new TaskSubtasksBean (0, $this->_smarty, '', '');
        $subtaskMap = $tsBean->getSubtaskMapForTaskList($taskList, $evalBean->getEvalYear());
        $this->dumpVar('subtaskMap', $subtaskMap);
        if (empty ($subtaskMap))
        {
            throw new Exception("Subtask map for the current task list and evaluation year is empty.",
                self::E_NO_SUBTASKS);
        }
        $subtaskList = $tsBean->getSubtaskListFromSubtaskMap($subtaskMap);
        $this->dumpVar('subtaskList', $subtaskList);

        $subtaskBean = new SubtaskBean (0, $this->_smarty, "x", "x");
        $fullSubtaskList = $subtaskBean->assignFullSubtaskList($subtaskList);

        /* Initialise the returned value as empty. */
        $data = array(NULL, NULL);
        /* If there are any students in $studentList, get their points. */
        if (count($studentList) > 0)
        {
            $pointsBean = new PointsBean (0, $this->_smarty, '', '');
            $points = $pointsBean->getPoints($studentList, $subtaskList, $this->schoolyear);

            /* Generate a verbose list of students based on
               the ID list we got above. Combine this list with the points students
               achieved. */
            $studentBean = new StudentBean (0, $this->_smarty, "x", "x");
            $data = $studentBean->getStudentDataFromList(
                $studentList, $points, $evalBean, $subtaskMap,
                $fullSubtaskList, $fullTaskList,
                $this->resType, $this->sort,
                $this->id);
            $data[] = $fullSubtaskList;
            $data[] = $fullTaskList;
        }

        $this->dumpVar('prepared data', $data);

        return $data;
    }

    function createSQLFilter()
    {
        $where = "";
        if (!empty ($this->firstLetter))
        {
            $where .= 'surname LIKE "' . $this->dbEscape($this->firstLetter) . '%"';
        }
        if (!empty ($where)) $where = " WHERE " . $where;

        return $where;
    }

    function createSQLLimit()
    {
        return array('offset' => $this->fromCount, 'count' => self::LIMIT_COUNT);
    }

    /* -------------------------------------------------------------------
       HANDLER: SHOW
       ------------------------------------------------------------------- */
    function doShow()
    {
        /* Update internal parameters from data sent by GET method. */
        $this->processGetVars();

        /* Get lecture data */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, "x", "x");
        $lectureBean->assignSingle();

        try
        {
            $data = $this->prepareStudentLectureData();
            $this->assign('studentList', $data[0]);
            $this->assign('statData', $data[1]);
            $this->assign('restype', $this->resType);
        } catch (Exception $e)
        {
            switch ($e->getCode())
            {
                case self::E_INIT_FAILED:
                    $this->action = 'e_init';
                    break;
                case self::E_NO_SUBTASKS:
                    $this->action = 'e_subtasks';
                    break;
                default:
                    throw $e;
            }
        }
    }

    /* -------------------------------------------------------------------
       HANDLER: SAVE
       ------------------------------------------------------------------- */
    function doSave()
    {
        /* The handler is typically called with GET request specifying the
           student to add to the list of students of the lecture specified
           by `id`. */
        $this->processGetVars();
        /* Update all the records. */
        $this->dbAppendSingle($this->student_id);
        /* Fetch information about this student. */
        $studentBean = new StudentBean($this->student_id, $this->_smarty, NULL, NULL);
        $studentBean->assignSingle();
    }

    /* -------------------------------------------------------------------
       HANDLER: DELETE
       ------------------------------------------------------------------- */
    function doDelete()
    {
        /* Just fetch the data of the user to be deleted and ask for
           confirmation. */
        $this->dbQuerySingle();
        $this->_smarty->assign('user', $this->rs);
        /* Left column contains administrative menu */
        $this->_smarty->assign('leftcolumn', "leftadmin.tpl");
    }

    /* -------------------------------------------------------------------
       HANDLER: REAL DELETE
       ------------------------------------------------------------------- */
    function doRealDelete()
    {
        /* Delete the record */
        DatabaseBean::dbDeleteById();
        /* Deleting a section can occur only in admin mode. Now that we
           have deleted the data, we shall return to the admin view by
           calling the appropriate action handler. */
        $this->doAdmin();
    }

    /* -------------------------------------------------------------------
       HANDLER: ADMIN
       ------------------------------------------------------------------- */
    function doAdmin()
    {
        /* Process optional parameters passed as a part of GET request. */
        $this->processGetVars();

        /* Get a list of all students stored in the system, possibly filtered
           by first letter of the surname. */
        $studentBean = new StudentBean (NULL, $this->_smarty, NULL, NULL);
        $studentBean->assignStudentList($this->createSQLFilter(), $this->createSQLLimit());
    }

    /* -------------------------------------------------------------------
       HANDLER: EDIT
       ------------------------------------------------------------------- */
    function doEdit()
    {
    }
}

?>
