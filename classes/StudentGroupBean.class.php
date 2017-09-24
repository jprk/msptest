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

    /* Error codes */
    const ERR_OK = 0;
    const ERR_HAVEGRP = 1;
    const ERR_ISMEMBER = 2;
    const ERR_NOTMEMBER = 3;
    const ERR_FULL = 4;
    const ERR_LOCKED = 64;
    const ERR_FTOK = 65;
    const ERR_SEMACQ = 66;
    const ERR_MUTEX = 67;

    /* Static project resource identifier for ftok().
       Has to be a single character. */
    static private $projectId = 'A';

    protected $lecture_id;
    protected $schoolyear;
    protected $max_places;
    protected $title;
    protected $forcegroup;

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
        $lecture_data = SessionDataBean::getLecture();
        $this->max_places = $lecture_data['group_limit'];
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

    function createStudentGroups($num)
    {
        /* Check that no student groups exist for the given lecture and school year. */
        $rs = $this->dbQuery(
            "SELECT * FROM studentgroup WHERE object_id=" . $this->lecture_id .
            " AND year=" . $this->schoolyear);
        /* If so, return an error. */
        if (!empty($rs))
        {
            return self::ERR_HAVEGRP;
        }

        /* Query the lecture record for information about student group type for this lecture. */
        $lecture_data = SessionDataBean::getLecture();
        switch ($lecture_data['group_type'])
        {
            case self::GRPTYPE_EXERCISE:
                throw new RuntimeException('Group type exercise not implemented yet.');
                /* The group is exercise-wide */
                /* Loop over all exercises ... */
                /* Create $num new records for the exercise. */
                break;
            case self::GRPTYPE_LECTURE:
                /* The group is lecture-wide. Create $num new records total. */
                $lecture_code = $lecture_data['code'];
                for ($i = 1; $i <= $num; $i++)
                {
                    $group_name = sprintf('%s-%d-%d', $lecture_code, $this->schoolyear, $i);
                    $this->dbQuery("INSERT INTO studentgroup(object_id,year,name) "
                        . "VALUES (" . $this->lecture_id . "," . $this->schoolyear . ",'" . $group_name . "')");
                }
                break;
            default:
                throw new RuntimeException('Unknown group type in lecture.');
        }

        return self::ERR_OK;
    }


    function getGroupForStudent($student_id)
    {
        $rs = $this->dbQuery(
            "SELECT grp.* FROM studentgroup AS grp" .
            " LEFT JOIN stud_group AS sg ON (grp.id=sg.group_id AND sg.cancel_stamp IS NULL)" .
            " WHERE grp.object_id=" . $this->lecture_id .
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
        if ($ngrps > 0)
        {
            $rs = $rs[0];
        }

        return $rs;
    }

    function getGroupsOccupancy()
    {
        /* Get the count of students in every active student group. */
        $rs = $this->dbQuery(
            "SELECT grp.*, COUNT(sg.student_id) AS cs FROM studentgroup AS grp" .
            " LEFT JOIN stud_group AS sg ON (grp.id=sg.group_id AND sg.cancel_stamp IS NULL)" .
            " WHERE grp.object_id=" . $this->lecture_id .
            " AND grp.year=" . $this->schoolyear .
            " GROUP BY grp.id");

        $this->dumpVar('all student groups', $rs);

        if (empty($rs))
        {
            throw new Exception('The list of returned student groups is empty!');
        }
        return $rs;
    }

    function getFreeGroupsList()
    {
        /* Initialize the free group list */
        $free_groups = array();
        /* This will always return a list of groups. */
        $rs = $this->getGroupsOccupancy();
        foreach ($rs as $group)
        {
            $free_places = $this->max_places - $group['cs'];
            if ($free_places > 0)
            {
                $id = $group['id'];
                $name = $group['name'] . " (free: $free_places)";
                $free_groups[$id] = $name;
            }
        }
        $this->dumpVar('free student groups', $free_groups);

        return $free_groups;
    }

    function getEmptyGroupsList()
    {
        /* Initialize the free group list */
        $empty_groups = array();
        /* This will always return a list of groups. */
        $rs = $this->getGroupsOccupancy();
        foreach ($rs as $group)
        {
            if (intval($group['cs']) == 0)
            {
                $id = $group['id'];
                $empty_groups[] = $id;
            }
        }
        $this->dumpVar('empty student groups', $empty_groups);

        return $empty_groups;
    }

    function getUnassignedStudentList()
    {
        $rs = $this->dbQuery(
            "SELECT sl.student_id FROM stud_lec AS sl" .
            "  LEFT JOIN stud_group AS sp ON sl.student_id=sp.student_id AND sp.cancel_stamp IS NULL" .
            "  LEFT JOIN studentgroup AS sg ON sp.group_id=sg.id " .
            "WHERE sl.lecture_id=" . $this->lecture_id . " AND sl.year=" . $this->schoolyear .
            "      AND sp.group_id IS NULL");

        $students = array();
        if (!empty($rs))
        {
            foreach ($rs as $row)
            {
                $students[] = $row['student_id'];
            }
        }
        return $students;
    }

    function forceGroupAssignment($unassignedStudents,$emptyGroups)
    {
        $numStudents = count($unassignedStudents);
        if ($numStudents > count($emptyGroups)*$this->max_places)
        {
            throw new Exception('Number of unassigned students higher than number of free places in empty groups');
        }

        $groupList = $emptyGroups;
        while (count($groupList) < $numStudents)
        {
            $groupList = array_merge($groupList, $emptyGroups);
        }

        $i = 0;
        while ($i < $numStudents)
        {
            $student_id = $unassignedStudents[$i];
            $group_id = $groupList[$i];
            $this->dumpVar('setting student to group', array($student_id, $group_id));
            $this->setGroupIdForStudent($student_id, $group_id);
            $i++;
        }
    }

    function getFreePlaces($group_id)
    {
        $rs = $this->dbQuery(
            "SELECT grp.*, COUNT(sg.student_id) AS cs FROM studentgroup AS grp" .
            " LEFT JOIN stud_group AS sg ON (grp.id=sg.group_id AND sg.cancel_stamp IS NULL)" .
            " WHERE grp.object_id=" . $this->lecture_id .
            " AND grp.year=" . $this->schoolyear .
            " AND grp.id=" . $group_id);

        if (isset($rs))
        {
            $free_places = $this->max_places - intval($rs[0]['cs']);
        }
        else
        {
            trigger_error("Cannot determing free places for $this->lecture_id, student group $group_id", E_WARNING);
            $free_places = -1;
        }
        return $free_places;
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

    /**
     * Set the student group ID for the given student.
     * @param $student_id int Student identifier.
     * @param $group_id int Group ID of the student group.
     * @return int Error code or self::ERR_OK
     */
    function setGroupIdForStudent($student_id, $group_id)
    {
        /* Check that the student is not a member of another group in this lecture and this school year. */
        $group_data = $this->getGroupForStudent($student_id);
        /* If there is a record for this student, return an error. */
        if (!empty($group_data))
        {
            $this->dumpVar('group_data', $group_data);
            $this->action = 'e_ismember';
            return self::ERR_ISMEMBER;
        }

        /* Lock the access to the table. */
        $res = mutexLock($this, self::$projectId, $lockTime, $lockLogin);

        /* Call to mutexLock() may return several return codes and we have
           to react to all of them. */
        switch ($res)
        {
            /** @noinspection PhpMissingBreakStatementInspection */
            case MUTEX_LOCK_STOLEN_OK;
                /* Stealing a stale lock is perfecty okay. On the other hand we would better let the user
                   know that someone has started editing the data and did not save them for more than 30 minutes. */
                $this->assign('lockstolen', true);
            case MUTEX_OK:
                /* Pass to the next stage. */
                break;
            case MUTEX_E_ISLOCKED:
                /* Resource is locked. Refuse to edit. */
                $this->action = 'e_islocked';
                return self::ERR_LOCKED;
            case MUTEX_E_FTOK:
                /* Could not construct a valid semaphore id. */
                $this->action = 'e_ftok';
                return self::ERR_FTOK;
            case MUTEX_E_CANTACQUIRE:
                /* Could not acquire the semaphore used to block access to the mutex file. */
                $this->action = 'e_cantacquire';
                return self::ERR_SEMACQ;
            default:
                $this->action = 'e_mutexval';
                return self::ERR_MUTEX;
        }

        /* Check that the selected group is not full. */
        if ($this->getFreePlaces($group_id) > 0)
        {
            /* Add the student to the group. */
            $this->dbQuery("INSERT INTO stud_group VALUES ($student_id, $group_id, NULL, NULL)");
            $retval = self::ERR_OK;
        }
        else
        {
            /* If it is full, return error code. */
            $this->action = 'e_full';
            $retval = self::ERR_FULL;
        }

        /* Unlock access to the table. */
        $res = mutexUnlock($this, self::$projectId);

        /* Call to mutexUnlock() may return several return codes and we have to react
           to all of them. */
        switch ($res)
        {
            case MUTEX_OK:
                /* Pass to the next stage. */
                break;
            case MUTEX_E_FTOK:
                /* Could not construct a valid semaphore id. */
                $this->action = 'e_ftok';
                return self::ERR_FTOK;
            case MUTEX_E_CANTACQUIRE:
                /* Could not acquire the semaphore used to block access to the mutex file. */
                $this->action = 'e_cantacquire';
                return self::ERR_SEMACQ;
            default:
                $this->action = 'e_mutexval';
                return self::ERR_MUTEX;
        }

        return $retval;
    }

    /**
     * Cancel the membership of a student in a student group.
     * @param $student_id int Student identifier.
     * @param $group_id int Group ID of the student group.
     * @return int Error code or self::ERR_OK
     */
    function removeStudentFromGroup($student_id, $group_id)
    {
        /* Construct a `WHERE` cause that will be used twice in this function. */
        $where = " WHERE student_id=$student_id AND group_id=$group_id AND cancel_stamp IS NULL";

        /* Check that the student is a member of the given group in this lecture and this school year. */
        $rs = $this->dbQuery("SELECT * FROM stud_group $where");

        /* If there is no record for this student, return an error. */
        if (empty($rs))
        {
            $this->action = 'e_notmember';
            return self::ERR_NOTMEMBER;
        }

        $num_records = count($rs);
        if ($num_records > 1)
        {
            trigger_error("Student $student_id is $num_records x member of group $group_id", E_WARNING);
        }

        /* Remove the student from the group simply by updating cancel_stamp of the current record. */
        $this->dbQuery("UPDATE stud_group SET cancel_stamp=NOW() $where");

        return self::ERR_OK;
    }

    static function getDefaultGroupStudents($student_id)
    {
        return array($student_id => array('id' => $student_id));
    }

    function getGroupStudentsOfStudent($student_id)
    {
        /* Note: if the format of $ret changes, change also self::getDefaultGroupStudents() accordingly. */
        $rs = $this->dbQuery(
            "SELECT student.*, grp.id AS group_id, grp.object_id, grp.year, grp.name FROM stud_group sg1" .
            " INNER JOIN stud_group sg2 ON (sg1.group_id=sg2.group_id)" .
            " LEFT JOIN studentgroup grp ON (grp.id=sg2.group_id)" .
            " LEFT JOIN student ON (sg2.student_id=student.id)" .
            " WHERE grp.object_id=" . $this->lecture_id .
            " AND grp.year=" . $this->schoolyear .
            " AND sg1.student_id=" . $student_id .
            " AND sg1.cancel_stamp IS NULL" .  // ... because there might be many `sg1` records for student
            " AND sg2.cancel_stamp IS NULL");  // ... because we want only those that did not leave the group

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

    function getGroupCount($lecture_id = 0, $schoolyear = 0)
    {
        if ($lecture_id == 0) $lecture_id = $this->lecture_id;
        if ($schoolyear == 0) $schoolyear = $this->schoolyear;

        $rs = $this->dbQuery(
            "SELECT COUNT(id) AS grpcount FROM studentgroup WHERE object_id=$lecture_id AND year=$schoolyear"
        );

        $grpcount = 0;
        if (!empty($rs))
        {
            $grpcount = intval($rs[0]['grpcount']);
        }
        return $grpcount;
    }

    /* Convert group number to group id. */
    function getGroupIdByNum($num, $lecture_id = 0, $schoolyear = 0)
    {
        if ($lecture_id == 0) $lecture_id = $this->lecture_id;
        if ($schoolyear == 0) $schoolyear = $this->schoolyear;

        /* First get the minimal id of the group for the lecture and schoolyear */
        $rs = self::dbQuery("SELECT MIN(id) AS baseid FROM studentgroup WHERE object_id=$lecture_id AND year=$schoolyear");
        if (empty($rs))
        {
            throw new Exception('cannot find minimal group id');
        }
        $grpid_base = intval($rs[0]['baseid']);
        $grpid = $grpid_base + $num - 1;

        /* And check that the given group is for the lecture and schoolyear */
        $rs = self::dbQuery("SELECT * FROM studentgroup WHERE id=$grpid AND object_id=$lecture_id AND year=$schoolyear");
        if (empty($rs))
        {
            trigger_error("Lecture $lecture_id: Group number $num maps to incorrect group id $grpid (group pid set to 0)");
            $grpid = 0;
        }
        return $grpid;
    }

    function getStudentsIdsForGroupId($grpid)
    {
        $rs = self::dbQuery("SELECT student_id FROM stud_group WHERE group_id=$grpid AND cancel_stamp IS NULL");
        $student_ids = array();
        if (!empty($rs))
        {
            foreach ($rs as $val)
            {
                $student_ids[] = $val['student_id'];
            }
        }
        return $student_ids;
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

    /**
     * Pass information about free student groups to Smarty templates.
     * Creates Smarty variable `free_groups`.
     * @throws Exception in case that there is no free place available
     * @return array Array of free groups
     */
    function assignFreeGroupsList()
    {
        $free_groups = $this->getFreeGroupsList();
        $this->assign('free_groups', $free_groups);
        return $free_groups;
    }

    function doAdmin()
    {
        assignGetIfExists($this->forcegroup, $this->rs, 'forcegroup');
        if ($this->forcegroup)
        {
            /* Force group assignment for unassigned students. */
            $unassignedStudents = $this->getUnassignedStudentList();
            $emptyGroups = $this->getEmptyGroupsList();
            $this->forceGroupAssignment($unassignedStudents, $emptyGroups);
            /* TODO: Get full infor for every student */
            /* TODO: Get list of assigned group-sudent pairs. */
            $this->action = 'forcegroup';
        }
    }

    /**
     * Handle `edit` events
     * The edit handler is called when a students decides to join a group.
     */
    function doEdit()
    {
        assignPostIfExists($this->id, $this->rs, 'group_id');
        $student_id = SessionDataBean::getUserId();
        $res = $this->setGroupIdForStudent($student_id, $this->id);
        if ($res != self::ERR_OK)
        {
            trigger_error("setGroupIdForStudent() did not succeed, error $res", E_USER_WARNING);
        }
        /* Could move to `assignSingle()` in case we need it. */
        $this->dbQuerySingle();
        $this->assign('student_group', $this->rs);
    }

    /**
     * Handle `save` events
     * We have a single save event when an administrator generates a list of groups.
     */
    function doSave()
    {
        assignPostIfExists($num_groups, $this->rs, 'num_groups');
        $this->assign('num_groups', $num_groups);

        $res = $this->createStudentGroups($num_groups);
        if ($res == self::ERR_HAVEGRP)
        {
            $this->action = 'err_havegr';
        }
    }

    /**
     * Handle `delete` event
     */
    function doDelete()
    {
        $this->dbQuerySingle();
        $this->assign('student_group', $this->rs);
    }

    function doRealDelete()
    {
        $student_id = SessionDataBean::getUserId();
        $res = $this->removeStudentFromGroup($student_id, $this->id);
        if ($res != self::ERR_OK)
        {
            trigger_error("removeStudentFromGroup() did not succeed, error $res", E_USER_WARNING);
        }
        $this->dbQuerySingle();
        $this->assign('student_group', $this->rs);
    }
}