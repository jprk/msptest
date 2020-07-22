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
    const TERMTYPE_NONE = 0;
    const TERMTYPE_WINTER = 1;
    const TERMTYPE_SUMMER = 2;

    protected $schoolyear_start = -1;

    static function TERMTYPE_LIST()
    {
        return array(
            self::TERMTYPE_NONE => 'Vyberte ze seznamu...',
            self::TERMTYPE_WINTER => 'Zimní',
            self::TERMTYPE_SUMMER => 'Letní');
    }

    /**
     * Return beginning and end date of a schoolyear term.
     *
     * @param int $schoolyear Year when the school year starts.
     * @param int $term SchoolYearBean::TERMTYPE_WINTER or SchoolYearBean::TERMTYPE_SUMMER.
     * @return array Dictionary indexed by 'from' and 'to' or NULL.
     */
    static function getTermDates($schoolyear, $term)
    {
        $_dates = array(
            2011 => array(
                self::TERMTYPE_WINTER => array('from' => '2011-09-29', 'to' => '2012-01-13'),
                self::TERMTYPE_SUMMER => array('from' => '2012-02-27', 'to' => '2012-06-01')
            ),
            2012 => array(
                self::TERMTYPE_WINTER => array('from' => '2012-09-24', 'to' => '2013-01-08'),
                self::TERMTYPE_SUMMER => array('from' => '2013-02-18', 'to' => '2013-05-24')
            ),
            2013 => array(
                self::TERMTYPE_WINTER => array('from' => '2013-09-30', 'to' => '2014-01-10'),
                self::TERMTYPE_SUMMER => array('from' => '2014-02-17', 'to' => '2014-05-23')
            ),
            2014 => array(
                self::TERMTYPE_WINTER => array('from' => '2014-09-22', 'to' => '2015-01-09'),
                self::TERMTYPE_SUMMER => array('from' => '2015-02-16', 'to' => '2015-05-22')
            ),
            2015 => array(
                self::TERMTYPE_WINTER => array('from' => '2015-10-01', 'to' => '2016-01-15'),
                self::TERMTYPE_SUMMER => array('from' => '2016-02-22', 'to' => '2016-05-29')
            ),
            2016 => array(
                self::TERMTYPE_WINTER => array('from' => '2016-10-03', 'to' => '2017-01-13'),
                self::TERMTYPE_SUMMER => array('from' => '2017-02-20', 'to' => '2017-05-26')
            ),
            2017 => array(
                self::TERMTYPE_WINTER => array('from' => '2017-10-02', 'to' => '2018-02-16'),
                self::TERMTYPE_SUMMER => array('from' => '2018-02-19', 'to' => '2018-09-30')
            ),
            2018 => array(
                self::TERMTYPE_WINTER => array('from' => '2018-10-01', 'to' => '2019-02-15'),
                self::TERMTYPE_SUMMER => array('from' => '2019-02-18', 'to' => '2019-09-20')
            ),
            2019 => array(
                self::TERMTYPE_WINTER => array('from' => '2019-09-23', 'to' => '2020-02-14'),
                self::TERMTYPE_SUMMER => array('from' => '2020-02-17', 'to' => '2020-09-13')
            ),
            /* Dummy, there is no official info yet. */
            2020 => array(
                self::TERMTYPE_WINTER => array('from' => '2020-09-21', 'to' => '2021-02-15'),
                self::TERMTYPE_SUMMER => array('from' => '2021-02-18', 'to' => '2021-09-30')
            ));

        if (array_key_exists($schoolyear, $_dates))
        {
            $termData = $_dates[$schoolyear];
            if (array_key_exists($term, $termData))
            {
                return $termData[$term];
            }
        }
        return NULL;
    }

    /**
     * @param $schoolyear
     * @param $term
     * @return array|null
     * @throws Exception In case that school year data is missing.
     */
    static function getTermLimits($schoolyear, $term)
    {
        switch ($term)
        {
            case self::TERMTYPE_WINTER :
                /* Winter term end limit is the beginning of the next winter term. */
                $termBegin = self::getTermDates($schoolyear, self::TERMTYPE_WINTER);
                $termEnd = self::getTermDates($schoolyear + 1, self::TERMTYPE_WINTER);
                /* It could happen that the information about next school year is
                   not available yet. In such a case we will raise an exception. */
                if (!isset ($termEnd))
                {
                    $message = 'Chybí údaje o školním roce ' . ($schoolyear + 1) . '/' . ($schoolyear + 2);
                    throw new Exception ($message);
                }
                return array('from' => $termBegin['from'], 'to' => $termEnd['from']);
            case self::TERMTYPE_SUMMER :
                /* Summer term end limit is the beginning of the next summer term. */
                $termBegin = self::getTermDates($schoolyear, self::TERMTYPE_SUMMER);
                $termEnd = self::getTermDates($schoolyear + 1, self::TERMTYPE_SUMMER);
                /* It could happen that the information about next school year is
                   not available yet. In such a case we will raise an exception. */
                if (!isset ($termEnd))
                {
                    $message = 'Chybí údaje o školním roce ' . ($schoolyear + 1) . '/' . ($schoolyear + 2);
                    throw new Exception ($message);
                }
                return array('from' => $termBegin['from'], 'to' => $termEnd['from']);
        }
        return NULL;
    }

    /**
     * Find out lecture beginning and ending dates in this term.
     * @param $lecture_info array Lecture info as stored in SessionBean.
     * @return array|null
     * @throws Exception
     */
    static function getTermLimitsForLecture($lecture_info)
    {
        $year = SessionDataBean::getSchoolYear();
        $term = $lecture_info['term'];
        return self::getTermDates($year, $term);

    }

    /**
     * Return the stating year of the current schoolyear.
     *
     * @return int Starting year of the current schoolyear (2010 for 2010/2011).
     */
    static function getSchoolYearStart()
    {
        return self::getSchoolYearStartForTerm(self::TERMTYPE_WINTER);
    }

    /**
     * Return the starting year of the current school year for lecture that is
     * lecture in term $term.
     *
     * @param int $term SchoolYearBean::TERMTYPE_WINTER or SchoolYearBean::TERMTYPE_SUMMER.
     * @return int Starting year of the current schoolyear (2010 for 2010/2011).
     */
    static function getSchoolYearStartForTerm($term)
    {
        /* This is the current time. */
        $cTime = time();
        $cDate = getdate($cTime);

        /* This is the current year. */
        $year = $cDate['year'];

        /* If the value of $term specifies the winter term, the possible pair of school years that may occur is
           $year (for date >= first day of the winter term and < the following year), and $year-1 (for all dates
           < first day of the winter term in the current year). For the summer term the candidate pair is $year-1
           (for all dates >= first day of the summer term) and $year-2 (otherwise).

           This means that we shall decrease the current value of $year by one for $term == TERMTYPE_SUMMER to get
           the correct date of the start of the summer term. */
        if ($term == self::TERMTYPE_SUMMER) $year--;

        /* Get the school year data assuming that $year is identical with the current school year (which is true
           only at the beginning of the winter term. */
        $termDates = self::getTermDates($year, $term);
        /* Use a grace period of one week so that the system switches into the new school year one week before
           the actual term starts. This is useful in cases where students should have access to locked files before
           the first lecture. */
        $termStart = strtotime($termDates['from'].' - 1 week');

        /* If the specified term starts later in this year, we are in the
           previous school year. */
        if ($termStart > $cTime) $year--;

        return $year;
    }

    /**
     * @static
     * @param $term
     * @return string
     * @throws OutOfRangeException
     */
    static function termToEnum($term)
    {
        switch ($term)
        {
            case self::TERMTYPE_WINTER :
                return 'w';
                break;
            case self::TERMTYPE_SUMMER :
                return 's';
                break;
            default :
                throw new OutOfRangeException ('Wrong value of term.');
        }
    }

    /**
     * @static
     * @param $enumVal
     * @return int
     * @throws OutOfRangeException
     */
    static function enumToTerm($enumVal)
    {
        switch ($enumVal)
        {
            case 'w' :
                return self::TERMTYPE_WINTER;
                break;
            case 's' :
                return self::TERMTYPE_SUMMER;
                break;
            default :
                throw new OutOfRangeException ('Unsupported enum value for term.');
        }
    }

    function doAdmin()
    {
        $this->schoolyear_start = SessionDataBean::getSchoolYear();
        $this->_smarty->assign('schoolyear_start', $this->schoolyear_start);
    }

    function doSave()
    {
        $this->schoolyear_start = $_POST['schoolyear_start'];
        SessionDataBean::setSchoolYear($_POST['schoolyear_start']);
        $this->_smarty->assign('schoolyear_start', $this->schoolyear_start);
    }
}

?>
