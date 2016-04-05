<?php
/**
 * Group mapping of laboratory tasks to a laboratory task group.
 *
 * This class implements mapping of a laboratory task group, denoted by an integer id, to a series of
 * laboratory tasks, identified as section ids.
 *
 * The `id` of the bean will be the id of the laboratory task group.
 *
 * (c) 2013 Jan Přikryl
 */

class LabtaskGroupSectionBean extends DatabaseBean
{
	protected $labtaskList; /**< Array of section identifiers */

	function _setDefaults ()
	{
		$this->labtaskList = array ();
        /* Update $this->rs */
        $this->_update_rs();
	}
	
	/* Constructor */
	function __construct ( $id, &$smarty, $action, $object )
	{
		/* Call parent's constructor first */
		parent::__construct ( $id, $smarty, "lgrp_sec", $action, $object );
        /* Initialise defaults. */
        $this->_setDefaults();
	}
	
	function dbReplace ()
	{
        /* Delete all existing mappings labtask-section. We will re-create
         * those that are valid again. */
        DatabaseBean::dbQuery (
            "DELETE FROM lgrp_sec WHERE lgrp_id=" . $this->id
            );
        /* Now create all the mappings again, possibly adding some new ones. */
        foreach ( $this->labtaskList as $section_id )
        {
            DatabaseBean::dbQuery (
                "REPLACE lgrp_sec VALUES ("
                . $this->id . ","
                . $section_id . ")"
            );
        }
	}
	
	/* Assign POST variables to internal variables of this class and
	   remove evil tags where applicable. We shall probably also remove
	   evil attributes et cetera, but this will be done later if ever. */
	function processPostVars ()
	{
		$this->labtaskList = $_POST['labtask'];
	}
	
    function getLabtaskListAsKeys ()
    {
        $rs = DatabaseBean::dbQuery (
            "SELECT section_id FROM lgrp_sec WHERE lgrp_id=" . $this->id );

        $labtaskList = array();
        if ( isset ( $rs ))
        {
            foreach ( $rs as $val )
            {
                $labtaskList[$val['section_id']]=1;
            }
        }
        return $labtaskList;
    }

    function getLabtasksForGroups ( $lgrpList )
    {
        $grpIds = array2ToDBString( $lgrpList, 'id' );
        $rs = DatabaseBean::dbQuery (
            "SELECT * FROM lgrp_sec LEFT JOIN section ON id=section_id " .
            "WHERE lgrp_id IN (" . $grpIds . ")" .
            "ORDER BY ival1"
             );

        $labtaskList = array();
        if ( isset ( $rs ))
        {
            foreach ( $rs as $val )
            {
                $grpId = $val['lgrp_id'];
                if ( !array_key_exists( $grpId, $labtaskList )) $labtaskList[$grpId] = array();
                $labtaskList[$grpId][]=$val;
            }
        }
        return $labtaskList;
    }

    /* Assign to Smarty variable `labtaskList` a list of laboratory tasks
       that form particular labtask groups. The input list are id values
       from `lgrp_sec` table.
    */
    function assignLabtasksForGroups ( $lgrpList )
    {
        $labtaskList = $this->getLabtasksForGroups( $lgrpList );
        $this->assign ( "labtaskList", $labtaskList );
        return $labtaskList;
    }

	/* -------------------------------------------------------------------
	   HANDLER: EDIT
	   ------------------------------------------------------------------- */
	function doEdit ()
	{
        /* The current id is the id of the labtask group. Query an instance of
           LabtaskGroupBean for the information about the group and make the
           information available to the templating engine. */
		$lgrpBean = new LabtaskGroupBean ( $this->id, $this->_smarty, NULL, NULL );
		$lgrpBean->assignSingle();

        /* Fetch a list of labtasks (as section ids) that already belong to
           this labtask group. The list will be stored as $key => $key pairs
           to make the search for particular section id faster. */
        $groupLabList = $this->getLabtaskListAsKeys();

        /* Now fetch the list of all lab tasks that are stored in the `section`
           table. */
        $sectionBean = new SectionBean ( NULL, $this->_smarty, NULL, NULL );
        $labSectionList = $sectionBean->getLabList ( SessionDataBean::getLectureId(), $this->labtaskList );

        /* Augment the $labList with information about labtasks that already
           belong to this group. */
        foreach ( $labSectionList as $key => $lab )
        {
            if ( array_key_exists ( $lab['id'], $groupLabList ))
            {
                $labSectionList[$key]['checked'] = ' checked="checked"';
            }
        }

        $this->_smarty->assign ( 'labList', $labSectionList );
	}
	
	/* -------------------------------------------------------------------
	   HANDLER: SAVE
	   ------------------------------------------------------------------- */
	function doSave ()
	{
		/* Assign POST variables to internal variables of this class and
		   remove evil tags where applicable. */
		$this->processPostVars ();
		/* Update all the records. */
		$this->dbReplace ();

        /* The current id is the id of the labtask group. Query an instance of
           LabtaskGroupBean for the information about the group and make the
           information available to the templating engine. */
        $lgrpBean = new LabtaskGroupBean ( $this->id, $this->_smarty, NULL, NULL );
        $lgrpBean->assignSingle();
    }
}
?>
