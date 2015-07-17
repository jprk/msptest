<?php
/**
 * Interface to replacement exercises and labs (used for physics labs at the moment).
 *
 * For explanation of the logic governing the `passed` and `failed` flags, see the method getStudentList().
 *
 * (c) Jan Prikryl, 2012,2014
 */
class ExcersiseRepBookingBean extends DatabaseBean
{
	/* Maximum number of replacement exercises for a single student. */
	const MAX_BOOKINGS = 5;
    /* Limit the possibility to cancel the booking to some minutes before start. */
    const CANCEL_INTERVAL = 15;

    const NOSHOW  = 0; /**> Booked, but did not show up. */
    const EXCUSED = 1; /**> Booked and excused themselves for not showing up. */
    const PASSED = 2; /**> Arrived and passed the test. */
    const FAILED  = 3; /**> Arrived but did not pass the test. */

    const SESSION_LGRPID_KEY = 'lgrpid';

    /* Booking type to display */
    const BOOKING_ALL = 0;      /**> All bookings for a student. */
    const BOOKING_NOSHOW = 1;   /**> Only failed replacements (no show) */
    const BOOKING_FAILED = 2;   /**> Only failed tests */

    /* Static project resource identifier for ftok().
       Has to be a single character. */
    static private $projectId = 'p';

    private $lgrpId;
    private $replId;
    private $datefrom;
    private $replstatus;
    private $bookingtype;

    /* Constructor */
	function __construct ( $id, &$smarty, $action, $object )
	{
		/* Call parent's constructor first */
		parent::__construct( $id, $smarty, 'repl_stud', $action, $object );
		/* Update internals. */
		//self::_setDefaults();
        $this->bookingtype = self::NOSHOW;
    }
	
	function dbInsert ( $repl_id, $student_id, $lgrp_id )
	{
		$this->dbQuery ( 
				"INSERT INTO repl_stud VALUES (" .
				$repl_id . "," .
                $student_id . "," .
                $lgrp_id .
                ",NOW(),NULL,FALSE,FALSE)"
				);
	}

    function dbReplace ( $repl_id, $student_id, $datefrom, $dateto, $passed, $failed )
    {
        $this->dbQuery (
            "UPDATE repl_stud SET dateto=" . $dateto . ", passed=" . intval($passed) . ", failed=" . intval($failed) .
            " WHERE " .
            "replacement_id=" . $repl_id . " AND " .
            "student_id=" . $student_id . " AND " .
            "datefrom='" . $datefrom . "'" );
    }

	function dbQueryFullList ( $where )
	{
	}

    function hasSessionLgrpId ()
    {
        return array_key_exists ( self::SESSION_LGRPID_KEY, $_SESSION );
    }

    function sessionPushLgrpId()
    {
        $_SESSION[self::SESSION_LGRPID_KEY] = $this->lgrpId;
    }

    function sessionPopLgrpId()
    {
        if ( $this->hasSessionLgrpId ())
        {
            $this->lgrpId = $_SESSION[self::SESSION_LGRPID_KEY];
            unset ( $_SESSION[self::SESSION_LGRPID_KEY] );
        }
        else
        {
            $this->lgrpId = NULL;
        }
    }
    /**
     * Get the booking information for replacement defined by replacement id and
     * booking date.
     */
    function getBooking ()
    {
        /* To query about booking makes sense only in case that `replId` and `datefrom`
           have a meaningful value. Check it. */
        if ( empty ( $this->replId ) || empty ( $this->datefrom ))
        {
            return NULL;
        }

        /* Get the information about the lecture we are listing excersises for ... */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, NULL, NULL);
        $lectureBean->assignSingle ();

        /* Retrieve parameters of the current term. */
        $termDates  = SchoolYearBean::getTermDates  ( $this->schoolyear, $lectureBean->getTerm() );

        $booking = $this->getBookedReplacements ( $termDates,
            " AND rd.id=" . $this->replId . " AND rs.datefrom='" . $this->datefrom . "'" );

        /* The count of resulting elements should be 1. */
        if ( count ( $booking ) > 1 )
        {
            throw new Exception ( "More then one booking for id=" . $this->replId .
                                  " and datefrom='" . $this->datefrom . "'!" );
        }

        /* In case there is a booking, the entry will be the first element of a one
           element list (it is in fact a resultset). */
        if ( ! empty ( $booking )) $booking = $booking[0];

        return $booking;
    }

    function getBookedReplacements ( $termLimits, $andWhere = "" )
    {
        $this->dumpVar ( 'termLimits', $termLimits );
        $this->dumpVar ( 'andWhere', $andWhere );
        /*
         return $this->dbQuery (
            "SELECT * FROM repl_stud WHERE " .
            "student_id=" . SessionDataBean::getUserId() . " AND " .
            "dateTo IS NULL ORDER BY dateFrom ASC"
         );
          */
        /*
         * SELECT NOW(),date,IFNULL(rd.mfrom,ex.from) AS `from`, ADDTIME(date,IFNULL(rd.mfrom,ex.from))-INTERVAL 15 MINUTE, ADDTIME(date,IFNULL(rd.mfrom,ex.from))-INTERVAL 15 MINUTE > NOW() AS candelete FROM replacement_dates AS rd LEFT JOIN excersise AS ex ON rd.excersise_id=ex.id
         * SELECT NOW(),date,@ftime:=IFNULL(rd.mfrom,ex.from) AS `from`, @fromtime:=ADDTIME(date,@ftime)-INTERVAL 15 MINUTE AS `fromtime`, @fromtime > NOW() AS candelete FROM replacement_dates AS rd LEFT JOIN excersise AS ex ON rd.excersise_id=ex.id
         */
        return $this->dbQuery (
            "SELECT replacement_id, date, @ftime:=IFNULL( rd.mfrom, ex.from ) AS `from`, IFNULL( rd.mto, ex.to ) AS `to`, " .
            "@fromtime:=ADDTIME(date,@ftime)-INTERVAL " . self::CANCEL_INTERVAL . " MINUTE AS `fromtime`, (@fromtime > NOW() AND dateto IS NULL) AS candelete, " .
            "ex.room AS `room`, surname, firstname, datefrom, dateto, passed, failed, IFNULL(group_id,'??') AS `grpid` FROM repl_stud AS rs " .
            "LEFT JOIN replacement_dates AS rd ON rs.replacement_id = rd.id " .
            "LEFT JOIN excersise AS ex ON rd.excersise_id = ex.id " .
            "LEFT JOIN labtask_group AS lg ON rs.lgrp_id = lg.id " .
            "LEFT JOIN lecturer AS le ON ex.lecturer_id = le.id " .
            "WHERE rs.student_id=" . SessionDataBean::getUserId() . " AND " .
            "date>='" . $termLimits['from'] . "' AND date<'" . $termLimits['to'] . "'" . $andWhere . " " .
            "ORDER BY date,IFNULL( rd.mfrom, ex.from )" );
    }

    function hasThisLabBooked ( $replacementList, $studentId )
    {
        $replIds = array2ToDBString ( $replacementList, 'id' );
        /*
           Conditions for having a lab booked:
           - it is a replacement active in this semester
           - the current time is before the lab starts ... NOW()<ADDTIME ...
           - the booking has not been canceled ... rs.dateto

           The value of rs.datefrom is irrelevant.
        */
        $rs = $this->dbQuery(
            "SELECT * FROM repl_stud AS rs " .
            "LEFT JOIN replacement_dates AS rd ON rs.replacement_id=rd.id " .
            "LEFT JOIN excersise AS ex ON rd.excersise_id=ex.id WHERE " .
            "rd.id IN (" . $replIds . ") AND " .
            "rs.lgrp_id=" . $this->lgrpId . " AND " .
            "rs.student_id=" . $studentId . " AND " .
            "NOW()<ADDTIME(rd.date,IFNULL(rd.mfrom,ex.from)) AND " .
            "rs.dateto IS NULL"
        );

        $this->dumpVar( 'rs', $rs );

        if ( ! empty ( $rs ))
        {
            $this->_smarty->assign ( 'booked', $rs[0] );
            return true;
        }

        return false;
    }

    function removeBookedGroups ( $replacementList, $userId )
    {
        /* Extract the list of replacement identifiers from the $replacementList. */
        $replIds = array2ToDBString ( $replacementList, 'id' );
        //$this->dumpVar( 'replacementList', $replacementList );

        /* Select all replacements identifiers
           - that this student has booked and not cancelled,
           - where other students have booked out the same labtask group and
             not cancelled their booking. */
        $rs = $this->dbQuery(
            "SELECT replacement_id FROM repl_stud WHERE " .
            "replacement_id IN (" . $replIds . ") AND " .
            "dateto IS NULL AND " .
            "( lgrp_id=" . $this->lgrpId . " OR " .
            "student_id=" . $userId . " )"
        );

        /* Convert the result into an array indexed by `replacement_id`. */
        $booked = resultsetToIndexKey ( $rs, 'replacement_id' );
        $this->dumpVar( 'booked', $booked );
        $this->dumpVar( 'replacementList1', $replacementList );

        /* Now loop over the input list of replacement exercises and delete
           those that
           - already have this student on them,
           - do not have the desired labtask group free,
           - are completely booked out. */
        foreach ( $replacementList as $key => $val )
        {
            /* Extract the id of the replacement. */
            $id = $val['id'];
            /* Default is to offer all lab experiments, but some of them
               may have been already booked out. */
            if ( array_key_exists ( $id, $booked ) || $val['avail_count'] == 0 )
            {
                /* The $booked[id] exists and hence this particular
                   replacement exercise it not available to this student
                   (either the student has already booked some other labtask
                   group or the desired labtask group is not free). We will
                   therefore remove this replacement exercise from the list of
                   replacements available for the given lab task.
                   Another possibility is that this replacement exercise has
                   no more free slots. */
                unset ( $replacementList[$key] );
            }
        }

        /* Some indices were deleted so we shall create a new zero-based
           list of keys, otherwise Smarty will go bonkers ... */
        $replacementList = array_values ( $replacementList );

        $this->dumpVar( 'replacementList2', $replacementList );
        return $replacementList;
    }

    function getBookingsCount ( $replacementList, $studentId  )
    {
        $replIds = array2ToDBString ( $replacementList, 'id' );
        $rs = $this->dbQuery(
            "SELECT COUNT(*) AS count FROM repl_stud WHERE " .
            "replacement_id IN (" . $replIds . ") AND " .
            "student_id=" . $studentId . " AND dateto IS NULL"
        );
        $this->dumpVar( 'rs', $rs );

        return intval($rs[0]['count']);
    }

    /**
     * Find out whether a lab can be booked for an replacement excersise.
     *
     * The function operates on internal parameters of this object.
     * It expects to have `replId` and `labId` set to appropriate values.
     *
     * @return bool True if booking is possible.
     */
    function isNotBooked ()
    {
        $rs = $this->dbQuery (
            "SELECT * FROM repl_stud WHERE " .
            "replacement_id=" . $this->replId . " AND " .
            "lgrp_id=" . $this->lgrpId . " AND " .
            "dateto IS NULL" );
        return empty ( $rs );
    }

    function getStudentList ()
    {
        /* The flag indicating a valid excuse from the replacement exercise is set by having `dateto` filed set to
           a later date than the date that allows automatic deletion of an existing booking (currently it is
           `self::CANCEL_INTERVAL` minutes, see the last line of the SQL command. */
        $studentList = $this->dbQuery (
            "SELECT rs.*,st.* FROM repl_stud AS rs " .
            "LEFT JOIN student AS st ON rs.student_id=st.id " .
            "LEFT JOIN replacement_dates AS rd ON rs.replacement_id = rd.id " .
            "LEFT JOIN excersise AS ex ON rd.excersise_id = ex.id " .
            "WHERE rd.id=" . $this->replId . " AND " .
            "(rs.dateto IS NULL OR " .
            "rs.dateto>(ADDTIME(rd.date,IFNULL(rd.mfrom,ex.from))-INTERVAL " . self::CANCEL_INTERVAL . " MINUTE)) "
            );
        /* Loop over the list and decide which of the four radio inputs
           shall be checked. */
        if ( ! empty ( $studentList ))
        {
            foreach ( $studentList as $key => $val )
            {
                if ( $val['passed'] )
                {
                    /* This student really attended the replacement exercise and passed the admission test. */
                    $studentList[$key]['passed'] = ' checked="checked"';
                    $studentList[$key]['failed'] = '';
                }
                elseif ( $val['failed'] )
                {
                    /* This student really attended the replacement exercise, but did not pass the admission test. */
                    $studentList[$key]['failed'] = ' checked="checked"';
                    $studentList[$key]['passed'] = '';
                }
                else
                {
                    /* These are students that for some reason did not attend the exercise. */
                    $studentList[$key]['failed'] = '';
                    $studentList[$key]['passed'] = '';

                    /* Either they simply did not show up or they excused themselves beforehand. */
                    if ( empty ( $val['dateto'] ))
                    {
                        /* These students did not attend and did not excuse themselves. */
                        $studentList[$key]['noshow'] = ' checked="checked"';
                    }
                    else
                    {
                        /* These students did not attend, but they did excuse themselves. */
                        $studentList[$key]['excused'] = ' checked="checked"';
                    }
                }
            }
        }
        return $studentList;
    }


    function getLabtasksForGroup ( $group_id )
    {
        /* We want to present user also the list of laboratory tasks that
           form the labtask group. This is not as straghtforward as it would
           seem due to our multi-level composition of labtask groups. */
        $lgrpBean = new LabtaskGroupBean ( NULL, $this->_smarty, NULL, NULL );
        $lgseBean = new LabtaskGroupSectionBean ( NULL, $this->_smarty, NULL, NULL );
        /* First step is determining the internal id of the labtask group referenced
           by `grpid` for this lecture and the current schoolyear (the group
           composition may be different every year even for the same lecture). */
        $where = " WHERE lecture_id=" . SessionDataBean::getLectureId() .
            " AND year=" . $this->schoolyear .
            " AND group_id=" . $group_id;
        $lgidList = $lgrpBean->getList ( 'id', $where );
        /* Now we have it so we can actually determine which sections of type
           'labtask' it refers to. These sections contain in their variable
           `ival` the proper number of the laboratory task ... */
        $lgrpList = $lgseBean->getLabtasksForGroups($lgidList);

        return current($lgrpList);
    }

    private function getStudentBookings ( $replacementList, $type=self::BOOKING_ALL )
    {
        $failedBookingPart = "";
        if ( $type == self::BOOKING_NOSHOW )
            $failedBookingPart = "AND NOW()>ADDTIME(rd.date,IFNULL( rd.mfrom, ex.from )) AND dateto IS NULL AND passed=0 AND failed=0 ";
        elseif ( $type == self::BOOKING_FAILED )
            $failedBookingPart = "AND NOW()>ADDTIME(rd.date,IFNULL( rd.mfrom, ex.from )) AND dateto IS NULL AND failed=1 ";

        $replIds = array2ToDBString ( $replacementList, 'id' );
        $this->dumpVar('replIds',$replIds);

        $failedList = $this->dbQuery (
            "SELECT lg.group_id, rs.*, rd.date, " .
            "st.id, st.login, st.firstname, st.surname, st.yearno, st.groupno, st.email, " .
            "ADDTIME(rd.date,IFNULL( rd.mfrom, ex.from )) AS `fromtime`, " .
            "NOW()>ADDTIME(rd.date,IFNULL( rd.mfrom, ex.from )) AS `finished` " .
            "FROM repl_stud AS rs LEFT JOIN student AS st ON rs.student_id=st.id " .
            "LEFT JOIN replacement_dates AS rd ON rs.replacement_id = rd.id " .
            "LEFT JOIN excersise AS ex ON rd.excersise_id = ex.id " .
            "LEFT JOIN labtask_group AS lg ON rs.lgrp_id = lg.id WHERE " .
            "replacement_id IN(" . $replIds . ") " .
            $failedBookingPart .
            "ORDER BY st.surname,st.firstname,rd.date"
        );
        $this->dumpVar ( 'failedList', $failedList );

        /* Preprocess the list: the output shall be an array of arrays, where the top-level array is indexed by
           distinct students and the lower level array contains the failures. Will be implemented as and array
           of associative arrays holding the resultset elements, with an added element for failures. */
        $student_keys = array_flip ( array ( 'id', 'login', 'firstname', 'surname', 'yearno', 'groupno', 'email' ));
        $failed_keys = array_flip ( array ( 'replacement_id', 'group_id', 'lgrp_id', 'datefrom', 'date', 'fromtime', 'dateto', 'passed', 'failed', 'finished' ));
        /* This is used to identify repeating students in the $failedList. */
        $prev_student_id = NULL;
        $bookings = array();
        $sr = NULL;
        foreach ( $failedList as $student )
        {
            $student_id = $student['id'];
            $sf = array_intersect_key( $student, $failed_keys );
            if ( $student_id != $prev_student_id )
            {
                /* If there is a student to append to the list of bookings, do so. */
                if ( $prev_student_id != NULL ) $bookings[] = $sr;
                /* Remember the new student's id. */
                $prev_student_id = $student_id;
                /* This should create $sr containing the element with keys in $student_keys. */
                $sr = array_intersect_key( $student, $student_keys );
                $sr['numitems'] = 1;
                $sr['replacements'] = array ( $sf );
            }
            else
            {
                /* We have seen the student before. */
                $sr['numitems']++;
                $sr['replacements'][] = $sf;
            }
        }
        /* The last student has to be added here. */
        if ( $prev_student_id != NULL ) $bookings[] = $sr;

        return $bookings;
    }

    function assignStudentBookings ( $replacementList )
    {
        $bookedStudents = $this->getStudentBookings ( $replacementList, self::BOOKING_ALL );
        $this->_smarty->assign ( 'bookedstudents', $bookedStudents );
        return $bookedStudents;
    }

    function assignFailedStudents ( $replacementList )
    {
        $failedStudents = $this->getStudentBookings ( $replacementList, self::BOOKING_NOSHOW );
        $this->_smarty->assign ( 'failstudents', $failedStudents );
        return $failedStudents;
    }

    /**
     * Process parameters supplied as POST part of the request.
     */
    function processPostVars ()
    {
        assignPostIfExists ( $this->replId,     $this->rs, 'replid' );
        assignPostIfExists ( $this->lgrpId,     $this->rs, 'lgrpid' );
        assignPostIfExists ( $this->datefrom,   $this->rs, 'datefrom' );
        assignPostIfExists ( $this->replstatus, $this->rs, 'replstatus' );
    }
    /**
     * Process parameters supplied as GET part of the request.
     */
    function processGetVars ()
    {
        assignGetIfExists ( $this->replId,       $this->rs, 'replid' );
        assignGetIfExists ( $this->datefrom,     $this->rs, 'datefrom' );
        assignGetIfExists ( $this->bookingtype,  $this->rs, 'bookingtype' );
    }


    function doAdmin ()
	{
        /* Process the submitted data. In this case it will be just the id of the lab. */
        $this->processPostVars();

        if ( empty ( $this->replId ))
        {
            /* Information about the lecture we are listing excersises for ... */
            $lectureBean = new LectureBean ( $this->id, $this->_smarty, NULL, NULL );
            $lectureBean->assignSingle ();

            /* Retrieve parameters of the current term. */
            $termDates = SchoolYearBean::getTermLimits ( $this->schoolyear, $lectureBean->getTerm() );

            /* Construct a database bean for accessing replacements. */
            $replBean = new ExcersiseReplacementBean ( $this->id, $this->_smarty, NULL, NULL );

            /* Get the list of possible replacements for this lecture. The list will possibly include
               replacement labs where this  particular lab task has been reserved already. */
            $replacementList = $replBean->getReplacements ( $termDates );

            /* Assign the result to the list of Smarty variables. */
            $this->_smarty->assign ( 'replacements', $replacementList );

            $this->action .= ".replselect";
        }
        else
        {
            /* Construct a database bean for accessing replacements. */
            $replBean = new ExcersiseReplacementBean ( $this->replId, $this->_smarty, NULL, NULL );
            $replBean->assignSingle();

            /* Select a list of students who booked this replacement excersise.
               The list will contain all entries where
                - the replacement_id corresponds to this replacement excersise,
                - the dateto is null or smaller than replacement->datefrom - CANCEL_INTERVAL.
            */
            $studentList = $this->getStudentList ();

            /* In order to identify the booked replacements safely, we need `replacement_id`,
               `student_id` and also `datefrom` timestamp (the same booking of the same
               student could have been cancelled before and without `datefrom` we would
               ruin the database). It would be complicated to pass the `datefrom` record
               to the browser and than to parse the response, it is much easier to use
               the session storage for this. */
            $_SESSION['replstudents'] = resultsetToIndexKey( $studentList, 'id' );

            /* The student list will contain only the database id of the booked labtask group.
               However, we would like to display the information about the booked groups,
               like `group_id` and list of labtasks. We will therefore fetch the definition
               lists of labtask groups. */
            $lgrpBean = new LabtaskGroupBean ( $this->id, $this->_smarty, NULL, NULL );
            $lgrpList = $lgrpBean->assignGroupsAndLabtasks();
            /* This will get $lgrpList indexed by $lgrpList[]['id'] */
            $lgrpList = resultsetToIndexKey( $lgrpList, 'id' );

            $this->assign ( 'lgrpList', $lgrpList );
            $this->assign ( 'replstudents', $studentList );
        }
    }
	
	function doEdit ()
	{
        /* Process the submitted data. In this case it will be just the id of the lab. */
        $this->processPostVars();

        /* Information about the lecture we are listing excersises for ... */
        $lectureBean = new LectureBean ( $this->id, $this->_smarty, NULL, NULL );
        $lectureBean->assignSingle ();

        /* Retrieve parameters of the current term. */
        $termDates = SchoolYearBean::getTermLimits ( $this->schoolyear, $lectureBean->getTerm() );

        /* Construct a database bean for accessing replacements. */
        $replBean = new ExcersiseReplacementBean ( $this->id, $this->_smarty, NULL, NULL );

        /* Construct a database bean for accessing labtask group information. */
        $lgrpBean = new LabtaskGroupBean ( $this->id, $this->_smarty, NULL, NULL );

        /* User selected a labtaskgroup already, but they did not have success in
           booking the replacement for whatever reason. This will display the list
           of possible replacement terms when they return directly to the edit page. */
        if ( empty ( $this->lgrpId ) && $this->hasSessionLgrpId())
        {
            $this->sessionPopLgrpId();
        }

        if ( empty ( $this->lgrpId ))
        {
            /* Get the list of possible replacements for this lecture. The list will possibly include replacement labs where this
               particular lab task has been reserved already. */
            $replacementList = $replBean->getReplacements ( $termDates );

            /* Check that the limit of bookings has not been exceeded. */
            if ( $this->getBookingsCount ( $replacementList, SessionDataBean::getUserId() )
                 >= $lectureBean->getReplacementCount() )
            {
                $this->action .= '.e_exceed';
                return;
            }

            /* Make information about the labtask groups and their corresponding labtasks to the
               templating engine. */
            $lgrpBean->assignGroupsAndLabtasks();

            $this->action .= ".labselect";
            return;
        }
        else {
            /* Do not list past replacements. */
            $termDates['from'] = date ( 'Y-m-d H:i:s');

            /* Get the list of possible replacements for this lecture without the past replacements. The list will possibly
               include replacement labs where this particular lab task has been reserved already. */
            $replacementList = $replBean->getReplacements($termDates);

            /* Store the user id. */
            $userId = SessionDataBean::getUserId();

            /* Make information about the selected labtask group available to the templating
               engine. */
            $lgrpBean->id = $this->lgrpId;
            $lgrpBean->assignSingle();

            /* Check that the student is not trying to reserve more than one replacement
               of the same lab topic. */
            if ( $this->hasThisLabBooked ( $replacementList, $userId ))
            {
                $this->action .= '.e_booked';
                return;
            }

            /* The rule says only one replacement student for a single laboratory task.
               We will therefore remove those that have been already reserved for somebody.
               We will also remove replacements that have been already booked out by this
               user for some other lab excersises. And finally, we will not display
               replacement terms in case that they are full. */
            $replacementList = $this->removeBookedGroups ( $replacementList, $userId );

            /* Assign the result to the list of Smarty variables. */
            $this->_smarty->assign ( 'replacements', $replacementList );

            /* Save the value of `labId` to session so that user cannot manipulate it. */
            $this->sessionPushLgrpId();
        }
    }
	
	function doSave ()
	{
		/* POST variable `replacement` contains a list selected replacement
		   excersises for this lecture. The list may contain new items
		   that shall be saved to the database. It may also not contain
		   some previously present items. Those items have to be checked
		   for possible registered students and if found empty, they can
		   be deleted. */
		$this->processPostVars();

        if ( ! empty ( $this->replstatus ))
        {
            /* Lecturer is saving the replacement status. As the save handler may
               be also used by students, check first that the role of the user
               is at least USR_LECTURER. */
            if ( UserBean::isRoleAtLeast( SessionDataBean::getUserRole(), USR_LECTURER ))
            {
                /* At least lecturer. Get the student list which is now indexed
                   by student id. */
                $studentList = $_SESSION['replstudents'];
                foreach ( $this->replstatus as $student_id => $status )
                {
                    /* Ignore entries that were not stored in the session. */
                    if ( ! array_key_exists ( $student_id, $studentList )) continue;
                    $student = $studentList[$student_id];
                    switch ( $status )
                    {
                        case self::PASSED :
                            $this->dbReplace ( $this->replId, $student_id, $student['datefrom'], 'NULL', true, false );
                            break;
                        case self::EXCUSED :
                            $this->dbReplace ( $this->replId, $student_id, $student['datefrom'], 'NOW()', false, false );
                            break;
                        case self::FAILED :
                            $this->dbReplace ( $this->replId, $student_id, $student['datefrom'], 'NULL', false, true );
                            break;
                        case self::NOSHOW :
                            $this->dbReplace ( $this->replId, $student_id, $student['datefrom'], 'NULL', false, false );
                            break;
                        default :
                            die ( "Wrong status." );
                    }
                }
                $this->action .= ".replstudents";
            }
            else
            {
                /* No permission to save anything. */
                $this->action .= "e_perm";
            }
        }
        else
        {
            /* Fetch the labId from the session.*/
            $this->sessionPopLgrpId();

            /* Check that the `lgrpId` really exists. Check also that the identifier
               of the replacement has been really passed to this handler. */
            if ( ! isset ( $this->lgrpId ) || empty ( $this->replId ))
            {
                $this->action .= '.e_noid';
                return;
            }

            /* We will need access to the replacement table as well - to manipulate
               the number of replacement slots. */
            $replBean = new ExcersiseReplacementBean ( $this->replId, $this->_smarty, NULL, NULL );
            $replBean->assignSingle();

            /* Lock the access to the table. */
            $res = mutexLock ( $this, self::$projectId, $lockTime, $lockLogin );

            /* Assign lock time and lock user. */
            $this->_smarty->assign ( 'locktime', $lockTime );
            $this->_smarty->assign ( 'locklogin', $lockLogin );

            /* Call to mutexLock() may return several return codes and we have
               to react to all of them. */
            switch ( $res )
            {
                /** @noinspection PhpMissingBreakStatementInspection */
                case MUTEX_LOCK_STOLEN_OK;
                    /* Stealing a stale lock is perfecty okay. On the other
                hand we would better let the user know that someone
                has started editing the data and did not save them for
                more than 30 minutes. */
                    $this->_smarty->assign ( 'lockstolen', true );
                case MUTEX_OK:
                    /* Pass to the next stage. */
                    break;
                case MUTEX_E_ISLOCKED:
                    /* Resource is locked. Refuse to edit. */
                    $this->action = 'e_islocked';
                    return;
                case MUTEX_E_FTOK:
                    /* Could not construct a valid semaphore id. */
                    $this->action = 'e_ftok';
                    return;
                case MUTEX_E_CANTACQUIRE:
                    /* Could not acquire the semaphore used to block access to the
                  mutex file. */
                    $this->action = 'e_cantacquire';
                    return;
                default:
                    $this->action = 'e_mutexval';
                    return;
            }

            /* Verify that the booking of this lab task is still possible.  */
            if ( ! $this->isNotBooked ())
            {
                /* Although the replacement of the lab task seemed possible, it has been
                   in between booked out by someone else. */
                $this->action .= '.e_booked';
                $this->sessionPushLgrpId();
            }
            elseif ( ! $replBean->hasFreeSlots ())
            {
                /* The replacement booking would still be possible as the labtask group
                   is still free for this replacement excersise, but someone was faster
                   taking up the last free slots. */
                $this->action .= '.e_noslots';
                $this->sessionPushLgrpId();
            }
            else
            {
                /* Insert booking info into the database. */
                $this->dbInsert( $this->replId, SessionDataBean::getUserId(), $this->lgrpId );
                /* Decrease the count of free slots. */
                $replBean->decreaseCount();
                /* Assign the group id to Smarty. */
                $this->assign ( 'lgrpid', $this->lgrpId );
            }

            /* Unlock the access to points. */
            $res = mutexUnlock ( $this, self::$projectId );

            /* Call to mutexUnlock() may return several return codes and we have to react
               to all of them. */
            switch ( $res )
            {
                case MUTEX_OK:
                    /* Pass to the next stage. */
                    break;
                case MUTEX_E_FTOK:
                    /* Could not construct a valid semaphore id. */
                    $this->action = 'e_ftok';
                    return;
                case MUTEX_E_CANTACQUIRE:
                    /* Could not acquire the semaphore used to block acces to the
                  mutex file. */
                    $this->action = 'e_cantacquire';
                    return;
                default:
                    $this->action = 'e_mutexval';
                    return;
            }
        }
    }

    function doDelete()
    {
        /* The replacement id and the replacement booking date will be used to
           identify the replacement to be deleted. */
        $this->processGetVars();

        /* Make the information about the lecture available to the templating
           engine and also use it to determine if the booking really exists. */
        $booking = $this->getBooking ();

        /* In case that no replacement has been booked (or in case that the
           parameters of the booking were not correct or not specified), the
           Smarty variable `booking` will be empty. */
        if ( ! empty ( $booking ))
        {
            $lgrpList = $this->getLabtasksForGroup ( $booking['grpid'] );
            $this->_smarty->assign ( 'booking',  $booking );
            $this->_smarty->assign ( 'lgrpList', $lgrpList );
        }
    }

    function doRealDelete()
    {
        /* The replacement id and the replacement booking date will be used to
           identify the replacement to be deleted. */
        $this->processPostVars();

        /* Make the information about the lecture available to the templating
           engine and also use it to determine if the booking really exists. */
        $booking = $this->getBooking ();

        /* In case that no replacement has been booked (or in case that the
           parameters of the booking were not correct or not specified), we
           shall ignore the attempt to delete the booking. */
        if ( ! empty ( $booking ))
        {
            /* We will need access to the replacement table as well - to manipulate
               the number of replacement slots. */
            $replBean = new ExcersiseReplacementBean ( $this->replId, $this->_smarty, NULL, NULL );
            $replBean->assignSingle();

            $lgrpList = $this->getLabtasksForGroup ( $booking['grpid'] );

            $this->_smarty->assign ( 'booking', $booking );
            $this->_smarty->assign ( 'lgrpList', $lgrpList );

            if ( $booking['candelete'] )
            {
                $this->dbQuery (
                    "UPDATE repl_stud SET dateto=NOW() WHERE " .
                        "replacement_id=" . $this->replId . " AND " .
                        "datefrom='" . $this->datefrom . "' AND " .
                        "student_id=" . SessionDataBean::getUserId() );
                /* Decrease the count of free slots. */
                $replBean->increaseCount();
            }
            else
            {
                $this->action .= '.e_cancelled';
            }
        }
    }

    function doShow()
    {
        /* Process parameters provided as a part of URL (bookingtype in this case) */
        $this->processGetVars();

        /* Information about the lecture we are listing exercises for ... */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, NULL, NULL);
        $lectureBean->assignSingle();

        /* Retrieve parameters of the current term. */
        $termDates = SchoolYearBean::getTermLimits($this->schoolyear, $lectureBean->getTerm());

        /* Construct a database bean for accessing replacements. */
        $replBean = new ExcersiseReplacementBean ($this->id, $this->_smarty, NULL, NULL);
        /* Get the list of replacements exercises for this lecture.
           @TODO Why do we need term limits instead of schoolyear? */
        $replacementList = $replBean->getReplacements($termDates);

        /* Fetch data for table of failed replacement exercises or data of all bookings. */
        if ( $this->bookingtype == self::BOOKING_NOSHOW )
        {
            $this->assignFailedStudents ( $replacementList );
            $this->action .= '.failed';
        }
        elseif ( $this->bookingtype == self::BOOKING_ALL )
        {
            $this->assignStudentBookings ( $replacementList );
            $this->action .= '.all';
        }
        else
        {
            $this->action .= '.e_bookingtype';
        }
    }
}
?>