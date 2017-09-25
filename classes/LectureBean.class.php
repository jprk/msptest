<?php

class LectureBean extends DatabaseBean
{
    const DEFAULT_GRACE_MINUTES = 15;

    protected $code;
    protected $title;
    protected $thanks;
    protected $alert;
    protected $syllabus;
    protected $locale;
    protected $term;
    protected $repl_count;
    protected $repl_students;
    protected $do_replacements;
    protected $group_limit;
    protected $group_type;
    protected $do_groups;
    protected $grace_minutes;
    protected $lecture_manager;
    protected $rootsection;

    function _setDefaults()
    {
        /* Define default values for properties. */
        $this->code = "11*";
        $this->title = "";
        $this->thanks = "";
        $this->alert = "";
        $this->syllabus = "";
        $this->locale = "cs";
        $this->term = SchoolYearBean::TERMTYPE_NONE;
        $this->repl_count = 0;
        $this->repl_students = 0;
        $this->do_replacements = false;
        $this->group_limit = 0;
        $this->group_type = StudentGroupBean::GRPTYPE_NONE;
        $this->do_groups = false;
        $this->grace_minutes = self::DEFAULT_GRACE_MINUTES;
        $this->rootsection = 0;
        /* Update the value of $this->rs. */
        $this->_update_rs();
    }

    /* Constructor */
    function __construct($id, &$smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, "lecture", $action, $object);
        /* Initialise new properties to their default values. */
        $this->_setDefaults();
    }

    /**
     * Getter method for SessionDataBean.
     */
    static function getLocale(&$rsData)
    {
        if (!empty ($rsData))
        {
            if (array_key_exists('locale', $rsData))
                return $rsData['locale'];
        }
        return NULL;
    }

    /**
     * Getter method for SessionDataBean
     */
    static function getReplacements(&$rsData)
    {
        if (!empty ($rsData))
        {
            if (array_key_exists('do_replacements', $rsData))
                return $rsData['do_replacements'];
        }
        return NULL;
    }

    /**
     * Getter method for SessionDataBean
     */
    static function getGroupFlag(&$rsData)
    {
        if (!empty ($rsData))
        {
            if (array_key_exists('do_groups', $rsData))
                return $rsData['do_groups'];
        }
        return NULL;
    }

    /**
     * Getter method for SessionDataBean
     */
    static function getGraceMinutes(&$rsData)
    {
        if (!empty ($rsData))
        {
            if (array_key_exists('grace_minutes', $rsData))
                return $rsData['grace_minutes'];
        }
        return self::DEFAULT_GRACE_MINUTES;
    }

    /**
     * Getter function for group type.
     * @return mixed
     */
    public function getGroupType()
    {
        return $this->group_type;
    }

    /**
     * Getter function for lecture code.
     */
    function getCode()
    {
        return $this->code;
    }

    /**
     * Getter function for lecture root section.
     */
    function getRootSection()
    {
        return $this->rootsection;
    }

    /**
     * Getter method for the term of the lecture.
     * Typically, we have summer and winter terms.
     * @return int The value of term as defined in the ScholYearBean.
     */
    function getTerm()
    {
        return $this->term;
    }

    static function getTermFromData(&$rsData)
    {
        if (!empty ($rsData))
        {
            if (array_key_exists('term', $rsData))
                return $rsData['term'];
        }
        return NULL;
    }

    /**
     * Getter method for the number of students allowed as replacements
     * for laboratory exercises. If 0, no replacements are allowed.
     * @return int The number of students allowed as replacements.
     */
    function getReplacementStudents()
    {
        return $this->repl_students;
    }

    /**
     * Getter method for the number of replacements allowed for
     * a single student.
     * @return int The number replacements allowed.
     */
    function getReplacementCount()
    {
        return $this->repl_count;
    }

    /**
     * Return lecture code as stored in `rsData`.
     * Used by SessionDataBean.
     */
    static function getCodeFromData(&$rsData)
    {
        return $rsData['code'];
    }

    /**
     * Return lecture root section as stored in `rsData`.
     * Used by SessionDataBean.
     */
    static function getRootSectionFromData(&$rsData)
    {
        return $rsData['rootsection'];
    }

    function getLectureData()
    {
        return $this->rs;
    }

    function dbReplace()
    {
        /* When adding a new lecture we have to create also a top-level section
         * record. This record will be empty and we will use only its identifier
         * as a root identifier.  */
        if ($this->id == 0)
        {
            $secBean = new SectionBean ($this->id, $this->_smarty, NULL, NULL);
            $secBean->type = "root";
            $secBean->dbReplace();
            $this->rootsection = $secBean->id;
        }

        $args = [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'syllabus' => $this->syllabus,
            'alert' => $this->alert,
            'thanks' => $this->thanks,
            'locale' => $this->locale,
            'term' => SchoolYearBean::termToEnum($this->term),
            'repl_students' => $this->repl_students,
            'repl_count' => $this->repl_count,
            'group_limit' => $this->group_type,
            'group_type' => $this->group_type,
            'rootsection' => $this->rootsection
        ];
        dibi::query('REPLACE `lecture`', $args);
        $this->updateId();
    }

    function dbQuerySingle($alt_id = 0)
    {
        /* Query the data of this lecture (ID has been already specified) */
        DatabaseBean::dbQuerySingle($alt_id);
        /* Non-existent `id` will result in empty resultset. */
        if (empty($this->rs))
        {
            /* Assign default values to everything. */
            $this->_setDefaults();
        }
        else
        {
            /* Initialize the internal variables with the data queried from the
               database. */
            $this->code = vlnka(stripslashes($this->rs['code']));
            $this->title = vlnka(stripslashes($this->rs['title']));
            $this->alert = vlnka(stripslashes($this->rs['alert']));
            $this->thanks = vlnka(stripslashes($this->rs['thanks']));
            $this->syllabus = vlnka(stripslashes($this->rs['syllabus']));
            $this->locale = $this->rs['locale'];
            $this->term = $this->rs['term'] = SchoolYearBean::enumToTerm($this->rs['term']);
            $this->repl_students = $this->rs['replacement_students'];
            $this->repl_count = $this->rs['replacement_count'];
            $this->do_replacements = ($this->repl_students > 0);
            $this->group_limit = $this->rs['group_limit'];
            $this->group_type = $this->rs['group_type'];
            $this->do_groups = ($this->group_limit > 0);
            $this->rootsection = $this->rs['rootsection'];
            /* Update the value of $this->rs. This will make the lecture data
               * available to the templating engine. */
            $this->_update_rs();
            //$this->dumpThis();
        }
    }

    /* Assign POST variables to internal variables of this class and
       remove evil tags where applicable. We shall probably also remove
       evil attributes et cetera, but this will be done later if ever. */
    function processPostVars()
    {
        $this->id = intval($_POST['id']);
        $this->code = trimStrip($_POST['code']);
        $this->title = trimStrip($_POST['title']);
        $this->alert = trimStrip($_POST['alert']);
        $this->thanks = trimStrip($_POST['thanks']);
        $this->syllabus = trimStrip($_POST['syllabus']);
        $this->locale = trimStrip($_POST['locale']);
        $this->term = intval($_POST['term']);
        $this->repl_students = intval($_POST['repl_students']);
        $this->repl_count = intval($_POST['repl_count']);
        $this->group_limit = intval($_POST['group_limit']);
        $this->group_type = intval($_POST['group_type']);
        $this->rootsection = intval($_POST['rootsection']);
    }

    function _dbQueryList()
    {
        return DatabaseBean::dbQuery("SELECT id, code, title, syllabus, locale, term FROM lecture ORDER BY code,title");
    }

    /**
     * Return a map of lectures indexed by lecture id.
     */
    function dbQueryLectureMap()
    {
        $resultset = $this->_dbQueryList();

        $lectureMap = array();
        if (isset ($resultset))
        {
            foreach ($resultset as $key => $val)
            {
                $id = $val['id'];
                $lectureMap[$id]['id'] = $id;
                $lectureMap[$id]['code'] = stripslashes($val['code']);
                $lectureMap[$id]['title'] = stripslashes($val['title']);
                $lectureMap[$id]['syllabus'] = stripslashes($val['syllabus']);
                $lectureMap[$id]['locale'] = $val['locale'];
                $lectureMap[$id]['term'] = $val['term'];
                $lectureMap[$id]['rootsection'] = $val['rootsection'];
            }
        }

        return $lectureMap;
    }

    function getSelectMap()
    {
        /* Initialise the map array. */
        $lectureMap = array();
        /* If the bean has been initialised with `id` equal to zero,
           we will list all lecures. Otherwise we will list just the
           lecture corresponding to the bean `id` value. */
        if ($this->id == 0)
        {
            $resultset = $this->_dbQueryList();
            $lectureMap[0] = "Vyberte ze seznamu ...";
        }
        else
        {
            $this->dbQuerySingle();
            $resultset[0] = $this->rs;
        }
        if (isset ($resultset))
        {
            foreach ($resultset as $key => $val)
            {
                $lectureMap[$val['id']] = stripslashes($val['code']) . " (" . stripslashes($val['title']) . ")";
            }
        }
        // Use assignSelectMap instead.
        // $this->_smarty->assign ( 'lectureSelect', $lectureMap );
        return $lectureMap;
    }

    function assignSelectMap()
    {
        $lectureMap = $this->getSelectMap();
        $this->_smarty->assign('lectureSelect', $lectureMap);
        return $lectureMap;
    }

    /* Assign a full list of lectures to 'lectureList' */
    function assignFull()
    {
        $resultset = $this->_dbQueryList();

        self::dumpVar('resultset', $resultset);

        $lectureList = array();
        if (isset ($resultset))
        {
            foreach ($resultset as $key => $val)
            {
                $lectureList[$key]['id'] = $val['id'];
                $lectureList[$key]['code'] = stripslashes($val['code']);
                $lectureList[$key]['title'] = stripslashes($val['title']);
                $lectureList[$key]['syllabus'] = stripslashes($val['syllabus']);
                $lectureList[$key]['locale'] = $val['locale'];
                $lectureList[$key]['term'] = $val['term'];
                //$lectureList[$key]['rootsection'] = $val['rootsection'];
            }
        }

        $this->_smarty->assign('lectureList', $lectureList);
    }

    function assignSingle()
    {
        /* If id == 0, we shall create a new record. */
        if ($this->id)
        {
            /* Query data of this lecture. */
            $this->dbQuerySingle();
            /* And if the result is an empty set, set defaults. */
            if (empty ($this->rs['id']))
            {
                $this->_setDefaults();
            }
        }
        else
        {
            /* Initialize default values. */
            $this->_setDefaults();
        }

        /* And assign it as a 'lecture' to Smarty. */
        $this->_smarty->assign('lectureInfo', $this->rs);

        return $this->rs;
    }

    /* -------------------------------------------------------------------
       HANDLER: SHOW
       ------------------------------------------------------------------- */
    function doShow()
    {
        /* Query data of this lecture and assign them to 'lecture' */
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
        /* Update the record, but do not update the password. */
        $this->dbReplace();
        /* Fetch the updated data of the lecture so that we can write
           something out. */
        $this->assignSingle();
    }

    /* -------------------------------------------------------------------
       HANDLER: DELETE
       ------------------------------------------------------------------- */
    function doDelete()
    {
        /* Just fetch the data of the user to be deleted and ask for
           confirmation. */
        $this->assignSingle();
    }

    /* -------------------------------------------------------------------
       HANDLER: REAL DELETE
       ------------------------------------------------------------------- */
    function doRealDelete()
    {
        /* Fetch the data of the lecture to be deleted before they
           are deleted. */
        $this->assignSingle();
        /* Delete the record */
        DatabaseBean::dbDeleteById();
    }

    /* -------------------------------------------------------------------
       HANDLER: ADMIN
       ------------------------------------------------------------------- */
    function doAdmin()
    {
        /* Get the list of all lectures. */
        $this->assignFull();
        /* Query data of this lecture and assign them to 'lecture' */
        $this->assignSingle();
        /* @todo too many calls to `setLecture` ... this is becoming too
         * complicated to track. */
        SessionDataBean::setLecture($this);
        /* It could have been that doAdmin() has been called from another
           handler. Change the action to "admin" so that ctrl.php will
           know that it shall display the scriptlet for lecture.admin */
        $this->action = "admin";
    }

    /* -------------------------------------------------------------------
       HANDLER: EDIT
       ------------------------------------------------------------------- */
    function doEdit()
    {
        /* If id == 0, we will create a new record. Otherwise, we will
           fetch the lecture data from database. The result will be
           assigned to template variable 'lectureInfo'. */
        $this->assignSingle();
        /* Fetch selectors for term type and group type. */
        $this->assign('select_term', SchoolYearBean::TERMTYPE_LIST());
        $this->assign('select_group', StudentGroupBean::GRPTYPE_LIST());
    }
}

?>
