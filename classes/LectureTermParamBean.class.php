<?php


class LectureTermParamBean extends DatabaseBean
{
    const DEFAULT_DATE = '1970-01-01';

    /** @var string $group_open_from */
    protected $group_open_from;
    /** @var string $group_open_to */
    protected $group_open_to;
    /** @var int $lecture_id */
    protected $lecture_id;

    private function _setDefaults()
    {
        $this->lecture_id = $this->rs['lecture_id'] = SessionDataBean::getLectureId();
        try
        {
            $limits = SchoolYearBean::getTermLimitsForLecture(SessionDataBean::getLecture());
            $this->group_open_from = $this->rs['group_open_from'] = $limits['from'];
            $this->group_open_to = $this->rs['group_open_to'] = $limits['to'];
        } catch (Exception $e)
        {
            error_log("Exception: {$e->getMessage()}, defaults substituted.");
            $this->group_open_from = $this->rs['group_open_from'] = self::DEFAULT_DATE;
            $this->group_open_from = $this->rs['group_open_from'] = self::DEFAULT_DATE;
        }
    }


    /* Constructor */
    function __construct($id, &$smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, "lecture_term", $action, $object);
        /* Initialise local defaults */
        $this->_setDefaults();
    }

    /**
     * Replace the database content with information from this object.
     */
    function dbReplace()
    {
        /* Fist we have to delete old data. We cannot use REPLACE as
           there is no primary key in the table. */
        $this->dbQuery(
            "DELETE FROM lecture_term WHERE lecture_id=$this->lecture_id AND year=$this->schoolyear");
        /* And now insert the updated record. */
        $this->dbQuery(
            "INSERT INTO lecture_term VALUES (" .
            "$this->lecture_id," .
            "$this->schoolyear,'" .
            $this->group_open_from . "','" .
            $this->group_open_to . "')");
    }

    /**
     * Process properties updated via HTTP POST request.
     */
    protected function processPostVars()
    {
        $this->group_open_from = $this->rs['group_open_from']
            = czechToSQLDateTime(
            trimStrip($_POST['group_open_from']), "00:00:00");
        $this->group_open_to = $this->rs['group_open_to']
            = czechToSQLDateTime(
            trimStrip($_POST['group_open_to']), "00:00:00");
    }

    public function dbQuerySingle($alt_id = 0)
    {
        $rs = DatabaseBean::dbQuery(
            "SELECT * FROM lecture_term WHERE lecture_id=$this->lecture_id AND year=$this->schoolyear");
        self::dumpVar('rs', $rs);
        if (empty($rs))
        {
            throw new InvalidArgumentException('No data for this lecture and schoolyear available.');
        }
        else
        {
            $rs = $rs[0];
            //self::dumpVar('gettype($rs["group_open_from"]=', gettype($rs['group_open_from']));
            //self::dumpVar('get_class($rs["group_open_from"]=', get_class($rs['group_open_from']));
            $this->group_open_from = $this->rs['group_open_from'] = $rs['group_open_from'];
            $this->group_open_to = $this->rs['group_open_to'] = $rs['group_open_to'];
        }
    }

    /**
     * @param bool $allow_empty To not throw exception on empty result and rather initialise empty dataset.
     * @throws Exception
     */
    protected function assignSingle($allow_empty = false)
    {
        if ($this->schoolyear && $this->lecture_id)
        {
            try {
                $this->dbQuerySingle();
            }
            catch (InvalidArgumentException $e)
            {
                /* Need to decide what to do next: if the entry does not exist, we will either rethrow the exception
                   or we will return a dummy default that can be e.g. edited later. */
                if ($allow_empty)
                {
                    $this->_setDefaults();
                }
                else
                {
                    /* Rethrow the exception */
                    throw $e;
                }
            }
        }
        else
        {
            throw new InvalidArgumentException('Lecture and/or school year info not specified.');
        }
        $this->assign('termParam', $this->rs);
    }

    /* -------------------------------------------------------------------
       HANDLER: SHOW
       ------------------------------------------------------------------- */
    function doShow()
    {
        throw new InvalidArgumentException('SHOW not implemented');
    }

    /**
     * Handler for "save" action.
     * @throws Exception
     */
    function doSave()
    {
        /* Assign POST variables to internal variables of this class and
           remove evil tags where applicable. */
        $this->processPostVars();
        /* Update the record, but do not update the password. */
        $this->dbReplace();
        /* Fetch the updated data of the lecture parameters so that we can write
           something out. */
        $this->assignSingle();
    }

    /* -------------------------------------------------------------------
       HANDLER: DELETE
       ------------------------------------------------------------------- */
    function doDelete()
    {
        throw new InvalidArgumentException('DELETE not implemented');
    }

    /* -------------------------------------------------------------------
       HANDLER: REAL DELETE
       ------------------------------------------------------------------- */
    function doRealDelete()
    {
        throw new InvalidArgumentException('REALDELETE not implemented');
    }

    /* -------------------------------------------------------------------
       HANDLER: ADMIN
       ------------------------------------------------------------------- */
    function doAdmin()
    {
        throw new InvalidArgumentException('ADMIN not implemented');
    }

    /**
     * Handler for "edit" action.
     * @throws Exception
     */
    function doEdit()
    {
        /* If id == 0, we will create a new record. Otherwise, we will
           fetch the lecture data from database. The result will be
           assigned to template variable 'lectureInfo'. */
        $this->assignSingle(true);
    }

    /**
     * @return string Group opening timestamp.
     */
    public function getGroupOpenFrom()
    {
        return $this->group_open_from;
    }

    /**
     * @return string Group closing timestamp.
     */
    public function getGroupOpenTo()
    {
        return $this->group_open_to;
    }


}