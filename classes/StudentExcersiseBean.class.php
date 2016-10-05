<?php

class StudentExcersiseBean extends DatabaseBean
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
        /* First let's find out which excersise ids have been defined for this
           school year and the active lecture. */
        $eb = new ExcersiseBean (0, $this->_smarty, NULL, NULL);
        $ers = $eb->getExcersisesForLecture($this->id, $this->schoolyear);
        /* Now we have to transform the result into a string that can be used
           as a parameter of "IN" clause. */
        $elst = array2ToDBString($ers, 'id');

        foreach ($this->relation as $key => $val)
        {
            $this->dbQuery(
                "DELETE FROM stud_exc " .
                "WHERE student_id=" . $key . " " .
                "AND excersise_id IN (" . $elst . ")");
            $this->dbQuery(
                "REPLACE stud_exc VALUES ("
                . $key . ","
                . $val . ")"
            );
        }

        // echo "<!-- replace ok -->\n";
    }

    /* Assign POST variables to internal variables of this class and
       remove evil tags where applicable. We shall probably also remove
       evil attributes et cetera, but this will be done later if ever. */
    function processPostVars()
    {
        $this->relation = $_POST['se_rel'];
    }

    /**
     * Return a list of students attending given excersises.
     * Students that are not officially assigned to any of the extersises in
     * the $excersiseList will not be part of the list.
     */
    function getExcersiseBinding($excersiseList)
    {
        /* Invert $excersiseList to map from excersise id to the given array
           key. At the same time construct a list of excersises that will limit
           the excersise-student mapping query below. */
        $excersiseMap = array();
        $excersiseIds = array();
        foreach ($excersiseList as $key => $val)
        {
            $id = $val['id'];
            $excersiseMap[$id] = $key;
            $excersiseIds[] = $id;
        }

        $this->dumpVar('excersiseList', $excersiseList);
        $this->dumpVar('excersiseMap', $excersiseMap);

        /* Now convert the contents of $excersiseIds to a list that can be used
           as a parameter to SQL WHERE ... IN(...) clause. */
        $eids = arrayToDBString($excersiseIds);

        /* TODO: Limit the list of students to those who are actually
           attending the lecture in this school year. */
        $rs = DatabaseBean::dbQuery(
            'SELECT student_id, excersise_id FROM stud_exc ' .
            'WHERE excersise_id IN(' . $eids . ') ' .
            'ORDER BY student_id');

        $binding = array();
        foreach ($rs as $key => $val)
        {
            $eid = $val['excersise_id'];
            $sid = $val['student_id'];
            if (array_key_exists($eid, $excersiseMap))
            {
                $binding[$sid] = $excersiseMap[$eid];
            }
            //else
            //{
            //    /* Students that repeat the lecture will have more than a
            //       single record returned - the database contains all
            //       excersises that they have ever attended. If such an older
            //       `eid` (which is not in the `excersiseMap`) comes after a
            //       newer, valid one (which is in the `excersiseMap`), the
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

    function getStudentListForExcersise($excersiseId)
    {
        $rs = DatabaseBean::dbQuery("SELECT student_id FROM stud_exc WHERE excersise_id=" . $excersiseId);
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
        /* Get the list of all excersises that have been defined for the current
           school year, assign it to the Smarty variable 'excersiseList' and
           return it to us as well, we will need it later. The value of
           $this->id holds the lecture_id in this case. */
        $excersiseBean = new ExcersiseBean (0, $this->_smarty, "x", "x");
        $excersiseList = $excersiseBean->assignFull($this->id, $this->schoolyear);

        /* Get the lecture description, just to fill in some more-or-less
           useful peieces of information. */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, "x", "x");
        $lectureBean->assignSingle();

        /* Now create an array that contains student id as an key and _index_ to
           the $excersiseList as a value (that is, not the excersise ID, but the
           true index into the array. */
        $excersiseBinding = $this->getExcersiseBinding($excersiseList);
        $this->dumpVar('excersiseBinding', $excersiseBinding);

        /* Get the list of all students. Additionally, create a field 'checked'
           that contains text ' checked="checked"' on the position of the excersise
           that the particular student visits, and '' otherwise. */
        $studentBean = new StudentBean (0, $this->_smarty, "x", "x");
        $studentBean->assignStudentListWithExcersises(
            $this->id, count($excersiseList), $excersiseBinding);

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
        $this->_smarty->assign('user', $this->rs);
        /* Get the list of all possible person categories. */
        $this->_smarty->assign('roles', $this->_getUserRoles());
        /* Left column contains administrative menu */
        $this->_smarty->assign('leftcolumn', "leftadmin.tpl");
    }
}

?>
