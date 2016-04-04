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
    private $lecturer_id;

    /* Constructor */
    function __construct ( $id, &$smarty, $action, $object )
    {
        /* Call parent's constructor first */
        parent::__construct ( $id, $smarty, "lecture_lecturer", $action, $object );
    }

    function dbReplace ()
    {
        DatabaseBean::dbQuery (
            "REPLACE lecture_lecturer VALUES ("
            . $this->lecture_id . ","
            . $this->lecturer_id . ","
            . $this->schoolyear . ")"
        );
    }

    function getLecturersForLecture ( $lecture_id=0 )
    {
        if ($lecture_id == 0)
        {
            $lecture_id = SessionDataBean::getLectureId();
        }

        $rs = DatabaseBean::dbQuery (
            "SELECT le.* FROM lecture_lecturer ll LEFT JOIN lecturer le ON le.id=ll.lecturer_id" .
            " WHERE ll.lecture_id=" . $lecture_id . " ORDER BY le.surname,le.name"
        );

        return $rs;
    }

    function isValidLecturerOfLecture  ( $user_id, $lecture_id )
    {
        $rs = DatabaseBean::dbQuery (
            "SELECT * FROM lecture_lecturer" .
            " WHERE user_id=" . $user_id .
            " AND lecture_id=" . $lecture_id
        );

        return isset($rs);
    }
}