 <?php

class StudentExerciseBean extends DatabaseBean
{
    var $relation;

    function _setDefaults()
    {
        $this->relation = array();
    }

    /* Constructor */
    function __construct($id, &$smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, "stud_exc", $action, $object);
        /* Initialise new properties to their default values. */
        $this->_setDefaults();
    }

    function dbReplace()
    {
        /* First let's find out which exercise ids have been defined for this
           school year and the active lecture. */
        $eb = new ExerciseBean (0, $this->_smarty, NULL, NULL);
        $ers = $eb->getExercisesForLecture($this->id, $this->schoolyear);
        /* Now we have to transform the result into a string that can be used
           as a parameter of "IN" clause. */
        $elst = array2ToDBString($ers, 'id');

        foreach ($this->relation as $student_id => $exercise_id)
        {
            /* Remove all possible references to previous exercises. */
            $this->dbQuery(
                "DELETE FROM stud_exc WHERE student_id=$student_id AND exercise_id IN ($elst)");
            $this->dbQuery(
                "REPLACE stud_exc VALUES ($student_id,$exercise_id)");
        }
    }

    /* Assign POST variables to internal variables of this class and
       remove evil tags where applicable. We shall probably also remove
       evil attributes et cetera, but this will be done later if ever. */
    function processPostVars()
    {
        $this->relation = $_POST['se_rel'];
    }

    /**
     * Return a list of students attending given exercises.
     * Students that are not officially assigned to any of the extersises in
     * the $exerciseList will not be part of the list.
     */
    function getExerciseBinding($exerciseList)
    {
        /* Invert $exerciseList to map from exercise id to the given array
           key. At the same time construct a list of exercises that will limit
           the exercise-student mapping query below. */
        $exerciseMap = array();
        $exerciseIds = array();
        foreach ($exerciseList as $key => $val)
        {
            $id = $val['id'];
            $exerciseMap[$id] = $key;
            $exerciseIds[] = $id;
        }

        $this->dumpVar('exerciseList', $exerciseList);
        $this->dumpVar('exerciseMap', $exerciseMap);

        /* Now convert the contents of $exerciseIds to a list that can be used
           as a parameter to SQL WHERE ... IN(...) clause. */
        $eids = arrayToDBString($exerciseIds);

        /* TODO: Limit the list of students to those who are actually
           attending the lecture in this school year. */
        $rs = DatabaseBean::dbQuery(
            'SELECT student_id, exercise_id FROM stud_exc ' .
            'WHERE exercise_id IN(' . $eids . ') ' .
            'ORDER BY student_id');

        $binding = array();
        foreach ($rs as $key => $val)
        {
            $eid = $val['exercise_id'];
            $sid = $val['student_id'];
            if (array_key_exists($eid, $exerciseMap))
            {
                $binding[$sid] = $exerciseMap[$eid];
            }
            //else
            //{
            //    /* Students that repeat the lecture will have more than a
            //       single record returned - the database contains all
            //       exercises that they have ever attended. If such an older
            //       `eid` (which is not in the `exerciseMap`) comes after a
            //       newer, valid one (which is in the `exerciseMap`), the
            //     * binding for that student will be voided. We sould prevent
            //     * that. */
            //    if ( ! array_key_exists ( $sid, $binding ))
            //    {
            //        $binding[$sid] = NULL;
            //    }
            //}                 
        }

        //$this->dumpVar ( 'binding',  $binding );

        return $binding;
    }

    function getStudentListForExercise($exerciseId)
    {
        $rs = DatabaseBean::dbQuery("SELECT student_id FROM stud_exc WHERE exercise_id=" . $exerciseId);
        $studentList = array();
        if (isset ($rs))
        {
            foreach ($rs as $key => $val)
            {
                $studentList[] = $val['student_id'];
            }
        }
        return $studentList;
    }

    /**
     * Remove students assigned to an exercise from a list of students.
     * Given a list of students, finds those of them that are not assigned to any
     * exercise. Assumes that the list is valid, does not check that the students
     * did not e.g. enroll for the given lecture.
     * @param $id_list array Array of student ids.
     * @param $exercise_list array List of exercises to check for assigned students
     * @return array Array of unassigned student ids.
     */
    function removeAssignedStudents($id_list, $exercise_list)
    {
        $ids = arrayToDBString($id_list);
        $eids = arrayToDBString($exercise_list);
        $rs = self::dbQuery(
            "SELECT id FROM student st " .
            "LEFT JOIN stud_exc se ON st.id=se.student_id AND se.exercise_id IN ($eids) " .
            "WHERE st.id IN ($ids) AND se.student_id IS NULL AND se.exercise_id IS NULL");
        $uids = array_column($rs, 'id');
        $this->dumpVar('unassigned ids', arrayToDBString($uids));
        return $uids;
    }

    /**
     * Assign students to an exercise based on their study group info.
     * @param $exercise ExerciseBean Object defining the exercise.
     */
    function assignStudentsToExercise($exercise)
    {
        /* Find out which exercise ids are currently valid for this school year and the active lecture.
           TODO: Check if we can use the $exercise for this. Probably yes.
         */
        $eb = new ExerciseBean (0, $this->_smarty, NULL, NULL);
        $ers = $eb->getExercisesForLecture($this->id, $this->schoolyear);
        /* We have to get a list of IDs */
        $eid_list = array_column($ers, 'id');

        /* Fetch a list of students of the actual lecture in the actual school year that have the same
           student group as the exercise. */
        $sb = new StudentBean (0, $this->_smarty, null, null);
        $stid_list = $sb->dbQueryStudentIdsByGroup($exercise->lecture_id, $exercise->getGroupNo(), $exercise->schoolyear);

        /* Remove the students that are already assigned to some exercise. */
        $stid_list = $this->removeAssignedStudents($stid_list, $eid_list);
        /* Assign the students to the exercise.
           For this we need an array of the form student_id => exercise_id. We have the list of students
           that need to be assigned, and we have the exercise id. The array may be constructed using
           a PHP function `array_fill_keys`.
           TODO: Ever `dbReplace()` call queries current list of exercises and deletes old entries before inserting.
           In our case the old entries do not exist and it is therefore not necessary to delete them ...
         */
        $this->relation = array_fill_keys($stid_list, $exercise->id);
        $this->dbReplace();
    }

    /**
     * Prepare a map holding student-exercise info.
     * @return array
     */
    function prepareStudentExerciseMap()
    {
        /* Get the list of all exercises that have been defined for the current
           school year, assign it to the Smarty variable 'exerciseList' and
           return it to us as well, we will need it later. The value of
           $this->id holds the lecture_id in this case. */
        $exerciseBean = new ExerciseBean (0, $this->_smarty, null, null);
        $exerciseList = $exerciseBean->assignFull($this->id, $this->schoolyear);

        /* Now create an array that contains student id as an key and _index_ to
           the $exerciseList as a value (that is, not the exercise ID, but the
           true index into the array. */
        $exerciseBinding = $this->getExerciseBinding($exerciseList);
        $this->dumpVar('exerciseBinding', $exerciseBinding);

        /* Get the list of all students. Additionally, create a field 'checked'
           that contains text ' checked="checked"' on the position of the exercise
           that the particular student visits, and '' otherwise. */
        $studentBean = new StudentBean (0, $this->_smarty, null, null);
        $map = $studentBean->assignStudentListWithExercises(
            $this->id, count($exerciseList), $exerciseBinding);

        return $map;
    }

    /* -------------------------------------------------------------------
       HANDLER: SHOW
       ------------------------------------------------------------------- */
    function doShow()
    {
        /* Query data of this section */
        $this->dbQuerySingle();
        /* The function above sets $this->rs to values that shall be
           displayed. By assigning $this->rs to Smarty variable 'section'
           we can fill the values of $this->rs into a template. */
        $this->_smarty->assign('person', $this->rs);
        /* Get left-hand menu, which will be an empty menu pointing to the
           parent level. */
        $this->_smarty->assign('leftcolumn', "leftempty.tpl");
    }

    /* -------------------------------------------------------------------
       HANDLER: SAVE
       ------------------------------------------------------------------- */
    function doSave()
    {
        /* Assign POST variables to internal variables of this class and
           remove evil tags where applicable. */
        $this->processPostVars();
        /* Update all the records. */
        $this->dbReplace();
        /* Get the lecture description, just to fill in some more-or-less
           usefull peieces of information. */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, "x", "x");
        $lectureBean->assignSingle();
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
        /* Get the lecture description, just to fill in some more-or-less
           useful peieces of information. */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, null, null);
        $lectureBean->assignSingle();

        $this->prepareStudentExerciseMap();

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
            /* Query data of this person. */
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
        $this->assign('user', $this->rs);
        /* Get the list of all possible person categories. */
        $this->assign('roles', $this->_getUserRoles());
        /* Left column contains administrative menu */
        $this->assign('leftcolumn', "leftadmin.tpl");
    }
}

?>
