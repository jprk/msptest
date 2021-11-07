<?php

class AssignmentsBean extends DatabaseBean
{
    /* Invalid assignment id. */
    const ID_INVALID = -1;
    /* Assignment ID used for non-individual subtasks. */
    const ID_UNDEFINED = 0;

    var $student_id;
    var $subtask_id;
    var $assignment_id;
    var $file_id;

    /* Fill in reasonable defaults. */
    function _setDefaults()
    {
        $this->subtask_id = $this->rs['subtask_id'] = 0;
        $this->student_id = $this->rs['student_id'] = 0;
        $this->assignment_id = $this->rs['assignment_id'] = 0;
        $this->file_id = $this->rs['file_id'] = 0;
    }

    /* Constructor */
    function __construct($id, &$smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, "assignmnts", $action, $object);
    }

    /**
     * Delete all references to particular student and subtask.
     * @param $subtask_list array List of subtasks to delete (optional).
     */
    function dbDelete($subtask_list = null)
    {
        if (!is_null($subtask_list))
        {
            $db_list = arrayToDBString($subtask_list);
        }
        else
        {
            $db_list = strval($this->subtask_id);
        }

        $this->dbQuery(
            "DELETE FROM assignmnts WHERE student_id=$this->student_id AND subtask_id IN ($db_list) AND year=$this->schoolyear");
    }


    function dbReplace()
    {
        $this->dbDelete();
        $this->dbQuery(
            "REPLACE assignmnts VALUES ("
            . $this->student_id . ","
            . $this->subtask_id . ","
            . $this->schoolyear . ","
            . $this->assignment_id . ","
            . $this->file_id . ")"
        );
    }

    /**
     * Delete all assignments for the given student and subtask.
     * In theory, there should be only one valid assigment, but this method deletes all of them in case that
     * there are some extra pieces that were not detected before.
     * @param $student_id int Student identifier.
     * @param $subtask_list array Subtask identifier.
     */
    function deleteAssignments($student_id, $subtask_list)
    {
        /* Initialise internal variables used by `dbDelete()`. */
        $this->student_id = $student_id;

        /* Delete the records. */
        $this->dbDelete($subtask_list);
    }

    /**
     * Delete all assignments for the given student and subtask.
     * In theory, there should be only one valid assigment, but this method deletes all of them in case that
     * there are some extra pieces that were not detected before.
     * @param $student_id int Student identifier.
     * @param $subtask_id int Subtask identifier.
     */
    function deleteAssignment($student_id, $subtask_id)
    {
        /* Initialise internal variables used by `dbDelete()`. */
        $this->student_id = $student_id;
        $this->subtask_id = $subtask_id;

        /* Delete the record. */
        $this->dbDelete();
    }

    function setAssignment($studentId, $subtaskId, $assignmentId, $fileId)
    {
        /* Initialise internal variables. */
        $this->student_id = $studentId;
        $this->subtask_id = $subtaskId;
        $this->assignment_id = $assignmentId;
        $this->file_id = $fileId;

        /* Store the record. */
        $this->dbReplace();
    }

    function getSingle($studentId, $subtaskId)
    {
        $rs = DatabaseBean::dbQuery(
            "SELECT * FROM assignmnts WHERE subtask_id=" . $subtaskId .
            " AND student_id=" . $studentId . " AND year=" . $this->schoolyear);
        if (!empty ($rs)) $rs = $rs[0];
        return $rs;
    }

    /**
     * Get a list of current assignments for the given subtask id.
     * Returns a list of assignments that have been generated for
     * students in this school year.
     */
    function getAssignmentList($subtaskId)
    {
        $rs = $this->dbQuery(
            'SELECT * FROM assignmnts ' .
            'WHERE subtask_id=' . $subtaskId . ' ' .
            'AND year=' . $this->schoolyear
        );
        return $rs;
    }

    /**
     * Get the ID of an assignment for particular student and subtask.
     * Returns ID_INVALID in case that the assignment ID cannot be found and
     * ID_UNDEFINED for tasks that do not have any individual assignments.
     * @throws Exception
     */
    function getAssignmentId($studentId, $subtaskId, $subtaskType)
    {
        $assignId = self::ID_INVALID;
        /* First check the subtask type. */
        if (SubtaskBean::noAssignmentIdRequired($subtaskType))
        {
            /* This is a subtask without individual assignments, i.e. it has no assignment id. */
            $assignId = self::ID_UNDEFINED;
        }
        else
        {
            /* This subtask should have a list of assignment ids available. */
            $rs = $this->getSingle($studentId, $subtaskId);
            $this->dumpVar('getAssignmentId::rs', $rs);
            if (!empty ($rs))
            {
                if ($rs['assignmnt_id'] <= self::ID_UNDEFINED)
                {
                    throw new Exception('Data inconsistent: `assignment_id` is zero or lower!');
                }
                $assignId = $rs['assignmnt_id'];
            }
            else
            {
                throw new Exception('Data inconsistent: compulsory `assignment_id` is not defined for this subtask!');
            }
        }

        return $assignId;
    }

    /**
     * Get the number of assignments generated for evert subtask from
     * the list. The subtask list is an array of records indexed by 'id'
     * of the subtask.
     */
    function getNumberOfAssignments($subtaskList)
    {
        /* First we have to convert the subtask list into an SQL
           sequence of ids. */
        $ids = array2ToDBString($subtaskList, 'id');
        /* Now query the counts. */
        $rs = $this->dbQuery(
            "SELECT subtask_id,COUNT(*) AS count FROM assignmnts " .
            "WHERE subtask_id IN (" . $ids . ") " .
            "AND year=" . $this->schoolyear . " " .
            "GROUP BY subtask_id");
        /* Default result is an empty array. */
        $ret = array();
        /* Now check the query resultset. */
        if (!empty ($rs))
        {
            /* We have some results. */
            foreach ($rs as $key => $val)
            {
                $ret[$val['subtask_id']] = $val['count'];
            }
        }
        $this->dumpVar('result', $rs);
        $this->dumpVar('return', $ret);
        return $ret;
    }

    function assignSingle()
    {
        /* Our `id` is the subtask id. The user id of the student is stored in
           the session data block. */
        $studentId = SessionDataBean::getUserId();
        $rs = $this->getSingle($studentId, $this->id);
        $this->_smarty->assign('assignment', $rs);
        return $rs;
    }

    function setClassifiedAssignments($studentId, $subtaskList)
    {
        if (!empty ($subtaskList))
        {
            /* This instance will be used to access solutions submitted by the student. */
            $fsBean = new FormSolutionsBean (null, $this->_smarty, null, null);

            /* Query the group mode flag of the lecture. The flag influences the computation of `haveSolution`
               flag in the code below. */
            if (SessionDataBean::getLectureGroupType() != StudentGroupBean::GRPTYPE_NONE)
            {
                $sgb = new StudentGroupBean(null, $this->_smarty, null, null);
                $students = $sgb->getGroupStudentsOfStudent($studentId);
            }
            else
            {
                $students = StudentGroupBean::getDefaultGroupStudents($studentId);
            }

            foreach ($subtaskList as $key => $val)
            {
                /* This is the identifier of the subtask. */
                $subtaskId = $val['id'];
                $subtaskType = $val['type'];
                /* Some subtask types do not have assignments generated (these
                   are subtasks corresponding to semestral projects, public
                   assignments presented by lecturers and so on. These
                   assignments have an implicit assignmentId equal to ID_UNDEFINED.
                   
                   The following method finds the appropriate assignment id for
                   the combination of student id, subtask id and type. It returns
                   ID_INVALID in case that there has been no assignment generated
                   for this student and subtask id. */
                $assignmntId = $this->getAssignmentId($studentId, $subtaskId, $subtaskType);

                /* If the assignmentId is not valid, skip checking for a submitted solution of individual
                   or group assignments -- the assignment has not been generated yet. However, do the
                   check in case of a subtask that does not define assignmentIds. */
                if ($assignmntId >= self::ID_UNDEFINED)
                {
                    /* Set up FormSolutionBean to this subtask. */
                    $fsBean->id = $subtaskId;
                    /* Query the presence of a solution submitted by this student. */
                    $subtaskList[$key]['haveSolution'] =
                        $fsBean->haveSolution($subtaskId, $students, $assignmntId);
                }
                else
                {
                    /* But make sure that the entry exists, otherwise the templating engine will complain
                       about undefined index ... */
                    $subtaskList[$key]['haveSolution'] = null;
                }
            }
        }

        $this->dumpVar('setClassifiedAssignments', $subtaskList);
        return $subtaskList;
    }

    /* -------------------------------------------------------------------
	   HANDLER: SHOW
	   ------------------------------------------------------------------- */
    function doShow()
    {
        /* Get information about this subtask. */
        $subtaskBean = new SubtaskBean ($this->id, $this->_smarty, null, null);
        $subtaskBean->assignSingle();
        /* Get information about the assignment for this particular student. */
    }

    /* -------------------------------------------------------------------
       HANDLER: ADMIN
       ------------------------------------------------------------------- */
    function doAdmin()
    {
        /* Get a lecture that this subtask is related to. */
        $lectureBean = new LectureBean (1, $this->_smarty, "", "");
        $lectureBean->assignSingle();
        /* Get a list of subtask types. */
        $subtaskBean = new SubtaskBean (0, $this->_smarty, "", "");
        $subtaskList = $subtaskBean->getForLecture(1, array(TaskBean::TT_WEEKLY_FORM, TaskBean::TT_WEEKLY_SIMU));
        /* Add count and publish it. */
        $subtaskList = $this->updateSubtaskList($subtaskList);
        $this->_smarty->assign('subtaskList', $subtaskList);
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
        /* There is no edit for this type of object. User may just import
           another set of subtasks. */
        /* Get a lecture that this subtask is related to. */
        $lectureId = SessionDataBean::getLectureId();
        $lectureBean = new LectureBean ($lectureId, $this->_smarty, "", "");
        $lectureBean->assignSingle();
        /* Get a list of subtask types. */
        $subtaskBean = new SubtaskBean ($this->id, $this->_smarty, "", "");
        $subtaskBean->assignSingle();
    }

    /* -------------------------------------------------------------------
       HANDLER: SAVE
       ------------------------------------------------------------------- */
    function doSave()
    {
        /* Assign POST variables to internal variables of this class and
           remove evil tags where applicable. */
        $this->processPostVars();
        /* Check the uploaded file. */
        if (is_uploaded_file($_FILES['assignfile']['tmp_name']))
        {
            /* Upload ok, open it. */
            $handle = @fopen($_FILES['assignfile']['tmp_name'], "r");
            if ($handle)
            {
                /* Can be opened, it shall be a CSV, so parse the lines. */
                while (!feof($handle))
                {
                    $buffer = fgets($handle, 4096);
                    $trimmed = trim($buffer);
                    /* Ignore empty lines. */
                    if (empty ($trimmed)) continue;
                    $la = explode(";", $trimmed);
                    echo "\n<!-- la=";
                    print_r($la);
                    echo " -->";

                    /* The record has to have 8 elements. Skip it otherwise. */
                    if (count($la) != 8) continue;

                    $this->assignment_id = trim($la[0], " \t\n\r\"");
                    $this->part = trim($la[1], " \t\n\r\"");
                    $this->a = trim($la[2], " \t\n\r\"");
                    $this->b = trim($la[3], " \t\n\r\"");
                    $this->c = trim($la[4], " \t\n\r\"");
                    $this->d = trim($la[5], " \t\n\r\"");
                    $this->e = trim($la[6], " \t\n\r\"");
                    $this->f = trim($la[7], " \t\n\r\"");
                    $this->count = 0;

                    $this->dbReplace();
                }

                fclose($handle);
            }
            else
            {
                /* Cannot open the file for reading. */
                $this->action = "err01";
            }
        }
        else
        {
            /* Possible file upload attack. */
            $this->action = "err02";
        }
    }

    /* -------------------------------------------------------------------
       HANDLER: DELETE
       ------------------------------------------------------------------- */
    function doDelete()
    {
        $this->assignSingle();

        /* Get a lecture that this subtask is related to. */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, "", "");
        $lectureBean->assignSingle();
    }

    /* -------------------------------------------------------------------
       HANDLER: REAL DELETE
       ------------------------------------------------------------------- */
    function doRealDelete()
    {
        $this->assignSingle();
        /* Delete the record */
        DatabaseBean::dbDeleteById();

        /* Get a lecture that this subtask is related to. */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, "", "");
        $lectureBean->assignSingle();
    }
}

?>
