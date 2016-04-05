<?php

class ExcersiseBean extends DatabaseBean
{
	var $day;
	var $from;
	var $to;
	var $room;
	var $lecture_id;
	var $lecturer_id;
	
	function _setDefaults ()
	{
		$this->day         = 0;
		$this->from        = 0;
		$this->to          = 0;
		$this->room        = "";
		$this->lecture_id  = 0;
		$this->lecturer_id = 0;
        $this->_update_rs();
	}
	
	/* Constructor */
	function __construct ( $id, &$smarty, $action, $object )
	{
		/* Call parent's constructor first */
		parent::__construct ( $id, $smarty, "excersise", $action, $object );
        /* And initialise new object properties. */
        $this->_setDefaults();
	}
	
	function dbReplace ()
	{
		DatabaseBean::dbQuery (
		  "REPLACE excersise VALUES ("
		  . $this->id . ",'"
		  . $this->day . "','"
		  . mysql_escape_string ( $this->from ) . "','"
		  . mysql_escape_string ( $this->to ) . "','"
		  . mysql_escape_string ( $this->room ) . "','"
		  . $this->lecture_id . "','"
		  . $this->lecturer_id . "','"
		  . mysql_escape_string ( $this->schoolyear ) . "')"
		  );
	}
	
	function dbQuerySingle($alt_id=0)
	{
		DatabaseBean::dbQuerySingle($alt_id);
		
		$this->day         = $this->rs['day'] = numToDay ( $this->rs['day'] );
		$this->from        = $this->rs['from'];
		$this->to          = $this->rs['to'];
		$this->room        = $this->rs['room'];
		$this->lecture_id  = $this->rs['lecture_id'];
		$this->lecturer_id = $this->rs['lecturer_id'];
		$this->schoolyear  = $this->rs['schoolyear'] = $this->rs['year'];
	}
	
	/* Assign POST variables to internal variables of this class and
	   remove evil tags where applicable. We shall probably also remove
	   evil attributes et cetera, but this will be done later if ever. */
	function processPostVars ()
	{
		$this->id          = $_POST['id'];
		$this->day         = trimStrip ( $_POST['day'] );
		$this->from        = trimStrip ( $_POST['from'] );
		$this->to          = trimStrip ( $_POST['to'] );
		$this->room        = trimStrip ( $_POST['room'] );
		$this->lecture_id  = trimStrip ( $_POST['lecture_id'] );
		$this->lecturer_id = trimStrip ( $_POST['lecturer_id'] );
		$this->schoolyear  = trimStrip ( $_POST['schoolyear'] );
	}
    
    /**
     * Process parameters supplied as GET part of the request.
     */
    function processGetVars ()
    {
    	assignGetIfExists($this->displayNames, $this->rs, 'displaynames');
    }
	
	function _dbQueryFullList ( $where )
	{
	    return DatabaseBean::dbQuery (
			"SELECT * FROM excersise" 
			. $where
			. " ORDER BY `day`, `from`"
			);
	}
	
	function _getFullList ( $where = '' )
	{
		$rs = $this->_dbQueryFullList ( $where );
		if ( isset ( $rs ))
		{
			foreach ( $rs as $key => $val )
			{
				$rs[$key]['day'] = numToDay ( $val['day'] );
			}
		}
		return $rs;
	}

	function getSelectMap ( $lectureId = 0, $schoolYear = 0 )
	{
		$where = $this->_lectureIdToWhereClause ( $lectureId, $schoolYear );
	    $rs = $this->_dbQueryFullList ( $where );
        
		$excersiseMap = array ();
		if ( $lectureId == 0 )
        {
            $excersiseMap[0] = "Vyberte ze seznamu ...";
        }
		if ( isset ( $rs ))
		{
    		foreach ( $rs as $key => $val )
	    	{
				$eId = $val['id'];
				$room = $val['room'];
				$day = numToDay ( $val['day'] );
				$timespan = substr ( $val['from'], 0, -3 ) . "-" . substr ( $val['to'], 0, -3 );
		        $excersiseMap[$eId] = $room . ", " . $day['name'] . ", " . $timespan;
		    }
		}
		
		
		return $excersiseMap;
	}
	
	function assignSelectMap ( $lectureId = 0, $schoolYear = 0 )
	{
		$excersiseMap = $this->getSelectMap ( $lectureId, $schoolYear );
		$this->_smarty->assign ( 'excersiseSelect', $excersiseMap );
		return $excersiseMap;
	}
	
	function prepareExcersiseData ( $sortType )
	{
		/* Check if there are some data. */
		if ( empty ( $this->rs['id'] ))
		{
			/* Nope, the id references a nonexistent excersise. */
			$this->action = "err_01x";
			return;
		}
		
        /* Now we know that the excersise record really exists, but we have
           to make ourselves sure that the excersise balongs to the currently
           active school year. */
        if ( $this->rs['year'] != $this->schoolyear )
        {
            /* Nope, wrong schoolyear. */
            $this->action = "e_year";
            return;
        }
    
		/* The function above sets $this->rs to values that shall be
		   displayed. By assigning $this->rs to Smarty variable 'excersise'
		   we can fill the values of $this->rs into a template. */
		$this->_smarty->assign ( 'excersise', $this->rs);
		
		/* Get the lecture data. */
		$lectureBean = new LectureBean ($this->lecture_id, $this->_smarty, "x", "x");
		$lectureBean->assignSingle ();
		
		/* Get the lecturer data. */
		$lecturerBean = new LecturerBean ($this->lecturer_id, $this->_smarty, "x", "x");
		$lecturerBean->assignSingle ();
		
		/* Get the list of students for this excersise. The list will contain
		   only student IDs. */
		$studentExcersiseBean = new StudentExcersiseBean (0, $this->_smarty, "x", "x");
		$studentList = $studentExcersiseBean->getStudentListForExcersise ( $this->id );
		
		/* Fetch the evaluation scheme from the database.
		   @TODO@ Allow for more than one scheme.
		   @TODO@ Allow for more than one lecture !!!!! */
		$evaluationBean = new EvaluationBean (0, $this->_smarty, "x", "x");
		
    	/* This will initialise EvaluationBean with the most recent evaluation
           scheme for lecture given by $this->lecture_id. The function returns
           'true' if the bean has been initialised. */
		$ret = $evaluationBean->initialiseFor ( $this->lecture_id, $this->schoolyear );
		
		/* Check the initialisation status. */
		if ( ! $ret )
		{
			/* Nope, the id references a nonexistent evaluation. */
			$this->action = "e_inval";
			return;
		}
		
		/* Get the list of tasks for evaluation of this excersise. The list will contain
		   only task IDs and we will have to fetch task and subtask information
		   by ourselves later. */
		$taskList = $evaluationBean->getTaskList ();
		
		/* Fetch a verbose list of tasks. */
		$taskBean = new TaskBean (0, $this->_smarty, "x", "x");
		
		/* This will both create a full list of tasks corresponding to the
		   evaluation scheme and assing this list to the Smarty variable
		   'taskList'. */
		$fullTaskList = $taskBean->assignFullTaskList ( $taskList );
		
		/* Fetch a verbose list of subtasks. */
		$subtaskBean = new SubtaskBean (0, $this->_smarty, "x", "x");
		/* This will both create a full list of subtasks corresponding to the
		   tasks of the chosen evaluation scheme and assign this list to the
		   Smarty variable 'subtaskList'. */
		$tsBean = new TaskSubtasksBean ( 0, $this->_smarty, '', '' );
		$subtaskMap  = $tsBean->getSubtaskMapForTaskList ( $taskList, $this->schoolyear );
		$subtaskList = $tsBean->getSubtaskListFromSubtaskMap ( $subtaskMap );
		
		$subtaskBean = new SubtaskBean (0, $this->_smarty, "x", "x");
		$fullSubtaskList = $subtaskBean->assignFullSubtaskList ( $subtaskList );
		
		/* If there are any students in $studentList, get their points. */
		if ( count ( $studentList ) > 0 )
		{
			$pointsBean = new PointsBean ( 0, $this->_smarty, '', '' );
			$points = $pointsBean->getPoints (
                $studentList, $subtaskList, $this->schoolyear );
		
			/* Generate a verbose list of students based on
		       the ID list we got above. Combine this list with the points students
		       achieved. */
		  	$studentBean = new StudentBean (0, $this->_smarty, "x", "x");
		  	$studentBean->assignStudentDataFromList (
				$studentList, $points, $evaluationBean, $subtaskMap,
				$fullSubtaskList, $fullTaskList,
				SB_STUDENT_ANY, $sortType,
                $this->lecture_id );
    	}
	}
	
    /**
     * Return an array of lecturer ids for excersises of the given
     * lecture.
     */
    function getExcersiseLecturersForLecture ( $lectureId = 0 )
    {
    	return $this->dbQuery ( 
            "SELECT lecturer_id FROM excersise " .
            "WHERE lecture_id=" . $lectureId . " " .
            "GROUP BY lecturer_id"
            );
    }
    
    /**
     * Return a list of excersises for the given lecture id.
     */
	function getExcersisesForLecture ( $lectureId = 0, $schoolYear = 0 )
    {
        $where = $this->_lectureIdToWhereClause ( $lectureId, $schoolYear );
        return $this->_getFullList ( $where );
    }
    
    function getFull ( $lectureId = 0, $schoolYear = 0 )
	{
		/* Get the list of excersises for the lecture. */
        $rs = $this->getExcersisesForLecture ( $lectureId, $schoolYear );
        
        /* Get the lecturer map. */
	    $lecturerBean = new LecturerBean ($this->lecturer_id, $this->_smarty, "x", "x");
		$lecturerMap = $lecturerBean->dbQueryLecturerMap ();
		
		if ( isset ( $rs ))
		{
			foreach ( $rs as $key => $val )
			{
				$rs[$key]['lecturer'] = $lecturerMap[$val['lecturer_id']];
			}
		}
		
		return $rs;
	}
	
	function assignFull ( $lectureId = 0, $schoolYear = 0 )
    {
        $rs = $this->getFull ( $lectureId, $schoolYear );
        $this->_smarty->assign ( 'excersiseList', $rs );
        return $rs;
    }

    function assignSingle ()
    {
		/* Just fetch the data of the user to be deleted and ask for
		   confirmation. */
		$this->dbQuerySingle ();
		$this->_smarty->assign ( 'excersise', $this->rs );
		
		/* Get the information about the lecture we are listing excersises
		   for ... */
		$lectureBean = new LectureBean ($this->lecture_id, $this->_smarty, "x", "x");
		$lectureBean->assignSingle ();
		
		/* Get the lecturer data. */
	    $lecturerBean = new LecturerBean ($this->lecturer_id, $this->_smarty, "x", "x");
		$lecturerBean->assignSingle ();
	}
	
	/* -------------------------------------------------------------------
	   HANDLER: STUDENT LIST
	   ------------------------------------------------------------------- */
	function doStudentList ( $sort )
	{
		$this->prepareExcersiseData ( $sort );
	}
	
	/* -------------------------------------------------------------------
	   HANDLER: SHOW
	   ------------------------------------------------------------------- */
	function doShow ()
	{
        /* Query data of this excersise. */
        $this->dbQuerySingle ();
        
        /* Check if there were some parameters passed as variables. */
        $this->processGetVars();
        
        /* Default sort type is by encoded used ids. */
        $sortType = SB_SORT_BY_ID;
        
        /* But in case that we shall display true names of students, we
           shall also sort by surname and not by the id. */
        if ( $this->displayNames )
        {
            /* Fetch the information about current role of the user. */
            $role = SessionDataBean::getUserRole();
            /* Names can be displayed only to users with role USR_LECTURER
               and above. */
            if ( UserBean::isRoleAtLeast ( $role, USR_LECTURER ))
            {
        	   $sortType = SB_SORT_BY_NAME;
            }
            else
            {
            	/* In case that the user does not have the permission
                   to display the list of student names, we will display
                   the normal list. For this, however, we will have to
                   reset the displayNames property. */
                $this->displayNames = false;
                $this->rs['displaynames'] = false; 
            }
        }
        
        /* Process the queried data. */
		$this->prepareExcersiseData ( $sortType );
		
		/* Get all active news for this excersise. That is, ignore all news
		   for the lecture (these will be displayed somewhere else), get
		   all active lecturer news for this lecturer, get all active news
		   entered especially for this excersise, and get all active news
		   for excersises to the lecture. */
        $newsBean = new NewsBean ( 0, $this->_smarty, "x", "x");
		$newsBean->assignNewsForTypes ( 0, $this->lecturer_id, $this->id, $this->lecture_id );
	}
	
	/* -------------------------------------------------------------------
	   HANDLER: SAVE
	   ------------------------------------------------------------------- */
	function doSave ()
	{
		/* Assign POST variables to internal variables of this class and
		   remove evil tags where applicable. */
		$this->processPostVars ();
		/* Update the record, but do not update the password. */
		$this->dbReplace ();
		/* Saving can occur only in admin mode. Now that we have saved the
		   data, return to the admin view by calling the appropriate action
		   handler. Admin mode expects to have the value of $this->id set
		   to lecture_id.
		*/
		$this->id = $this->lecture_id;
		$this->doAdmin ();
	}
	
	/* -------------------------------------------------------------------
	   HANDLER: DELETE
	   ------------------------------------------------------------------- */
	function doDelete ()
	{
		$this->assignSingle ();
	}
	
	/* -------------------------------------------------------------------
	   HANDLER: REAL DELETE
	   ------------------------------------------------------------------- */
	function doRealDelete ()
	{
		$this->assignSingle ();
		/* Delete the record */
		DatabaseBean::dbDeleteById ();
	}
	
	/* -------------------------------------------------------------------
	   HANDLER: ADMIN
	   ------------------------------------------------------------------- */
	function doAdmin ()
	{
		/* Get the information about the lecture we are listing excersises
		   for ... */
		$lectureBean = new LectureBean ($this->id, $this->_smarty, "", "");
		$lectureBean->assignSingle ();
		
        /* Get the current shool year. */
        $this->year = SessionDataBean::getSchoolYear();
        
		/* Get the list of all excersises for the given lecture id and the
           current school year. */
	    $this->assignFull ( $this->id, $this->year );
		
		/* It could have been that doAdmin() has been called from another
		   handler. Change the action to "admin" so that ctrl.php will
		   know that it shall display the scriptlet for section.admin */
		$this->action = "admin";
	}
	
	/* -------------------------------------------------------------------
	   HANDLER: EDIT
	   ------------------------------------------------------------------- */
	function doEdit ()
	{
		/* If id == 0, we shall create a new record. */
		if ( $this->id )
		{
			/* Query data of this person. */
			$this->dbQuerySingle ();
		}
		else
		{
			/* Initialize default values. */
			$this->_setDefaults ();
			assignGetIfExists ( $this->lecture_id, $this->rs, 'lecture_id' );
		}
		/* Both above functions set $this->rs to values that shall be
		   displayed. By assigning $this->rs to Smarty variable 'user'
		   we can fill the values of $this->rs into a template. */
		$this->_smarty->assign ( 'excersise', $this->rs );
		
		/* Get a list of lectures. */
		$lectureBean = new LectureBean ( 0, $this->_smarty, "", "" );
		$lectureBean->assignSelectMap ();
		
		/* Get a list of lecturers. */
		$lecturerBean = new LecturerBean ( 0, $this->_smarty, "", "" );
		$lecturerBean->assignSelectMap ();
		
		/* Get a map of schoolyears. */
		//$this->assignYearMap ();
	}
}
?>
