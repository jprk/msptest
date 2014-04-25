<?php
/*
 * Created on 13.2.2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class SchoolYearBean extends BaseBean
{
	/* Winter and summer terms */
	const WINTER_TERM = 1;
	const SUMMER_TERM = 2;
	
    protected $schoolyear_start = -1;
    
    /**
     * Return beginning and end date of a schoolyear term.
     * 
     * @param int $schoolyear Year when the school year starts. 
     * @param int $term SchoolYearBean::WINTER_TERM or SchoolYearBean::SUMMER_TERM.
     * @return array Dictionary indexed by 'from' and 'to' or NULL.
     */
    static function getTermDates ( $schoolyear, $term )
    {
    	$_dates = array ( 
    		2011 => array (
    			self::WINTER_TERM => array ( 'from' => '2011-09-29', 'to' => '2012-01-13' ),
    			self::SUMMER_TERM => array ( 'from' => '2012-02-27', 'to' => '2012-06-01' )
    		),
            2012 => array (
                self::WINTER_TERM => array ( 'from' => '2012-09-24', 'to' => '2013-01-08' ),
                self::SUMMER_TERM => array ( 'from' => '2013-02-18', 'to' => '2013-05-24' )
            ),
    		2013 => array (
                self::WINTER_TERM => array ( 'from' => '2013-09-30', 'to' => '2014-01-10' ),
                self::SUMMER_TERM => array ( 'from' => '2014-02-17', 'to' => '2014-05-23' )
	    ),
    		2014 => array (
                self::WINTER_TERM => array ( 'from' => '2014-09-23', 'to' => '2013-14-20' ),
                self::SUMMER_TERM => array ( 'from' => '2015-02-17', 'to' => '2015-05-16' )
            ));

        if ( array_key_exists ( $schoolyear, $_dates ))
    	{
    		$termData = $_dates[$schoolyear];
    		if ( array_key_exists ( $term, $termData ))
    		{
    			return $termData[$term];
    		}
    	}
    	return NULL;
    }

    static function getTermLimits ( $schoolyear, $term )
    {
        switch ( $term )
        {
            case self::WINTER_TERM :
                /* Winter term end limit is the beginning of the next winter term. */
                $termBegin = self::getTermDates ( $schoolyear,   self::WINTER_TERM );
                $termEnd   = self::getTermDates ( $schoolyear+1, self::WINTER_TERM );
                /* It could happen that the information about next school year is
                   not available yet. In such a case we will raise an exception. */
                if ( ! isset ( $termEnd ))
                {
                    throw new Exception (
                        'Chybí údaje o školním roce ' .
                            $schoolyear + 1 . '/' . $schoolyear + 2 );
                }
                return array ( 'from' => $termBegin['from'], 'to' => $termEnd['from'] );
            case self::SUMMER_TERM :
                /* Summer term end limit is the beginning of the next summer term. */
                $termBegin = self::getTermDates ( $schoolyear,   self::SUMMER_TERM );
                $termEnd   = self::getTermDates ( $schoolyear+1, self::SUMMER_TERM );
                /* It could happen that the information about next school year is
                   not available yet. In such a case we will raise an exception. */
                if ( ! isset ( $termEnd ))
                {
                    throw new Exception (
                        'Chybí údaje o školním roce ' .
                        $schoolyear + 1 . '/' . $schoolyear + 2 );
                }
                return array ( 'from' => $termBegin['from'], 'to' => $termEnd['from'] );
        }
        return NULL;
    }
    
    /**
     * Return the stating year of the current schoolyear.
     * 
     * @return int Starting year of the current schoolyear (2010 for 2010/2011).
     */
    static function getSchoolYearStart ( )
    {
    	return self::getSchoolYearStartForTerm ( self::WINTER_TERM );
    }

    /**
     * Return the starting year of the current school year for lecture that is
     * lecture in term $term.
     *
     * @param int $term SchoolYearBean::WINTER_TERM or SchoolYearBean::SUMMER_TERM.
     * @return int Starting year of the current schoolyear (2010 for 2010/2011).
     */
    static function getSchoolYearStartForTerm ( $term )
    {
        /* This is the current time. */
        $cTime = time ();
        $cDate = getdate ( $cTime );

        /* This is the current year. */
        $year = $cDate['year'];

        /* If the value of $term specifies the winter term, the possible pair
           of school years that may occur is $year (for date >= first day of
           the winter term and < the following year), and $year-1 (for all dates
           < first day of the winter term in the current year). For the summer
           term the candidate pair is $year-1 (for all dates >= first day of the
           summer term) and $year-2 (otherwise).

           This means that we shall decrease the current value of $year by one
           for $term == SUMMER_TERM to get the correct date of the start of
           the summer term. */
        if ( $term == self::SUMMER_TERM ) $year--;

        /* Get the school year data assuming that $year is identical with the
           current school year (which is true only at the beginning of the
           winter term. */
        $termDates = self::getTermDates ( $year, $term );
        $termStart = strtotime ( $termDates['from'] );

        /* If the specified term starts later in this year, we are in the
           previous school year. */
        if ( $termStart > $cTime ) $year--;

        return $year;
    }

    /**
     * @static
     * @param $term
     * @return string
     * @throws OutOfRangeException
     */
    static function termToEnum ( $term )
    {
        switch ( $term )
        {
            case self::WINTER_TERM : return 'w'; break;
            case self::SUMMER_TERM : return 's'; break;
            default :
                throw new OutOfRangeException ( 'Wrong value of term.' );
        }
    }

    /**
     * @static
     * @param $enumVal
     * @return int
     * @throws OutOfRangeException
     */
    static function enumToTerm ( $enumVal )
    {
        switch ( $enumVal )
        {
            case 'w' : return self::WINTER_TERM; break;
            case 's' : return self::SUMMER_TERM; break;
            default :
                throw new OutOfRangeException ( 'Unsupported enum value for term.' );
        }
    }

    function doAdmin()
    {
    	$this->schoolyear_start = SessionDataBean::getSchoolYear();
        $this->_smarty->assign ( 'schoolyear_start', $this->schoolyear_start );
    }

    function doSave()
    {
        $this->schoolyear_start = $_POST['schoolyear_start'];
        SessionDataBean::setSchoolYear($_POST['schoolyear_start']);
        $this->_smarty->assign ( 'schoolyear_start', $this->schoolyear_start );
    }
}
?>
