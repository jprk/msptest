<?php

class LabtaskBean extends DatabaseBean
{
    protected $labtaskList;

    function _setDefaults()
    {
        $this->labtaskList = array();
        /* Update $this->rs */
        $this->_update_rs();
    }

    /* Constructor */
    function __construct($id, &$smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, "labtask", $action, $object);
    }

    function dbReplace()
    {
        /* Delete all existing mappings labtask-schoolyear. We will re-create
         * those that are valid again. */
        DatabaseBean::dbQuery(
            "DELETE FROM labtask WHERE year=" . $this->schoolyear . " AND lecture_id=" . $this->id
        );
        foreach ($this->labtaskList as $labtask_id)
        {
            DatabaseBean::dbQuery(
                "REPLACE labtask VALUES ("
                . $this->id . ","
                . $this->schoolyear . ","
                . $labtask_id . ")"
            );
        }
    }

    /* Assign POST variables to internal variables of this class and
       remove evil tags where applicable. We shall probably also remove
       evil attributes et cetera, but this will be done later if ever. */
    function processPostVars()
    {
        $this->labtaskList = $_POST['labtask'];
    }

    function getLabtaskList()
    {
        $rs = DatabaseBean::dbQuery(
            "SELECT section_id FROM labtask WHERE year=" . $this->schoolyear . " AND lecture_id=" . $this->id);

        $labtaskList = array();
        if (isset ($rs))
        {
            foreach ($rs as $val)
            {
                $labtaskList[] = $val['section_id'];
            }
        }
        return $labtaskList;
    }

    function getLabtaskListAsKeys()
    {
        $rs = DatabaseBean::dbQuery(
            "SELECT section_id FROM labtask WHERE year=" . $this->schoolyear . " AND lecture_id=" . $this->id);

        $labtaskList = array();
        if (isset ($rs))
        {
            foreach ($rs as $val)
            {
                $labtaskList[$val['section_id']] = 1;
            }
        }
        return $labtaskList;
    }

    function getActiveLabtaskList()
    {
        $labtaskList = $this->getLabtaskList();
        $sectionBean = new SectionBean (NULL, $this->_smarty, NULL, NULL);
        return $sectionBean->getLabList($this->id, $labtaskList);
    }

    function getActiveLabtaskSelectionList()
    {
        $labtaskList = $this->getLabtaskList();
        $sectionBean = new SectionBean (NULL, $this->_smarty, NULL, NULL);
        return $sectionBean->getLabSelectionList($this->id, $labtaskList);
    }

    function assignFull()
    {
    }

    /* -------------------------------------------------------------------
       HANDLER: EDIT
       ------------------------------------------------------------------- */
    function doEdit()
    {
        /* The current id is the id of the lecture. Query an instance of
           SectionBean for the list of all sections of type `lab`. */
        $sectionBean = new SectionBean ($this->id, $this->_smarty, NULL, NULL);
        $labSectionList = $sectionBean->getLabList($this->id);

        /* Now fetch the list of all lab section ids that were marked as active
           in this schoolyear. */
        $activeLabList = $this->getLabtaskListAsKeys();

        /* Now update the `labSectionList` elements with the information from
           `activeLabList`. */
        foreach ($labSectionList as $key => $lab)
        {
            if (array_key_exists($lab['id'], $activeLabList))
            {
                $labSectionList[$key]['checked'] = ' checked="checked"';
            }
        }
        $this->_smarty->assign('labtaskList', $labSectionList);
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
