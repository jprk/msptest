<?php

/**
 * Created by PhpStorm.
 * User: prikryl
 * Date: 23.3.2016
 * Time: 17:30
 */
class LectureLecturerBean extends DatabaseBean
{

    private $lecture_id;
    private $lecturer_states;

    /* Constructor */
    function __construct($id, &$smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, "lect_lecturer", $action, $object);
        $this->lecture_id = SessionDataBean::getLectureId();
        $this->lecturer_states = array();
    }

    function dbReplace()
    {
        /* Loop over the list of lecturer ids and create the list of entries for insert/replace. */
        $ids_to_insert = '';
        $ids_to_delete = '';
        $comma_insert = '';
        $comma_delete = '';
        foreach ($this->lecturer_states as $id => $assigned)
        {
            if ($assigned == 'true')
            {
                $ids_to_insert .= $comma_insert . '(' . $this->lecture_id . ',' . $id . ',' . $this->schoolyear . ')';
                $comma_insert = ',';
            }
            $ids_to_delete .= $comma_delete . $id;
            $comma_delete = ',';
        }

        /* Before inserting we have to delete, just for case that the insert comes multiple times ... */
        DatabaseBean::dbQuery(
            'DELETE FROM lect_lecturer '
            . 'WHERE lecturer_id IN (' . $ids_to_delete . ') '
            . 'AND lecture_id=' . $this->lecture_id . ' '
            . 'AND year=' . $this->schoolyear
        );

        if (!empty($ids_to_insert))
        {
            DatabaseBean::dbQuery('INSERT lect_lecturer VALUES ' . $ids_to_insert);
        }
    }

    function getLecturersForLecture($lecture_id = 0)
    {
        if ($lecture_id == 0)
        {
            $lecture_id = SessionDataBean::getLectureId();
        }

        $rs = DatabaseBean::dbQuery(
            "SELECT le.* FROM lect_lecturer ll LEFT JOIN lecturer le ON le.id=ll.lecturer_id" .
            " WHERE ll.lecture_id=$lecture_id" .
            " AND ll.year=$this->schoolyear ORDER BY le.surname,le.firstname"
        );

        return $rs;
    }

    function getActiveIdsChecked($lecture_id = 0)
    {
        if ($lecture_id == 0)
        {
            $lecture_id = SessionDataBean::getLectureId();
        }

        $rs = DatabaseBean::dbQuery(
            "SELECT lecturer_id FROM lect_lecturer" .
            " WHERE lecture_id=$lecture_id" .
            " AND year=$this->schoolyear"
        );

        /* TODO: For PHP>=5.5.0 we can use array_column() */
        $rs = array_map(function($element) {return $element['lecturer_id'];}, $rs);
        $rs = array_fill_keys($rs, 'checked="checked"');
        $this->dumpVar('lecturer_ids', $rs);
        return $rs;
    }

    function isValidLecturerOfLecture($lecturer_id, $lecture_id)
    {
        $rs = DatabaseBean::dbQuery(
            "SELECT * FROM lect_lecturer" .
            " WHERE lecturer_id=$lecturer_id" .
            " AND lecture_id=$lecture_id" .
            " AND year=$this->schoolyear"
        );

        return isset($rs);
    }

    function processPostVars()
    {
        $this->lecturer_states = $_POST['lecturer_states'];
    }

    function getSelectMap()
    {
        $rs = $this->getLecturersForLecture();

        $lecturerMap = array();
        if (isset ($rs))
        {
            foreach ($rs as $key => $val)
            {
                $lecturerMap[$val['id']] = $val['firstname'] . " " . $val['surname'];
            }
        }
        return $lecturerMap;
    }

    function assignSelectMap()
    {
        $this->assign('lectureLecturers', $this->getSelectMap());
    }

    function doSave()
    {
        /* Assign POST variables to internal variables of this class. */
        $this->processPostVars();
        /* Update the database. */
        $this->dbReplace();
    }
}