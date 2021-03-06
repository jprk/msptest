<?php

class ExerciseBean extends DatabaseBean
{
    var $day;
    var $from;
    var $to;
    var $room;
    var $lecture_id;
    /** @var int Lecturer/tutor id. Not used anymore but kept for compatibility reasons. */
    private $lecturer_id;
    /** @var array Tutor identifiers. */
    private $tutor_ids;
    /** @var array Tutor records. */
    private $tutors;
    /** @var int Student group that has this exercise in their schedule. */
    private $groupno;
    /** @var ExerciseTutorsBean Instance of ExerciseTutorBean. */
    private $extutBean;
    private $displayNames;

    /**
     * @param $week_str
     * @param $day_str
     * @return int
     * @throws Exception
     */
    function make_day($week_str, $day_str)
    {
        $day_str = iconv("windows-1250", "utf-8", trim($day_str, " \t\n\r\"\xa0"));

        $this->dumpVar('week_str', $week_str);
        $this->dumpVar('day_str', $day_str);

        $week_str = strtolower($week_str);
        $day_str = mb_strtolower($day_str, "utf-8");

        if ( empty($week_str) || $week_str == '-' )
        {
            $day_offset = 0;
        }
        elseif ( $week_str == 's' || $week_str == 'e' )
        {
            /* Even week */
            $day_offset = 10;
        }
        elseif ( $week_str == 'l' || $week_str == 'o' )
        {
            /* Odd week */
            $day_offset = 20;
        }
        else
        {
            throw new Exception("incorrect week string '$week_str'");
        }

        $days = array(
            'po' => 1,
            'pondeli' => 1,
            'pondělí' => 1,
            'mo' => 1,
            'monday' => 1,
            //
            'ut' => 2,
            'út' => 2,
            'utery' => 2,
            'úterý' => 2,
            'tue' => 2,
            'tuesday' => 2,
            //
            'st' => 3,
            'streda' => 3,
            'středa' => 3,
            'wed' => 3,
            'wednesday' => 3,
            //
            'c' => 4,
            'č' => 4,
            'ct' => 4,
            'čt' => 4,
            'ctvrtek' => 4,
            'čtvrtek' => 4,
            'thu' => 4,
            'thursday' => 4,
            //
            'pa' => 5,
            'pá' => 5,
            'patek' => 5,
            'pátek' => 5,
            'fri' => 5,
            'friday' => 5
            );
        if ( ! array_key_exists($day_str, $days))
        {
            throw new Exception("invalid day string '$day_str'");
        }

        return $days[$day_str] + $day_offset;
    }

    function _setDefaults()
    {
        $this->day = 0;
        $this->from = 0;
        $this->to = 0;
        $this->room = "";
        $this->lecture_id = SessionDataBean::getLectureId();
        $this->lecturer_id = 0;
        $this->groupno = 0;
        $this->tutor_ids = array();
        $this->tutors = array();
        $this->_update_rs();
    }

    /* Constructor */
    function __construct($id, &$smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, "exercise", $action, $object);
        $this->extutBean = new ExerciseTutorsBean(null, $this->_smarty, null, null);
        /* And initialise new object properties. */
        $this->_setDefaults();
    }

    function dbReplace()
    {
        $args = [
            'id' => $this->id,
            'day' => $this->day,
            'from' => $this->from,
            'to' => $this->to,
            'room' => $this->room,
            'lecture_id' => $this->lecture_id,
            'lecturer' => 0,
            'year' => $this->schoolyear
        ];
        dibi::query('REPLACE `exercise`', $args);

        $this->updateId();
        $this->extutBean->setTutorsIdsForExercise($this->tutor_ids, $this->id);
    }

    /**
     * @param int $alt_id
     * @throws Exception
     */
    function dbQuerySingle($alt_id = 0)
    {
        DatabaseBean::dbQuerySingle($alt_id);

        $this->day = $this->rs['day'] = numToDay($this->rs['day']);
        $this->from = $this->rs['from'];
        $this->to = $this->rs['to'];
        $this->room = $this->rs['room'];
        $this->groupno = $this->rs['groupno'];
        $this->lecture_id = $this->rs['lecture_id'];
        $this->lecturer_id = $this->rs['lecturer_id'];
        $this->schoolyear = $this->rs['schoolyear'] = $this->rs['year'];

        $this->tutor_ids = $this->rs['tutor_ids'] = $this->extutBean->getTutorsIdsForExercise($this->id);
    }

    /* Assign POST variables to internal variables of this class and
       remove evil tags where applicable. We shall probably also remove
       evil attributes et cetera, but this will be done later if ever. */
    function processPostVars()
    {
        $this->id = $_POST['id'];
        $this->day = trimStrip($_POST['day']);
        $this->from = trimStrip($_POST['from']);
        $this->to = trimStrip($_POST['to']);
        $this->room = trimStrip($_POST['room']);
        $this->groupno = trimStrip($_POST['groupno']);

        /* Array of tutor ids does not need string trimming. */
        $this->tutor_ids = $_POST['tutor_ids']; /**> @var array */
    }

    /**
     * Process parameters supplied as GET part of the request.
     */
    function processGetVars()
    {
        assignGetIfExists($this->displayNames, $this->rs, 'displaynames');
    }

    function _dbQueryFullList($where)
    {
        return DatabaseBean::dbQuery(
            "SELECT * FROM exercise"
            . $where
            . " ORDER BY `day`, `from`"
        );
    }

    function _getFullList($where = '')
    {
        $rs = $this->_dbQueryFullList($where);
        if (isset ($rs))
        {
            foreach ($rs as $key => $val)
            {
                $rs[$key]['day'] = numToDay($val['day']);
            }
        }
        return $rs;
    }

    function getSelectMap($lectureId = 0, $schoolYear = 0)
    {
        $where = $this->_lectureIdToWhereClause($lectureId, $schoolYear);
        $rs = $this->_dbQueryFullList($where);

        $exerciseMap = array();
        if ($lectureId == 0)
        {
            $exerciseMap[0] = "Vyberte ze seznamu ...";
        }
        if (isset ($rs))
        {
            foreach ($rs as $key => $val)
            {
                $eId = $val['id'];
                $room = $val['room'];
                $day = numToDay($val['day']);
                $timespan = substr($val['from'], 0, -3) . "-" . substr($val['to'], 0, -3);
                $exerciseMap[$eId] = $room . ", " . $day['name'] . ", " . $timespan;
            }
        }


        return $exerciseMap;
    }

    function assignSelectMap($lectureId = 0, $schoolYear = 0)
    {
        $exerciseMap = $this->getSelectMap($lectureId, $schoolYear);
        $this->_smarty->assign('exerciseSelect', $exerciseMap);
        return $exerciseMap;
    }

    function prepareExerciseData($sortType)
    {
        /* Check if there are some data. */
        if (empty ($this->rs['id']))
        {
            /* Nope, the id references a nonexistent exercise. */
            $this->action = "err_01x";
            return;
        }

        /* Now we know that the exercise record really exists, but we have
           to make ourselves sure that the exercise balongs to the currently
           active school year. */
        if ($this->rs['year'] != $this->schoolyear)
        {
            /* Nope, wrong schoolyear. */
            $this->action = "e_year";
            return;
        }

        /* The function above sets $this->rs to values that shall be
           displayed. By assigning $this->rs to Smarty variable 'exercise'
           we can fill the values of $this->rs into a template. */
        $this->_smarty->assign('exercise', $this->rs);

        /* Get the lecture data. */
        $lectureBean = new LectureBean ($this->lecture_id, $this->_smarty, "x", "x");
        $lectureBean->assignSingle();

        /* Get the lecturer data. */
        $lecturerBean = new LecturerBean ($this->lecturer_id, $this->_smarty, "x", "x");
        $lecturerBean->assignSingle();

        /* Get the list of students for this exercise. The list will contain
           only student IDs. */
        $studentExerciseBean = new StudentExerciseBean (0, $this->_smarty, "x", "x");
        $studentList = $studentExerciseBean->getStudentListForExercise($this->id);

        /* Fetch the evaluation scheme from the database.
           @TODO@ Allow for more than one scheme.
           @TODO@ Allow for more than one lecture !!!!! */
        $evaluationBean = new EvaluationBean (0, $this->_smarty, "x", "x");

        /* This will initialise EvaluationBean with the most recent evaluation
           scheme for lecture given by $this->lecture_id. The function returns
           'true' if the bean has been initialised. */
        $ret = $evaluationBean->initialiseFor($this->lecture_id, $this->schoolyear);

        /* Check the initialisation status. */
        if (!$ret)
        {
            /* Nope, the id references a nonexistent evaluation. */
            $this->action = "e_inval";
            return;
        }

        /* Get the list of tasks for evaluation of this exercise. The list will contain
           only task IDs and we will have to fetch task and subtask information
           by ourselves later. */
        $taskList = $evaluationBean->getTaskList();

        /* Fetch a verbose list of tasks. */
        $taskBean = new TaskBean (0, $this->_smarty, "x", "x");

        /* This will both create a full list of tasks corresponding to the
           evaluation scheme and assing this list to the Smarty variable
           'taskList'. */
        $fullTaskList = $taskBean->assignFullTaskList($taskList);

        /* Fetch a verbose list of subtasks. */
        $subtaskBean = new SubtaskBean (0, $this->_smarty, "x", "x");
        /* This will both create a full list of subtasks corresponding to the
           tasks of the chosen evaluation scheme and assign this list to the
           Smarty variable 'subtaskList'. */
        $tsBean = new TaskSubtasksBean (0, $this->_smarty, '', '');
        $subtaskMap = $tsBean->getSubtaskMapForTaskList($taskList, $this->schoolyear);
        $subtaskList = $tsBean->getSubtaskListFromSubtaskMap($subtaskMap);

        $subtaskBean = new SubtaskBean (0, $this->_smarty, "x", "x");
        $fullSubtaskList = $subtaskBean->assignFullSubtaskList($subtaskList);

        /* If there are any students in $studentList, get their points. */
        if (count($studentList) > 0)
        {
            $pointsBean = new PointsBean (0, $this->_smarty, '', '');
            $points = $pointsBean->getPoints(
                $studentList, $subtaskList, $this->schoolyear);

            /* Generate a verbose list of students based on
               the ID list we got above. Combine this list with the points students
               achieved. */
            $studentBean = new StudentBean (0, $this->_smarty, "x", "x");
            $studentBean->assignStudentDataFromList(
                $studentList, $points, $evaluationBean, $subtaskMap,
                $fullSubtaskList, $fullTaskList,
                SB_STUDENT_ANY, $sortType,
                $this->lecture_id);
        }
    }

    /**
     * Return an array of lecturer ids for exercises of the given
     * lecture.
     */
    function getExerciseLecturersForLecture($lectureId = 0)
    {
        return $this->dbQuery(
            "SELECT lecturer_id FROM exercise " .
            "WHERE lecture_id=" . $lectureId . " " .
            "GROUP BY lecturer_id"
        );
    }

    /**
     * Return a list of exercises for the given lecture id.
     */
    function getExercisesForLecture($lectureId = 0, $schoolYear = 0)
    {
        $where = $this->_lectureIdToWhereClause($lectureId, $schoolYear);
        return $this->_getFullList($where);
    }

    function fetchTutors()
    {
        /* The data contains only tutor ids by default. Extend the information with full names. */
        $lecturerBean = new LecturerBean(null, $this->_smarty, null, null);
        $fullTutors = $lecturerBean->getLecturersById($this->tutor_ids);
        $this->tutors = $this->rs['tutors'] = $fullTutors;
    }

    function getFull($lectureId = 0, $schoolYear = 0)
    {
        /* Get the list of exercises for the lecture. */
        $rs = $this->getExercisesForLecture($lectureId, $schoolYear);

        /* Get the old-style lecturer map. In new-style records the lecturer ID is equal to zero. */
        $lecturerBean = new LecturerBean ($this->lecturer_id, $this->_smarty, "x", "x");
        $lecturerMap = $lecturerBean->dbQueryLecturerMap();

        if (isset ($rs))
        {
            foreach ($rs as $key => $val)
            {
                $lid = $val['lecturer_id'];
                /* Value of $lid will be zero for new-style records. */
                if ($lid > 0) $rs[$key]['lecturer'] = $lecturerMap[$lid];
            }
        }

        /* Add the new-style tutor info (i.e. multiple lecturers for an exercise). */
        $tutorsBean = new ExerciseTutorsBean(null, $this->_smarty, null, null);
        $rs = $tutorsBean->addToExercises($rs);

        return $rs;
    }

    function assignFull($lectureId = 0, $schoolYear = 0)
    {
        $rs = $this->getFull($lectureId, $schoolYear);
        $this->_smarty->assign('exerciseList', $rs);
        return $rs;
    }

    function assignSingle()
    {
        /* Just fetch the data of the user to be deleted and ask for
           confirmation. */
        $this->dbQuerySingle();
        $this->fetchTutors();
        $this->assign('exercise', $this->rs);

        /* Get the information about the lecture we are listing exercises
           for ... */
        $lectureBean = new LectureBean ($this->lecture_id, $this->_smarty, "x", "x");
        $lectureBean->assignSingle();

        /* Get the lecturer data. */
        $lecturerBean = new LecturerBean ($this->lecturer_id, $this->_smarty, "x", "x");
        $lecturerBean->assignSingle();
    }

    /* -------------------------------------------------------------------
       HANDLER: STUDENT LIST
       ------------------------------------------------------------------- */
    function doStudentList($sort)
    {
        $this->prepareExerciseData($sort);
    }

    /* -------------------------------------------------------------------
       HANDLER: SHOW
       ------------------------------------------------------------------- */
    function doShow()
    {
        /* Query data of this exercise. */
        $this->dbQuerySingle();
        $this->fetchTutors();

        /* Check if there were some parameters passed as variables. */
        $this->processGetVars();

        /* Default sort type is by encoded used ids. */
        $sortType = SB_SORT_BY_ID;

        /* But in case that we shall display true names of students, we
           shall also sort by surname and not by the id. */
        if ($this->displayNames)
        {
            /* Fetch the information about current role of the user. */
            $role = SessionDataBean::getUserRole();
            /* Names can be displayed only to users with role USR_LECTURER
               and above. */
            if (UserBean::isRoleAtLeast($role, USR_LECTURER))
            {
                $sortType = SB_SORT_BY_NAME;
            }
            else
            {
                /* In case that the user does not have the permission
                   to display the list of student names, we will display
                   the normal list. For this, however, we will have to
                   reset the displayNames property. */
                $this->displayNames = false;
                $this->rs['displaynames'] = false;
            }
        }

        /* Process the queried data. */
        $this->prepareExerciseData($sortType);

        /* Get all active news for this exercise. That is, ignore all news
           for the lecture (these will be displayed somewhere else), get
           all active lecturer news for this lecturer, get all active news
           entered especially for this exercise, and get all active news
           for exercises to the lecture. */
        $newsBean = new NewsBean (0, $this->_smarty, "x", "x");
        $newsBean->assignNewsForTypes(0, $this->lecturer_id, $this->id, $this->lecture_id);
    }

    /** -------------------------------------------------------------------
       HANDLER: SAVE
       -------------------------------------------------------------------
     * @throws Exception for file upload
     */
    function doSave()
    {
        /* Check for file upload. */
        $this->dumpVar('FILES', $_FILES);
        if (isset ($_FILES['csv_exercises']))
        {
            $file_data = $_FILES['csv_exercises'];
            if (is_uploaded_file($file_data['tmp_name']))
            {
                $handle = @fopen($file_data['tmp_name'], "r");
                if ($handle)
                {
                    /* We have to skip the first line of the imported CSV file - it contains header information. */
                    $skipHeader = true;
                    /* First row of the resulting list. */
                    $row = 1;
                    /* And loop while we have something to chew on ... */
                    while (!feof($handle))
                    {
                        /* Read a line of text from the submitted file. */
                        $buffer = fgets($handle, 4096);
                        /* The file contains sometimes also form feed character (^L, 0x0c) which shall be
                           removed as well. */
                        $trimmed = trim($buffer, " \t\n\r\0\x0b\x0c\xa0");
                        /* The file may also contain some empty lines, and trimming the form feed will
                           generate another empty line. */
                        if (empty ($trimmed))
                        {
                            /* Skip empty lines. */
                            continue;
                        }
                        if ($skipHeader)
                        {
                            /* Skip header row. */
                            $skipHeader = false;
                            continue;
                        }
                        /* The line contains several fields separated by semicolon. */
                        $la = explode(";", $trimmed);
                        self::dumpVar('la', $la);
                        /* Stucture is as follows:
                            0 ... group number, integer
                            1 ... week type: empty or -/S or E/L or O
                            2 ... day (Pondělí-Neděle)
                            3 ... from
                            4 ... to
                            5 ... room no
                        */
                        if ( empty($la[1]) || empty($la[2]) || empty($la[3]) || empty($la[4]))
                        {
                            continue;
                        }
                        $this->id = 0;
                        $this->groupno = intval($la[0]);
                        $this->day = self::make_day($la[1],$la[2]);
                        $this->from = $la[3];
                        $this->to = $la[4];
                        $this->room = $la[5];
                        /* Store the record into database */
                        $this->dbReplace();
                    }
                }
            }
            else
            {
                throw new Exception('possible file upload attack when importing exercises');
            }
        }
        else
        {
        /* Assign POST variables to internal variables of this class and
           remove evil tags where applicable. */
        $this->processPostVars();
        /* Update the record, but do not update the password. */
        $this->dbReplace();
        /* Saving can occur only in admin mode. Now that we have saved the
           data, return to the admin view by calling the appropriate action
           handler. Admin mode expects to have the value of $this->id set
           to lecture_id.
        */
        }
        $this->id = $this->lecture_id;
        $this->doAdmin();
    }

    /* -------------------------------------------------------------------
       HANDLER: DELETE
       ------------------------------------------------------------------- */
    function doDelete()
    {
        $this->assignSingle();
    }

    /* -------------------------------------------------------------------
       HANDLER: REAL DELETE
       ------------------------------------------------------------------- */
    function doRealDelete()
    {
        $this->assignSingle();
        /* Delete the record */
        DatabaseBean::dbDeleteById();
    }

    /* -------------------------------------------------------------------
       HANDLER: ADMIN
       ------------------------------------------------------------------- */
    function doAdmin()
    {
        /* Get the information about the lecture we are listing exercises
           for ... */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, null, null);
        $lectureBean->assignSingle();

        /* Get the list of all exercises for the given lecture id and the
           current school year. */
        $this->assignFull($this->id, $this->schoolyear);

        /* It could have been that doAdmin() has been called from another
           handler. Change the action to "admin" so that ctrl.php will
           know that it shall display the scriptlet for section.admin */
        $this->action = "admin";
    }

    /* -------------------------------------------------------------------
       HANDLER: EDIT
       ------------------------------------------------------------------- */
    function doEdit()
    {
        /* If id == 0, we shall create a new record. */
        if ($this->id)
        {
            /* Query data of this exercise. */
            $this->dbQuerySingle();
        }
        else
        {
            /* Initialize default values. */
            $this->_setDefaults();
        }

        /* Both above functions set $this->rs to values that shall be
           displayed. By assigning $this->rs to Smarty variable 'user'
           we can fill the values of $this->rs into a template. */
        $this->_smarty->assign('exercise', $this->rs);

        /* Get a list of lectures. */
        $lectureBean = new LectureBean (0, $this->_smarty, "", "");
        $lectureBean->assignSelectMap();

        /* Get a list of lecturers assigned to this lecture in this schoolyear. */
        $leleBean = new LectureLecturerBean(null, $this->_smarty, null, null);
        $leleBean->assignSelectMap();

        /* Get a map of schoolyears. */
        //$this->assignYearMap ();
    }
}

?>
