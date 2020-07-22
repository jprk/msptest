<?php

class TaskSubtasksBean extends DatabaseBean
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
        parent::__construct($id, $smarty, "tsksub", $action, $object);
        /* Initialise new properties to their default values. */
        $this->_setDefaults();
    }

    function dbReplace()
    {
        foreach ($this->relation as $key => $val)
        {
            DatabaseBean::dbQuery(
                "DELETE FROM tsksub " .
                "WHERE subtask_id=" . $key . " " .
                "AND year=" . $this->year
            );
            if ($val > 0)
            {
                DatabaseBean::dbQuery(
                    "REPLACE tsksub VALUES ("
                    . $this->year . ","
                    . $val . ","
                    . $key . ")"
                );
            }
        }
    }

    /* Assign POST variables to internal variables of this class and
       remove evil tags where applicable. We shall probably also remove
       evil attributes et cetera, but this will be done later if ever. */
    function processPostVars()
    {
        assignPostIfExists($this->year, $this->rs, 'year');
        $this->relation = $_POST['st_rel'];
    }

    function getSubtaskList()
    {
        $rs = DatabaseBean::dbQuery(
            "SELECT subtask_id FROM tsksub WHERE task_id="
            . $this->id);

        $subtaskList = array();
        foreach ($rs as $key => $val)
        {
            $subtaskList[] = $val['subtask_id'];
        }
        return $subtaskList;
    }

    /* Given a subtask map in the form of array indexed by subtask_id holding
       corresponding task_id, return a simple list of all subtask_ids. */
    static function getSubtaskListFromSubtaskMap($subtaskMap)
    {
        $subtaskList = array();
        foreach ($subtaskMap as $key => $val)
        {
            $subtaskList[] = $key;
        }
        return $subtaskList;
    }

    /**
     * Return a mapping from subtask to task in the current evaluation scheme.
     * This can be used for summing the subtask points relevant to particular
     * tasks. All subtasks belonging to a certain version of some evaluation
     * scheme have their `year` set to the same year as the relevant evaluation
     * scheme.
     * @param $task_list array List of integer task ids
     * @param $evaluation_year int|null Year of the evaluation scheme
     * @return array A map of subtask_id => task_id pairs.
     * @throws Exception In case that the map is empty
     */
    function getSubtaskMapForTaskList($task_list, $evaluation_year = null)
    {
        /* Default is the current school year. */
        if (is_null($evaluation_year)) $evaluation_year = $this->schoolyear;

        /* Convert the task id list to an SQL list. */
        $db_list = arrayToDBString($task_list);

        $this->dumpVar('getSubtaskListForTaskList::taskList', $task_list);
        //$this->dumpVar ( 'getSubtaskListForTaskList::db_list', $db_list );

        /* Get the subtask for an evaluation scheme that is valid in the given schoolyear. */
        $rs = $this->dbQuery(
            "SELECT subtask_id, task_id FROM tsksub WHERE task_id IN ($db_list) AND year=$evaluation_year");

        $subtask_map = array();
        if (!empty($rs))
        {
            $subtask_map = array_column($rs, 'task_id', 'subtask_id');
        }
        $this->dumpVar('subtask_map', $subtask_map);

        if (empty($subtask_map))
        {
            throw new Exception ('Lecture evaluation not configured properly: missing subtasks!');
        }

        return $subtask_map;
    }

    function getSubtaskTaskMap($taskIdSq, $subtaskList, $nullTaskId, $schoolYear)
    {
        $dbString = arrayToDBString($taskIdSq);
        $rs = DatabaseBean::dbQuery(
            "SELECT * FROM tsksub " .
            "WHERE year=" . $schoolYear . " " .
            "AND task_id IN (" . $dbString . ")"
        );
        /* This is the resulting subtask map. */
        $map = array();
        /* Initially, all subtasks are assigned to the "null task". */
        foreach ($subtaskList as $key => $val)
        {
            $map[$val['id']] = $nullTaskId;
        }
        /* Of there are some results, the affected subtasks will be assigned
           to their actual task stored in database. */
        if (isset ($rs))
        {
            foreach ($rs as $key => $val)
            {
                $map[$val['subtask_id']] = $val['task_id'];
            }
        }
        $this->dumpVar('map', $map);
        return $map;
    }

    /* -------------------------------------------------------------------
       HANDLER: EDIT
       ------------------------------------------------------------------- */
    function doEdit()
    {
        /* Get the description of the lecture we are editing evaluation
           bindings for. */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, "", "");
        $lectureBean->assignSingle();

        /* Get the list of tasks defined for this lecture. */
        $taskBean = new TaskBean ($this->id, $this->_smarty, "", "");
        $taskList = $taskBean->assignFull($this->id);
        $taskList = $taskBean->addNullTask($taskList);
        $taskIdSq = $taskBean->getIdSequence($this->id);

        /* Get the list of subtasks defined for this lecture. */
        $subtaskBean = new SubtaskBean ($this->id, $this->_smarty, "", "");
        $subtaskList = $subtaskBean->assignFull($this->id);

        /* Now get the mapping for the current school year, replacing
           any of the $subtaskList tasks that are not mapped by the "null
           task" id (which is zero in our case) */
        $stMap = $this->getSubtaskTaskMap(
            $taskIdSq, $subtaskList, TaskBean::NULL_TASK_ID,
            $this->schoolyear);

        /* Go through the 'taskList' and add a new field 'checked'
           to the entries of the list. The field is indexed by 'taskList'
           entries and contains either 'checked' or an empty string. */
        foreach ($taskList as $tk => $tv)
        {
            $tId = $taskList[$tk]['id'];
            $sChecked = array();
            foreach ($subtaskList as $sk => $sv)
            {
                $sId = $subtaskList[$sk]['id'];
                if (array_key_exists($sId, $stMap))
                {
                    $sChecked[$sk] = ($stMap[$sId] == $tId) ? ' checked' : '';
                }
                else
                {
                    $sChecked[$sk] = '';
                }
            }
            $taskList[$tk]['checked'] = $sChecked;
        }
        $this->_smarty->assign('taskList', $taskList);

        /* Provide the actual school year start to the template (the actual
           value of Smarty variable `schoolyear` is ####/####). */
        $this->_smarty->assign('year', SessionDataBean::getSchoolYear());
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
        /* Get the description of the lecture we are editing evaluation
           bindings for. */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, "", "");
        $lectureBean->assignSingle();
    }
}

?>
