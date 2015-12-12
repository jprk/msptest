<?php
/**
 * Interface to replacement excersises and labs (used for physics labs
 * at the moment).
 *
 * (c) Jan Prikryl, 2012, 2013
 */
class ExcersiseReplacementBean extends DatabaseBean
{
	/* Maximum number of persons allowed for replacement. */
	const PERSON_LIMIT = 3;

    private $replacements;
    private $excersise_id;
    private $manual_term;
    private $date;
    private $mfrom;
    private $mto;
	
	/* Constructor */
	function __construct ( $id, &$smarty, $action, $object )
	{
		/* Call parent's constructor first */
		parent::__construct( $id, $smarty, 'replacement_dates', $action, $object );
		/* Update internals. */
		//self::_setDefaults();
	}
	
	function dbInsert ( $id, $excersise_id, $date, $mfrom, $mto, $count )
	{
		if ( isset ( $mfrom ))
            $mfrom = "'" . $mfrom . "'";
        else
            $mfrom = 'NULL';

        if ( isset ( $mto ))
            $mto = "'" . $mto . "'";
        else
            $mto = 'NULL';

        $this->dbQuery (
			"INSERT INTO replacement_dates VALUES (" .
				$id . "," .
				$excersise_id . ",'" .
				$date . "'," .
                $mfrom . "," .
                $mto . "," .
                $count . ")"
				);
	}
	
	function dbQueryFullList ( $where )
	{
		return DatabaseBean::dbQuery (
				"SELECT * FROM replacement_dates"
				. $where
				. " ORDER BY `date`"
		);
	}

    function dbQuerySingle ()
    {
        DatabaseBean::dbQuerySingle ();

        $this->excersise_id = $this->rs['excersise_id'];
    }

    function dbDelete ( $id )
    {
        $this->dbQuery ( "DELETE FROM replacement_dates WHERE id=" .$id );
    }

    function decreaseCount ()
    {
        $this->dbQuery ( "UPDATE replacement_dates SET avail_count=avail_count-1 WHERE id=" .$this->id );
    }

    function increaseCount ()
    {
        $this->dbQuery ( "UPDATE replacement_dates SET avail_count=avail_count+1 WHERE id=" .$this->id );
    }

    function hasFreeSlots ()
    {
        $rs = $this->dbQuery ( "SELECT avail_count FROM replacement_dates WHERE id=" .$this->id );
        if ( ! empty ( $rs ))
        {
            if ( $rs[0]['avail_count'] > 0 ) return true;
        }
        return false;
    }

    function assignSingle ()
    {
        /* Fetch the data of the replacement. */
        $this->dbQuerySingle ();
        $this->_smarty->assign ( 'replacement', $this->rs );

        /* Fetch the information abou the corresponding
           excersise and lecturer. */
        $excersiseBean = new ExcersiseBean ( $this->excersise_id, $this->_smarty, NULL, NULL );
        $excersiseBean->assignSingle ();
    }
	
	/**
     * Get all possibilities to visit a replacement exercise.
     * Will list all replacement records that are bound to a given set of exercises and occur within the given
     * time limit.
     */
    function getReplacementsForExcList ( $excersiseList, $termDates )
	{
		/* Convert the excersise list to an `id` array. */
		$idStr = array2ToDBString ( $excersiseList, 'id' );
		/* Query a list of replacements.
		   ADDTIME has to be used to combine the `rd.date` (which is just the day) with the hour when the
		   exercise really starts. Without this the 00:00:00 of `rd.date` is used for calculation, effectively
		   skipping all exercises during the actual day -- given that $termDates['from'] contains the actual
		   time, which it under certain circumstances does. */
		$rs = $this->dbQuery (
            "SELECT rd.id AS `id`, excersise_id, date, " .
            "IFNULL(rd.mfrom,ex.from) AS `from`, " .
            "IFNULL(rd.mto,ex.to) AS `to`, rd.mto IS NOT NULL AS `manual_term`, " .
            "avail_count FROM replacement_dates AS rd " .
            "LEFT JOIN excersise AS ex ON rd.excersise_id=ex.id " .
            "WHERE ex.id IN (" . $idStr . ") AND " .
            "ADDTIME(rd.date,IFNULL(rd.mfrom,ex.from))>='" . $termDates['from'] . "' AND " .
            "rd.date<='" . $termDates['to'] . "' ORDER BY rd.date,`from`" );
        $this->dumpVar ( 'rs', $rs );
		return $rs; //resultsetToIndexKey ( $rs, 'id' );
	}

    /**
     * Return a list of replacement candidate dates for given exercises and term limits.
     *
     * @param $exerciseList
     * @param $termDates
     * @return array
     */
	function getReplDatesForExclist ( $exerciseList, $termDates )
	{
		$repls = $this->getReplacementsForExcList ( $exerciseList, $termDates );
		$ret = array();
		if ( ! empty ( $repls ))
		{
			foreach ( $repls as $key => $val )
			{
				$date  = strtotime ( $val['date'] );
				$excId = $val['excersise_id'];
				if ( ! array_key_exists( $date, $ret )) $ret[$date] = array();
				$ret[$date][$val['from']] = $val;
			}
		}
		$this->dumpVar ( 'repls', $ret );
		return $ret;
	}

    function getReplacements ( $termDates )
    {
        /* Get the list of all exercises for the given lecture id and the current school year. */
        $exerciseBean = new ExcersiseBean ( NULL, $this->_smarty, NULL, NULL );
        $exerciseList = $exerciseBean->getFull ( $this->id, $this->schoolyear );

        /* Convert the exercise list to a list indexed by exercise id so that it can be merged
           into the list of replacements. */
        $exerciseList = resultsetToIndexKey ( $exerciseList, 'id' );

        /* Get a list of already defined replacement dates for this term and the given list of
           exercise ids. The list is indexed by date and it may contains dates that were entered
           manually (like exercises that were moved from their scheduled occurrence due to holidays,
           technical problems etc.).   */
        $reps = $this->getReplacementsForExcList ( $exerciseList, $termDates );

        /* Populate the entries in the `reps` with exercise info. */
        foreach ( $reps as $key => $val )
        {
            $eId = $val['excersise_id'];
            $reps[$key] = array_merge ( $exerciseList[$eId], $reps[$key] );
        }

        return $reps;
    }
	
    /**
     * Process parameters supplied as POST part of the request.
     */
    function processPostVars ()
    {
        assignPostIfExists ( $this->replacements, $this->rs, 'replacements' );
        assignPostIfExists ( $this->manual_term,  $this->rs, 'manual_term' );
        assignPostIfExists ( $this->excersise_id, $this->rs, 'excersise_id' );
        assignPostIfExists ( $this->mfrom,        $this->rs, 'mfrom' );
        assignPostIfExists ( $this->mto,          $this->rs, 'mto' );

        if ( array_key_exists ( 'date', $_POST ))
        {
            $this->date = $this->rs['date'] = czechToSQLDateTime ( trimStrip ( $_POST['date'] ));
        }
    }
		
	function doAdmin ()
	{
		/* Get the information about the lecture we are listing excersises for ... */
		$lectureBean = new LectureBean ($this->id, $this->_smarty, NULL, NULL);
		$lectureBean->assignSingle ();
		
		/* Get the list of all excersises for the given lecture id and the
		   current school year. */
        $exerciseBean = new ExcersiseBean ( NULL, $this->_smarty, NULL, NULL );
        $exerciseList = $exerciseBean->getFull ( $this->id, $this->schoolyear );

        /* Exercise list is now indexed by row position, we would like to
           have it indexed by exercise id. */
        $exerciseList = resultsetToIndexKey ( $exerciseList, 'id' );
		//$this->dumpVar('excersiseList',$excersiseList);

		/* Retrieve parameters of the current term. */
        $termDates  = SchoolYearBean::getTermDates  ( $this->schoolyear, $lectureBean->getTerm() );
        $termLimits = SchoolYearBean::getTermLimits ( $this->schoolyear, $lectureBean->getTerm() );

        /* Convert term start and end dates to integer timestamps. */
		$dateFrom  = strtotime ( $termDates['from'] );
		$dateTo    = strtotime ( $termDates['to']   );
		
		/* Get a list of already defined replacement dates for this term
		 * and the given list of exercise ids. The list is indexed by date and
		 * it may contain dates that were entered manually (like exercises
		 * that were moved from their scheduled occurrence due to holidays,
		 * technical problems etc.).   */
		$reps = $this->getReplDatesForExcList ( $exerciseList, $termLimits );
		
		/* Loop over all exercises and generate possible dates for subscription
		   to replacement exercises. */
		$replacementList = array();
		//$this->dumpVar('excersiseList', $excersiseList);
		foreach ( $exerciseList as $key => $val )
		{
			//$this->dumpVar('val', $val);
            /* This is the date when the term begins. */
			$currentDate = $dateFrom;
            /* Determine the offset of the first exercise counted from $dateFrom and the `spacing` - the number
               of days between exercises. */
			$tmpoff  = daynumToOffsets ( $currentDate, $val['day']['num'] );
			$offset  = $tmpoff['offset'];
			$spacing = $tmpoff['spacing'];
			if ( $offset > 0 )
			{
				/* Move the first day. */
				$offsetStr = sprintf ( "+%d day", $offset );
			    if ( $offsetStr > 1 ) $offsetStr = $offsetStr . 's';
			    //$this->dumpVar('offset string', $offsetStr );
			    $currentDate = strtotime ( $offsetStr, $currentDate );
			}

			$spacingStr = sprintf ( "+%d day", $spacing );
			if ( $spacingStr > 1 ) $spacingStr = $spacingStr . 's';
			//$this->dumpVar('spacing string', $spacingStr );
				
			while ( $currentDate <= $dateTo )
			{
			    //$this->dumpVar('adding excersise date', strftime('%d.%m.%Y',$currentDate));
				$replacementList[$currentDate][$val['from']] = $key;
				$currentDate = strtotime ( $spacingStr, $currentDate );
			}
		}

        /* Merge the contents of `reps` into `replacementList`. Some of the
           entries may be duplicated. Despite functions `array_merge` and
           `array_merge_recursive`, this has to be done using a custom code
           as we only want to add the non-existent entries from `reps` to
           `replacementList`. */
        foreach ( $reps as $date => $singleDay )
        {
            foreach ( $singleDay as $from => $data )
            {
                $replacementList[$date][$from] = $data['excersise_id'];
            }
        }
		
		/* The keys of $replacementList may be in arbitrary order. We need
		   to have them sorted. */
		ksort ( $replacementList );
		/* And we also need to sort the keys of the second level. */
		foreach ( $replacementList as $key => $val )
		{
			ksort ( $replacementList[$key] );
		}

        $this->dumpVar('replacement list', $replacementList );

		/* Now construct a new one-level array that contains the list of
		   possible replacement excersises. */
		$this->replacements = array();
		foreach ( $replacementList as $date => $singleDay )
		{
			foreach ( $singleDay as $from => $excersiseId )
			{
				$tmpRec = $exerciseList[$excersiseId];
                /* The `id` property denotes the id of an existing replacement
                   term. The existing replacements are handled by the if clause
                   below, here we assume the default, unselected replacement
                   excersise. */
                $tmpRec['id'] = 0;
                $tmpRec['excersise_id'] = $excersiseId;
				$tmpRec['from'] = strtotime ( '1970-01-01 '.$tmpRec['from'] );
				$tmpRec['to']   = strtotime ( '1970-01-01 '.$tmpRec['to'] );
				$tmpRec['date'] = $date;
                $tmpRec['manual_term'] = false;
				//$this->dumpVar('replacement date',$date);

                /* Handle the existing replacement excersises. */
				if ( array_key_exists ( $date, $reps ) &&
					 array_key_exists ( $from, $reps[$date] ))
                {
                    $rdf = $reps[$date][$from];
                    if ( $excersiseId == $rdf['excersise_id'] )
				    {
					    $tmpRec['checked']=' checked="checked"';
                        $tmpRec['id'] = $rdf['id'];
                        if ( $rdf['manual_term'] )
                        {
                            /* Manually entered replacement term. */
                            $tmpRec['from'] = $rdf['from'];
                            $tmpRec['to']   = $rdf['to'];
                            $tmpRec['manual_term'] = true;
                        }
                    }

				}	
				$this->replacements[] = $tmpRec;
			}
		}

		/* Assign the result to the list of Smarty variables. */
		$this->_smarty->assign ( 'replacements', $this->replacements );
		/* Store the data in the session so that the subsequent `save` may
		   check which elements were checked and which were unchecked. */
		$_SESSION['replacements'] = $this->replacements;
	}
	
	function doEdit ()
	{
        /* Get the information about the lecture we are listing excersises
           for ... */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, NULL, NULL);
        $lectureBean->assignSingle ();

        /* Get the list of all excersises for the given lecture id and the
             current school year. */
        $excersiseBean = new ExcersiseBean ( NULL, $this->_smarty, NULL, NULL );
        $excersiseBean->assignSelectMap ( $this->id, $this->schoolyear );
    }
	
	function doSave ()
	{
		/* POST variable `replacements` contains a list selected replacement
		   excersises for this lecture. The list may contain new items
		   that shall be saved to the database. It may also not contain
		   some previously present items. Those items have to be checked
		   for possible registered students and if found empty, they can
		   be deleted. */
		$this->processPostVars();
		
		/* Save may be called either for a list of terms where a lecturer
		   chooses appropriate dates (by admin handler), or from an edit
		   handler where a single manual term is being added. */
        if ( $this->manual_term )
        {
            /* Manually adding a single replacement term. */
            $this->dbInsert (
                0, $this->excersise_id, $this->date,
                $this->mfrom, $this->mto,
                self::PERSON_LIMIT );
            $this->action .= '.manual';
            $this->_smarty->assign ( 'manual_term', $this->rs);
            /* Get the excersise data. */
            $excersiseBean = new ExcersiseBean ( $this->excersise_id, $this->_smarty, NULL, NULL );
            $excersiseBean->assignSingle();
        }
        else
        {
            $addedList   = array();
            $deletedList = array();

            foreach (  $_SESSION['replacements'] as $key => $val )
		    {
                /* There are tho interesting state changes for every item:
                   (1) was checked -> is unchecked (shall be deleted),
                   (2) was unchecked -> is checked (shall be inserted).

                   First we shall have a look whether the response array contains
                   the same key. */
                $has_key = array_key_exists ( $key, $this->replacements );
                /* And now start with the checked -> unchecked change. */
                if ( isset ( $val['checked'] ) && ( ! $has_key ))
                {
                    /* Delete the element from the database. */
                    $this->dbDelete ( $val['id'] );
                    /* Store the deleted element data for presentation. */
                    $deletedList[] = $val;
                }
                elseif ( empty ( $val['checked'] ) && $has_key )
                {
                    /* Add the element to the database. */
                    $this->dbInsert (
                            0, $val['excersise_id'], timestampToSQL($val['date']),
                            NULL, NULL,
                            self::PERSON_LIMIT );
                    $addedList[] = $val;
                }
            }

            unset ( $_SESSION['replacements'] );

            $this->_smarty->assign ( 'addedList',   $addedList   );
            $this->_smarty->assign ( 'deletedList', $deletedList );
        }
	}
}
?>