<?php

/**
 * Created by PhpStorm.
 * User: prikryl
 * Date: 29.2.2016
 * Time: 15:42
 */
class StudentGroupBean extends DatabaseBean
{
    /* Types of student group. */
    const GRPTYPE_NONE = 0;
    const GRPTYPE_EXERCISE = 1;
    const GRPTYPE_LECTURE = 2;

    protected $lecture_id;
    protected $schoolyear;
    protected $title;

    static function GRPTYPE_LIST()
    {
        return array(
            self::GRPTYPE_NONE => 'Vyberte ze seznamu...',
            self::GRPTYPE_EXERCISE => 'Pouze v rámci cvičení',
            self::GRPTYPE_LECTURE => 'V rámci předmětu');
    }

    static function getGroupTypeString($group_type)
    {
        $group_types = self::GRPTYPE_LIST();
        return $group_types[$group_type];
    }

    function _setDefaults()
    {
        $this->lecture_id = SessionDataBean::getLectureId();
        $this->schoolyear = SessionDataBean::getSchoolYear();
        $this->title = '';
    }

    /* Constructor */
    function __construct($id, &$smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, "studentgroup", $action, $object);
        /* Initialise new properties to their default values. */
        $this->_setDefaults();
    }


    function getGroupForStudent($student_id)
    {
        $rs = $this->dbQuery(
            "SELECT grp.* FROM studentgroup AS grp LEFT JOIN stud_group AS sg ON grp.id=sg.group_id" .
            " WHERE grp.lecture_id=" . $this->lecture_id .
            " AND grp.year=" . $this->schoolyear .
            " AND sg.student_id=" . $student_id);

        $this->dumpVar('student group', $rs);

        /* Sanity check, the number of groups may be always only one. */
        $ngrps = count($rs);
        if ($ngrps > 1)
        {
            trigger_error("inconsistency: we have " . $ngrps . " groups for student " . $student_id, E_WARNING);
        }

        /* If no group returned, probably the student is not assigned to one. */
        if (isset($rs))
        {
            $rs = $rs[0];
        }

        return $rs;
    }

    /**
     * Get the student group ID for the given student.
     * @param $student_id int Student identifier.
     * @return int Group ID of the student group, null in case that the student is not a member of a student group.
     */
    function getGroupIdForStudent($student_id)
    {
        $group_data = $this->getGroupForStudent($student_id);
        return isset($group_data) ? $group_data['id'] : null;
    }

    static function getDefaultGroupStudents($student_id)
    {
        return array($student_id => array('id' => $student_id));
    }

    function getGroupStudentsOfStudent($student_id)
    {
        /* Note: if the format of $ret changes, change also self::getDefaultGroupStudents() accordingly. */
        $rs = $this->dbQuery(
            "SELECT student.*, grp.id as group_id, grp.lecture_id, grp.year, grp.name FROM stud_group sg1" .
            " INNER JOIN stud_group sg2 ON (sg1.group_id=sg2.group_id)" .
            " LEFT JOIN studentgroup grp ON (grp.id=sg2.group_id)" .
            " LEFT JOIN student ON (sg2.student_id=student.id)" .
            " WHERE grp.lecture_id=" . $this->lecture_id .
            " AND grp.year=" . $this->schoolyear .
            " AND sg1.student_id=" . $student_id);

        $this->dumpVar('student group of student ' . $student_id, $rs);

        $ret = array();
        if (isset($rs))
        {
            foreach ($rs as $val)
            {
                $ret[$val['id']] = $val;
            }
        }

        return $ret;
    }

    /**
     * Pass information about student's group and group students to Smarty templates.
     * Creates Smarty variables `group_data` and `group_students`.
     * @param $student_id int Identifier of the student.
     * @return array Array of [$group_data, $group_students]
     */
    function assignGroupAndGroupStudentsOfStudent($student_id)
    {
        $group_data = $this->getGroupForStudent($student_id);
        $this->assign('group_data', $group_data);
        $group_students = $this->getGroupStudentsOfStudent($student_id);
        $this->assign('group_students', $group_students);
        return array($group_data, $group_students);
    }
}