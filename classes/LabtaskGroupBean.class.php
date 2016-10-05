<?php

/**
 * Group of laboratory tasks for a lecture.
 *
 * Defines groups of laboratory tasks, which will form a single laboratory excersise.
 * The laboratory tasks are stored as sections of type ST_PHYLAB.
 *
 * (c) 2013 Jan Přikryl
 */
class LabtaskGroupBean extends DatabaseBean
{
    protected $lecture_id;
    protected $group_id;

    function _setDefaults()
    {
        $this->lecture_id = SessionDataBean::getLectureId();
        $this->group_id = 0;
        /* Update $this->rs */
        $this->_update_rs();
    }

    /* Constructor */
    function __construct($id, &$smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, "labtask_group", $action, $object);
        /* Initialise default values. */
        $this->_setDefaults();
    }

    function dbReplace()
    {
        DatabaseBean::dbQuery(
            "REPLACE labtask_group VALUES ("
            . $this->id . ","
            . $this->lecture_id . ","
            . $this->schoolyear . ","
            . $this->group_id . ")"
        );
        $this->updateId();
    }

    function dbQuerySingle($alt_id = 0)
    {
        /* Query the data of this lecture (ID has been already specified) */
        DatabaseBean::dbQuerySingle($alt_id);
        /* Initialize the internal variables with the data queried from the
             database. */
        $this->lecture_id = $this->rs['lecture_id'];
        $this->schoolyear = $this->rs['year'];
        $this->group_id = $this->rs['group_id'];
    }

    function getList($what = '*', $where = '', $order = 'id')
    {
        $rs = DatabaseBean::dbQuery(
            "SELECT " . $what . " FROM labtask_group" . $where . " ORDER BY " . $order);
        return $rs;
    }

    /**
     * Get the complete list of labtask groups for the given lecture and
     * schoolyear. The list will be ordered by group id.
     *
     * @return array Resultset of the database query.
     */
    function getFullList()
    {
        $where = " WHERE lecture_id=" . $this->lecture_id . " AND year=" . $this->schoolyear;
        return $this->getList('*', $where, 'group_id');
    }

    /* Assign POST variables to internal variables of this class and
        remove evil tags where applicable. We shall probably also remove
        evil attributes et cetera, but this will be done later if ever. */
    function processPostVars()
    {
        assignPostIfExists($this->id, $this->rs, "id");
        assignPostIfExists($this->lecture_id, $this->rs, "lecture_id");
        assignPostIfExists($this->group_id, $this->rs, "group_id");
    }

    function assignFull()
    {
        $rs = $this->getFullList();
        $this->assign('lgrpList', $rs);
        return $rs;
    }

    function assignSingle()
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
        $this->_smarty->assign('lgrp', $this->rs);
    }

    function assignGroupsAndLabtasks()
    {
        $lgrpList = $this->assignFull();

        /* Fetch also a list of labtasks for every labtask group. */
        $lgrpsecBean = new LabtaskGroupSectionBean (NULL, $this->_smarty, NULL, NULL);
        $lgrpsecBean->assignLabtasksForGroups($lgrpList);

        return $lgrpList;
    }

    /* -------------------------------------------------------------------
       HANDLER: ADMIN

       List all the labtask groups that have been defined for a lecture.
       The `id` is the lecture_id.
       ------------------------------------------------------------------- */
    function doAdmin()
    {
        $this->assignGroupsAndLabtasks();
    }

    /* -------------------------------------------------------------------
       HANDLER: EDIT

       The `id` is the labtask group id.
       ------------------------------------------------------------------- */
    function doEdit()
    {
        /* Publish the information about the edited labtask group */
        $this->assignSingle();
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
    }
}

?>
