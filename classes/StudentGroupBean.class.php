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

    /* Sorting */
    const GRP_SORT_BY_GRP = 1;
    const GRP_SORT_BY_NAME = 2;
    const GRP_SORT_BY_LOGIN = 3;

    /* Static project resource identifier for ftok().
       Has to be a single character. */
    static private $projectId = 'A';

    protected $lecture_id;
    protected $schoolyear;
    protected $max_places;
    protected $title;
    protected $forcegroup;
    protected $group_open_from;
    protected $group_open_to;

    private $gots_from;
    private $gots_to;

    static function GRPTYPE_LIST()
    {
        return array(
            self::GRPTYPE_NONE => 'Bez skupin',
            self::GRPTYPE_EXERCISE => 'Pouze v rámci cvičení',
            self::GRPTYPE_LECTURE => 'V rámci předmětu');
    }

    static function getGroupTypeString($group_type)
    {
        $group_types = self::GRPTYPE_LIST();
        return $group_types[$group_type];
    }

    /**
     * @throws Exception
     */
    private function _setDefaults()
    {
        $this->lecture_id = SessionDataBean::getLectureId();
        $this->schoolyear = SessionDataBean::getSchoolYear();
        $lecture_data = SessionDataBean::getLecture();
        $this->max_places = $lecture_data['group_limit'];
        $this->title = '';

        $termParams = new LectureTermParamBean(null, $this->_smarty, null, null);
        try
        {
            $termParams->dbQuerySingle();
            $this->group_open_from = $termParams->getGroupOpenFrom();
            $this->group_open_to = $termParams->getGroupOpenTo();
        } catch (Exception $e)
        {
            error_log("Exception: {$e->getMessage()}");
            $this->group_open_from = LectureTermParamBean::DEFAULT_DATE;
            $this->group_open_to = LectureTermParamBean::DEFAULT_DATE;
        }

        $dt = new DateTime($this->group_open_from);
        $this->gots_from = $dt->getTimestamp();

        $dt = new DateTime($this->group_open_to);
        // $dt->modify('+1 day');
        $dt->modify('+15 minutes');
        $this->gots_to = $dt->getTimestamp();

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
            "SELECT * FROM studentgroup WHERE object_id=$this->lecture_id AND year=$this->schoolyear");
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

    /**
     * Get the active student groups and the count of students in every group.
     * @return array
     * @throws Exception
     */
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

    /**
     * Get a list of student groups where a student may still join the group.
     * @throws Exception
     */
    function getFreeGroupsList()
    {
        /* Initialize the free group list */
        $free_groups = array();
        /* This will always return a non-empty list of groups. */
        $rs = $this->getGroupsOccupancy();
        foreach ($rs as $group)
        {
            /* Get the number of free places in this group. */
            $free_places = $this->max_places - $group['cs'];
            if ($free_places > 0)
            {
                $id = $group['id'];
                $group['free_places'] = $free_places;
                $group['namef'] = $group['name'] . " (free: $free_places)";
                $free_groups[$id] = $group;
            }
        }
        $this->dumpVar('free student groups', $free_groups);

        return $free_groups;
    }

    /**
     * Get a list of student groups that are completely empty.
     * @throws Exception
     */
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
                $empty_groups[] = $group;
            }
        }
        $this->dumpVar('empty student groups', $empty_groups);

        return $empty_groups;
    }

    /**
     * Get a list of students that are not assigned to any student group.
     * @param array $groups Limit the search to study groups from the array.
     * @return array Student data of unassigned students.
     */
    function getUnassignedStudentList($groups=null)
    {
        /* WARNING: This is not correct. It ignores students that enrolled for the second or third time. */
        /*
            $rs = $this->dbQuery(
            "SELECT st.* " .
            "FROM stud_lec AS sl " .
            "LEFT JOIN stud_group AS sp ON sl.student_id=sp.student_id AND sp.cancel_stamp IS NULL " .
            "LEFT JOIN studentgroup AS sg ON sp.group_id=sg.id " .
            "LEFT JOIN student AS st ON st.id=sl.student_id " .
            "WHERE sl.lecture_id=" . $this->lecture_id . " " .
            "AND sl.year=" . $this->schoolyear . " " .
            "AND sp.group_id IS NULL");
        */

        /* Better: Use a right join. This will insert NULLs where no group exists, so the unassigned students may
           be filtered out using the sg.id is NULL condition. */
        $where = "";
        if (! empty($groups))
        {
            $where = "AND st.groupno IN (" . arrayToDBString($groups, false) . ") ";
        }

        $rs = $this->dbQuery(
           "SELECT st.* " .
           "FROM studentgroup AS sg " .
           "LEFT JOIN stud_group as sp ON (sg.id=sp.group_id) " .
           "RIGHT JOIN stud_lec as sl ON (sl.student_id=sp.student_id AND sl.lecture_id=sg.object_id AND sl.year=sg.year) " .
           "JOIN student AS st ON (sl.student_id=st.id) " .
           "WHERE sl.lecture_id=$this->lecture_id AND sl.year=$this->schoolyear " .
           "AND sp.cancel_stamp is NULL AND sg.id IS NULL " . $where .
           "ORDER BY st.surname,st.firstname");

        return $rs;
    }

    /**
     * Assign selected students to selected groups using round-robbin assignment.
     * Used to force group assignment for students that did not select a student group by themselves.
     * @param $unassignedStudents array Student ids for students that did not select a student group.
     * @param $emptyGroups array Empty group identifiers.
     * @return array The list of assigned groups and their students.
     * @throws Exception in case that we do not have enough free places
     */
    function forceGroupAssignment($unassignedStudents, $emptyGroups)
    {
        $numStudents = count($unassignedStudents);
        if ($numStudents > count($emptyGroups)*$this->max_places)
        {
            throw new Exception(
                'Number of unassigned students is higher than the number of free places in empty groups');
        }

        /* We have enough places, duplicate the list of empty groups at most `max_places` times to
           accommodate the given number of unassigned students. */
        $groupList = $emptyGroups;
        while (count($groupList) < $numStudents)
        {
            $groupList = array_merge($groupList, $emptyGroups);
        }

        $i = 0;
        $forced_groups = array();
        while ($i < $numStudents)
        {
            $student = $unassignedStudents[$i];
            $group = $groupList[$i];
            $group_id = $group['id'];
            if (!array_key_exists($group_id, $forced_groups))
                $forced_groups[$group_id] = array( 'group' => $group, 'students' => array());
            $forced_groups[$group_id]['students'][] = $student;
            $this->dumpVar('setting student to group', array($student, $group));
            $this->setGroupIdForStudent($student['id'], $group_id, true);
            $i++;
        }

        $this->dumpVar('forced groups', $forced_groups);
        return $forced_groups;
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
            trigger_error(
                "Cannot determine free places for $this->lecture_id, student group $group_id",
                E_WARNING);
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
     * @param bool $forced True if the assignment was forced (pseudo)-automatically.
     * @return int Error code or self::ERR_OK
     */
    function setGroupIdForStudent($student_id, $group_id, $forced=false)
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
                /* Stealing a stale lock is perfectly okay. On the other hand we would better let the user
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

        /* We have the resource locked.
           Check that the selected group is not full. */
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

    /**
     * Get the students in the same student group as student `$student_id`.
     * @param $student_id
     * @return array[] All students of a student group where `$student_id` is a member.
     */
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
     * Return a list of students solving the same subtask as the active student.
     * In case that the task is not a group task, an array with only one entry is returned.
     * In case of a group task and a student that does not belong to any group, NULL is returned and `action` flag is
     * set to 'e_nogroup'.
     * @param boolean $task_is_group_task True if the caller task/subtask is a group task.
     * @return array | null Result of comparison
     */
    function getValidStudentsForTaskType($task_is_group_task)
    {
        /* Initialise the return variable. */
        $students = null;

        /* Check student group validity if the task is a group task. */
        if ($task_is_group_task && (SessionDataBean::getLectureGroupType() != StudentGroupBean::GRPTYPE_NONE))
        {
            // $sgb = new StudentGroupBean(null, $this->_smarty, null, null);
            $students = $this->getGroupStudentsOfStudent(SessionDataBean::getUserId());
            /* Check that the student is really member of a student group.
               If so, the $students will contain at least his/her student id. */
            if (empty($students))
            {
                /* Check failed. Indicate it. */
                $students = null;
            }
        }
        else
        {
            /* Not a group task. */
            $students = StudentGroupBean::getDefaultGroupStudents(SessionDataBean::getUserId());
        }

        return $students;
    }


    /**
     * Provide a full list of student groups with membership info.
     * @param int $sort_type
     * @return array Hierarchical array of groups and studens
     * @throws Exception in case of unimplemented view
     */
    function assignFullGroupList($sort_type = self::GRP_SORT_BY_GRP)
    {
        $this->dumpVar('lecture group type', SessionDataBean::getLectureGroupType());
        $group_list = array();
        switch (SessionDataBean::getLectureGroupType())
        {
            case self::GRPTYPE_NONE:
                $this->action = 'e_grptypenone';
                break;
            case self::GRPTYPE_EXERCISE:
                throw new Exception('This view has not been implemented yet.');
            case self::GRPTYPE_LECTURE:
                $object_id = SessionDataBean::getLectureId();
                $rs = self::dbQuery("SELECT " .
                    "sg.id AS group_id, sg.year AS group_year, sg.name AS group_name, sg.object_id, " .
                    "sp.entry_stamp, sp.cancel_stamp, " .
                    "st.id, st.firstname, st.surname, st.yearno, st.groupno, st.email, st.calendaryear " .
                    "FROM studentgroup AS sg " .
                    "LEFT JOIN stud_group AS sp ON sg.id=sp.group_id " .
                    "LEFT JOIN student AS st ON sp.student_id=st.id " .
                    "WHERE sg.year=" . SessionDataBean::getSchoolYear() . " " .
                    "AND sg.object_id=$object_id " .
                    "ORDER BY sg.id, st.surname, st.firstname, entry_stamp");
                $this->dumpVar('lecture group rs',$rs);
                /* Now group the list by group */
                $grp_id0 = null;
                $grp_id1 = null;
                $group_data = array();
                $group = null;
                /* Reverse the array so that we can access the contents starting from the first element using
                   array_pop(). */
                $rs = array_reverse($rs);
                while (true)
                {
                    /* Get the element off the tail of the reversed array. */
                    $val = array_pop($rs);
                    /* In case that we are not at the end of the array, get the group id. */
                    if (isset($val)) $grp_id1 = intval($val['group_id']);
                    if ($val === null || $grp_id0 != $grp_id1)
                    {
                        /* Store the old group? */
                        if ($grp_id0 !== null)
                        {
                            $group['students'] = $group_data;
                            $group_list[$grp_id0] = $group;
                        }
                        /* Break out of the loop? */
                        if ($val === null) break;
                        /* If not, create a new group storage */
                        $group = array( 'id' => $grp_id1, 'name' => $val['group_name'], 'students' => null);
                        $group_data = array();
                        $grp_id0 = $grp_id1;
                    }
                    /* Append student data in case that there is a student assigned to this group. */
                    if (isset($val['id'])) $group_data[] = $val;
                }
        }
        $this->assign('groupList', $group_list);
        $this->assign('groupListSort', $sort_type);
        return $group_list;
    }

    /**
     * Make group deadlines and their parameters available to Smarty.
     * Publishes `group_open_from`, `group_open_to`, `group_open` and `group_past`.
     */
    public function assignGroupDeadlines()
    {
        $current_timestamp = time();
        $group_open = ($current_timestamp >= $this->gots_from && $current_timestamp <= $this->gots_to);
        $group_past = ($current_timestamp > $this->gots_to);
        $this->assign('group_open_from', $this->group_open_from);
        $this->assign('group_open_to', $this->group_open_to);
        $this->assign('group_open', $group_open);
        $this->assign('group_past', $group_past);
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

    /**
     * Pass option information about free student groups to Smarty templates.
     * Creates Smarty variable `free_group_options`.
     * @throws Exception in case that there is no free place available
     * @return array Array of free group options indexed by group id
     */
    function assignFreeGroupsHtmlOptions()
    {
        $free_groups = $this->getFreeGroupsList();
        $free_groups = array_column($free_groups, 'namef', 'id');
        $this->assign('free_group_options', $free_groups);
        $this->dumpVar('free student group options', $free_groups);
        return $free_groups;
    }

    /**
     * Handle `show` event.
     * @throws Exception
     */
    function doShow()
    {
        /* Make sure that all necessary template variables are defined. */
        // $this->assign('group_open_from', null);
        // $this->assign('group_open_to', null);
        $this->assign('group_open', null);

        $this->assignFullGroupList();
    }

    /**
     * Handle `admin` event.
     * The admin handler is used to either display interface for specifying the number of student groups
     * to generate, or it is used to force group assignment for students that did not choose a group
     * for themselves.
     * @throws Exception
     */
    function doAdmin()
    {
        $force_confirm = false;
        assignPostIfExists($force_confirm, $this->rs, 'force_confirm');
        if (! $force_confirm)
        {
            /* Check if we are generating student groups or forcing group assignments. */
            assignGetIfExists($this->forcegroup, $this->rs, 'forcegroup');
            if ($this->forcegroup)
            {
                /* Display a list of unassigned students. Those students will be assigned to their own groups
                   in the next step. */
                $unassigned_students = $this->getUnassignedStudentList();
                /* Get the list of student groups of unassigned students */
                $groups = array();
                foreach ($unassigned_students as $val)
                {
                    $group = intval($val['groupno']);
                    $groups[$group] = $group;  // This way we do not have to test for existing entries
                }
                sort($groups);
                $this->assign('groupList', $groups);
                $this->assign('unassigned_students', $unassigned_students);
                $this->action = 'forcelist';
            }
        }
        else
        {
            /* Second stage of forcing a group. */
            assignPostIfExists($groups, $this->rs, 'groups');
            $unassigned_students = $this->getUnassignedStudentList($groups);
            $this->dumpVar('unassigned students', $unassigned_students);
            $empty_groups = $this->getEmptyGroupsList();
            $forced_groups = $this->forceGroupAssignment($unassigned_students, $empty_groups);
            $this->assign('forced_groups', $forced_groups);
            $this->action = 'forcegroup';
        }
    }

    /**
     * Handle `edit` events
     * The edit handler is called when a students decides to join a group.
     * @throws Exception
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
     * Fetch data needed to confirm the removal of student from certain student group.
     * @throws Exception
     */
    function doDelete()
    {
        $this->dbQuerySingle();
        $this->assign('student_group', $this->rs);
    }

    /**
     * Really delete the association of student to certain student group.
     * @throws Exception
     */
    function doRealDelete()
    {
        /* We need to check all subtask assignments and delete them as well.
           For this we need to know the current evaluation scheme that will in turn provide us with the list
           of subtasks that are valid in this schoolyear. */
        $evb = new EvaluationBean (null, $this->_smarty, null, null);
        /* The EvaluationBean constructed above does not map to any evaluation scheme. We have to initialise
           it for the current lecture (the id of the lecture is stored in the session). The function returns
           'true' if the evaluation scheme has been found and the object has been initialised. */
        $res = $evb->initialiseFor(SessionDataBean::getLectureId(), $this->schoolyear);
        if (!$res)
        {
            /* No evaluation for this school year, abort. */
            $this->action = 'e_evalinit';
            return ERR_ADMIN_MODE;
        }

        /* Student identifier is a part of the session. */
        $student_id = SessionDataBean::getUserId();
        $res = $this->removeStudentFromGroup($student_id, $this->id);
        if ($res != self::ERR_OK)
        {
            trigger_error("removeStudentFromGroup() did not succeed, error $res", E_USER_WARNING);
        }

        /* Get a list of all valid subtasks for this lecture in this schoolyear. */
        $subtask_list = $evb->getSubtaskList();
        /* And remove those subtasks from assignment storage in case that someone already assigned assignments. */
        $asb = new AssignmentsBean(null, $this->_smarty, null, null);
        $asb->deleteAssignments($student_id, $subtask_list);

        $this->dbQuerySingle();
        $this->assign('student_group', $this->rs);
    }
}