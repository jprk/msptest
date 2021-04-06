<?php

class FormAssignmentBean extends DatabaseBean
{

    /* Ordering constants. */
    const FA_ORDER_BY_LOGIN = 1;
    const FA_ORDER_BY_NAME = 2;

    var $subtask_id;
    var $assignment_id;
    var $part;
    var $count;
    var $a, $b, $c, $d, $e, $f;
    private $regenerate;
    private $onlynew;
    private $catalogue;
    private $copysub;
    private $pdfimport;

    /* Fill in reasonable defaults. */
    function _setDefaults()
    {
        $this->subtask_id = $this->rs['subtask_id'] = 0;
        $this->assignment_id = $this->rs['assignment_id'] = 0;
        $this->part = $this->rs['part'] = '';
        $this->count = $this->rs['count'] = 0;

        $this->a = $this->rs['a'] = 0;
        $this->b = $this->rs['b'] = 0;
        $this->c = $this->rs['c'] = 0;
        $this->d = $this->rs['d'] = 0;
        $this->e = $this->rs['e'] = 0;
        $this->f = $this->rs['f'] = 0;

        $this->regenerate = $this->rs['regenerate'] = false;
        $this->onlynew = $this->rs['onlynew'] = false;
        $this->catalogue = $this->rs['catalogue'] = false;
        $this->copysub = $this->rs['copysub'] = false;
        $this->pdfimport = $this->rs['pdfimport'] = false;
    }

    /* Constructor */
    /**
     * FormAssignmentBean constructor.
     * @param $id
     * @param $smarty
     * @param $action
     * @param $object
     */
    function __construct($id, & $smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, "formassignmnt", $action, $object);
        /* Initialise new properties to their default values. */
        $this->_setDefaults();
    }

    /**
     *
     */
    function dbReplace()
    {
        DatabaseBean:: dbQuery("DELETE FROM formassignmnt WHERE " . "subtask_id=" . $this->subtask_id . " AND " . "assignmnt_id=" . $this->assignment_id . " AND part='" . $this->dbEscape($this->part) . "'");

        DatabaseBean:: dbQuery("REPLACE formassignmnt VALUES (" . $this->subtask_id . "," . $this->assignment_id . ",'" . $this->dbEscape($this->part) . "'," . $this->count . "," . $this->a . "," . $this->b . "," . $this->c . "," . $this->d . "," . $this->e . "," . $this->f . ")");
    }

    /**
     * Get the full list of records corresponding to the given WHERE clause.
     * If `$where` is empty, returns the full list of all form assignments.
     * @param string $where
     * @return array
     */
    function _getFullList($where = '')
    {
        $rs = DatabaseBean:: dbQuery("SELECT * FROM formassignmnt " .
            $where . " ORDER BY subtask_id,assignmnt_id,part");

        return $rs;
    }

    /**
     * Process POST variables.
     */
    function processPostVars()
    {
        assignPostIfExists($this->subtask_id, $rs, 'subtask_id');
        assignPostIfExists($this->pdfimport, $this->rs, 'pdfimport');
    }

    /**
     *
     */
    function processGetVars()
    {
        assignGetIfExists($this->regenerate, $this->rs, 'regenerate');
        assignGetIfExists($this->catalogue, $this->rs, 'catalogue');
        assignGetIfExists($this->onlynew, $this->rs, 'onlynew');
        assignGetIfExists($this->copysub, $this->rs, 'copysub');
        assignGetIfExists($this->pdfimport, $this->rs, 'pdfimport');
    }

    /**
     * Fetch a complete list of assigments for a list of subtasks.
     * @param $subtaskList
     * @return array
     */
    function getFullSubtaskList($subtaskList)
    {
        $dbList = arrayToDBString($subtaskList);
        $rs = $this->_getFullList(" WHERE subtask_id IN (" . $dbList . ")");
        return $rs;
    }

    /**
     * Get the number of assignment parts for the guiven subtask.
     * @param $subtaskId
     * @return array
     */
    function getParts($subtaskId)
    {
        $rs = DatabaseBean:: dbQuery(
            "SELECT part FROM formassignmnt WHERE subtask_id=" .
            $subtaskId . " GROUP BY part"
        );
        return $rs;
    }

    /**
     * Match the submitted solution with the solution in database.
     * @param $assignmntId
     * @param $part
     * @param $type
     * @param $a
     * @param $b
     * @param $c
     * @param $d
     * @param $e
     * @param $f
     * @param null $g
     * @return float|int
     */
    function matchSolution($assignmntId, $part, $type, $a, $b, $c, $d, $e, $f, $g = NULL)
    {
        $rs = $this->dbQuery("SELECT * FROM formassignmnt WHERE "
            . "subtask_id=" . $this->id . " AND "
            . "assignmnt_id=" . $assignmntId . " AND "
            . "part='" . $part . "'");
        $rs = $rs[0];
        $this->dumpVar('rs_solution', $rs);
        $this->dumpVar('task type', $type);

        $match = 0;
        if ($type == TaskBean::TT_WEEKLY_FORM)
        {
            if ($rs['d'] == 0)
            {
                if ($a == $rs['a'] || $rs['a'] == 0)
                    $match++;
                if ($b == $rs['b'] || $rs['b'] == 0)
                    $match++;
                if ($c == $rs['c'] || $rs['c'] == 0)
                    $match++;
                if ($e == $rs['e'] || $rs['e'] == 0)
                    $match++;
                if ($f == $rs['f'] || $rs['f'] == 0)
                    $match++;
                $this->dumpVar('match d==0 A', $match);
                $match = ($match / 5.0) * 6.0;
                $this->dumpVar('match d==0 B', $match);
            }
            else if ($rs['c'] == 0)
            {
                /* Partial fraction in the form of (Ax+D)/(x+B)+E/(x+F), no
                 * swapping of variables can occur here. */
                if ($a == $rs['a'] || $rs['a'] == 0)
                    $match++;
                if ($b == $rs['b'] || $rs['b'] == 0)
                    $match++;
                if ($d == $rs['d'] || $rs['d'] == 0)
                    $match++;
                if ($e == $rs['e'] || $rs['e'] == 0)
                    $match++;
                if ($f == $rs['f'] || $rs['f'] == 0)
                    $match++;
                $this->dumpVar('match c==0 A', $match);
                $match = ($match / 5.0) * 6.0;
                $this->dumpVar('match c==0 B', $match);
            }
            else
            {
                /* Partial fraction in the form of A/(x+B)+C/(x+D)+E/(x+F) and we
                 * do not know if AB, CD, and EF have not been swapped. */
                $cand = array(
                    array(
                        $b,
                        $a
                    ),
                    array(
                        $d,
                        $c
                    ),
                    array(
                        $f,
                        $e
                    )
                );
                $sols = array(
                    array(
                        $rs['b'],
                        $rs['a']
                    ),
                    array(
                        $rs['d'],
                        $rs['c']
                    ),
                    array(
                        $rs['f'],
                        $rs['e']
                    )
                );
                /* Loop over candidate pairs and try to match them with solutions. */
                foreach ($cand as $cv)
                {
                    /* Look for the given solution pair in the vector of real
                     * solutions. */
                    $idx = array_search($cv, $sols);
                    if ($idx !== false)
                    {
                        /* Delete the existing pair from the real solutions. */
                        unset ($sols[$idx]);
                        $match += 2;
                    }
                }
            }
        }
        elseif ($type == TaskBean::TT_WEEKLY_TF)
        {
            /* Candidate pairs. */
            $cand = array(
                array($a, $b),
                array($c, $d),
                array($e, $f)
            );

            /* Possible solution pairs. */
            $sols = array(
                array($rs['a'], $rs['b']),
                array($rs['a'], -$rs['b']),
                array($rs['c'], 0)
            );

            $this->dumpVar('tf_cand', $cand);
            $this->dumpVar('tf_sols', $sols);
            $this->dumpVar('tf_g', $g);

            /* Loop over candidate pairs and try to match them with solutions. */
            foreach ($cand as $cv)
            {
                /* Look for the given solution pair in the vector of real
                 * solutions. */
                $idx = array_search($cv, $sols);
                if ($idx !== false)
                {
                    /* Delete the existing pair from the real solutions. */
                    unset ($sols[$idx]);
                    /* Add points. */
                    $match += 2;
                }
            }

            /* And match the stability answer. */
            if ($rs['d'] == $g) $match++;
        }
        return $match;
    }

    /**
     * Assign the list of assignment parts to Smarty variable 'parts'.
     * @param $subtaskId
     * @return array
     */
    function assignParts($subtaskId)
    {
        $rs = $this->getParts($subtaskId);
        $this->_smarty->assign('parts', $rs);
        return $rs;
    }

    /**
     * Extend subtask list with the count of subtask parts and the counts of
     * available and generated assignments.
     */
    function updateSubtaskList($subtaskList)
    {
        /* Get the number of generated assignments for subtasks that were
           passed as parameters. */
        $asbean = new AssignmentsBean (0, $this->_smarty, NULL, NULL);
        $gen = $asbean->getNumberOfAssignments($subtaskList);

        /* Select the information about subtasks with assignments stored in the
           form database. */
        $rs = $this->dbQuery(
            "SELECT subtask_id,part,COUNT(*) AS mc " .
            "FROM formassignmnt GROUP BY subtask_id,part"
        );
        /* Result may be empty, in that case we will not update anything. */
        if (!empty ($rs))
        {
            $num = array();
            foreach ($rs as $key => $val)
            {
                $num[$val['subtask_id']] = $val['mc'];
            }
            foreach ($subtaskList as $key => $val)
            {
                /* Temporary index. */
                $tid = $val['id'];
                $subtaskList[$key]['count'] = array_key_exists($tid, $num) ? $num[$tid] : 0;
                $subtaskList[$key]['generated'] = array_key_exists($tid, $gen) ? $gen[$tid] : 0;
            }
        }

        return $subtaskList;
    }

    /**
     *  Reduce student list to students without assignment.
     */
    function getReducedStudentIdList($subtaskId, $lectureId)
    {
        /* Get a list containing 'student_id' elements holding the
           student ids. */
        $rs = $this->dbQuery(
            "SELECT sl.student_id FROM stud_lec AS sl " .
            "LEFT JOIN assignmnts AS ag ON " .
            "( ag.subtask_id=" . $subtaskId . " " .
            "AND ag.year=sl.year " .
            "AND sl.student_id=ag.student_id ) WHERE " .
            "sl.lecture_id=" . $lectureId . " AND " .
            "sl.year=" . $this->schoolyear . " AND " .
            "ag.assignmnt_id IS NULL"
        );

        /* Result array. */
        $ret = array();

        /* Transform the above list into an array of student ids
           as expected by generateAssignments(). */
        if (!empty ($rs))
        {
            foreach ($rs as $val)
            {
                $ret[] = $val['student_id'];
            }
        }

        $this->dumpVar('reduced rs', $ret);

        return $ret;
    }

    /**
     *  Generate a single file containing all assignments with solutions
     *  ordered by student login.
     */
    function generateAssignmentCatalogue($subtaskId, $orderType)
    {
        if ($orderType == self::FA_ORDER_BY_NAME)
        {
            $orderStr = "st.surname,st.firstname";
        }
        else
        {
            $orderStr = "st.login";
        }

        /* Get the code of the subtask id. */
        $subtaskBean = new SubtaskBean(0, $this->_smarty, "", "");
        $sCode = $subtaskBean->getSubtaskCode($subtaskId);

        /* Construct the file bean that implements also all operations on 
         assigment files. */
        $fileBean = new FileBean(0, $this->_smarty, "", "");

        /* Construct the assignments bean that interconnects the subtask
           and studetna dn dile data. */
        $assignmentsBean = new AssignmentsBean(0, $this->_smarty, "", "");

        /* Select the assignments for this subtask ans schoolyear, combine
           them with student data. */
        $stYear = SessionDataBean::getSchoolYear();
        $stagList = DatabaseBean::dbQuery(
            "SELECT st.login,st.firstname,st.surname,st.yearno,st.groupno,assignmnt_id " .
            "FROM assignmnts AS ag " .
            "LEFT JOIN student AS st ON ag.student_id=st.id " .
            "WHERE subtask_id=" . $subtaskId . " AND year=" . $stYear . " " .
            "ORDER BY " . $orderStr . ";"
        );

        /* Change to the directory where files shall be generated. */
        $tGeneBase = "generated/" . $sCode . "/";
        $base = CMSFILES . "/" . $tGeneBase;
        @ mkdir($base);
        chdir($base);

        /* Write the template tex file. */
        $cmsFileBase = $tGeneBase . $sCode . "_inc";
        $filename = CMSFILES . "/" . $cmsFileBase . ".tex";
        $handle = fopen($filename, "w");

        foreach ($stagList as $key => $val)
        {
            /* Prepare the student data. */
            $u8name = $val['firstname'] . " " . $val['surname'];
            $name = $val['login'] . " (" . iconv("utf-8", "windows-1250", $u8name) . ")";
            $group = $val['yearno'] . "/" . $val['groupno'];
            $id = sprintf("%05d", $val['assignmnt_id']);
            /* Transform the template into assignment file. */
            $texstr = "\\{$sCode}sol{{$id}}{{$name}}{{$group}}\n";
            fwrite($handle, $texstr);
        }

        fclose($handle);

        /* This is the base file that includes the file created above. */
        $tBaseDir = CMSFILES . "/assignments/" . $sCode . "/";
        $filename = $tBaseDir . $sCode . "_catalogue.tex";

        /* LaTeX it. */
        $ret = system("TEXINPUTS=`kpsexpand -p tex`:$tBaseDir pdflatex -interaction=batchmode " . $filename . " > /dev/null ");
        //$ret = system ( "TEXINPUTS=`kpsexpand -p tex`:$tBaseDir pdflatex ".$filename." " );
        echo "<!-- " . $ret . "-->\n";
        //system ( 'rm -f *.tex *.log *.aux');
    }

    /**
     * Generate a new set of PDF files for existing assignments.
     * This can be used for example in case of an error in assignment text, as it will generate new assignment files
     * for the given subtask using already generated assignment ids. The files will be placed in a directory
     * `<subtask_code>r` and no cleaning of intermediate TeX files will occur.
     * @param $subtaskId  integer Identifier of the subtask we will operate on
     */
    function regenerateAssignments($subtaskId)
    {
        error_log("FormAssignmentBean::regenerateAssignments(subtaskId=$subtaskId)");

        $subtaskBean = new SubtaskBean(0, $this->_smarty, null, null);
        $sCode = $subtaskBean->getSubtaskCode($subtaskId);

        /* Construct the file bean that implements also all operations on
           assignment files. */
        // $fileBean = new FileBean(0, $this->_smarty, null, null);

        /* Construct the assignments bean that interconnects the subtask
           and studetna dn dile data. */
        // $assignmentsBean = new AssignmentsBean(0, $this->_smarty, null, null);

        /* Select the assignments for this subtask and the current year from
           the database. */
        $assignmentList = $this->dbQuery(
            "SELECT * FROM assignmnts WHERE subtask_id=" . $subtaskId .
            " AND year=" . $this->schoolyear
        );

        /* Read the template. */
        $tBaseDir = CMSFILES . "/assignments/" . $sCode . "/";
        $tFileName = $tBaseDir . $sCode . ".tex";
        $handle = fopen($tFileName, "r");
        $templatestr = fread($handle, filesize($tFileName));
        fclose($handle);
        error_log("* template $tFileName read");

        /* Make the directories where files shall be generated. Change to the
           target directory */
        $tGeneBase = "generated/" . $sCode . "r/" . $this->schoolyear . "/";
        $base = CMSFILES . "/" . $tGeneBase;
        @ mkdir($base, 0777, true);
        chdir($base);
        error_log("* changed directory to $base");

        /* Erase all remaining files in the directory. */
        //system ( 'rm -f *');

        foreach ($assignmentList as $key => $val)
        {
            /* Get the student data. */
            $rs = $this->dbQuery(
                "SELECT * FROM student WHERE id=" . $val['student_id']);
            if (empty($rs))
            {
                trigger_error(
                    "No record for student " . $val['student_id'] . " in `student` table?");
                continue;
            }
            /* Returned result set is an array, therefore we have to copy
               its first (and only) element out. */
            $sval = $rs[0];

            /* Prepare translation table. */
            $codes = array(
                "@DATE@",
                "@NAME@",
                "@GROUP@",
                "@ID@"
            );
            $date = date("d.m.Y");
            $u8name = $sval['firstname'] . " " . $sval['surname'];
            $name = iconv("utf-8", "windows-1250", $u8name);
            $group = $sval['yearno'] . "/" . $sval['groupno'];
            $id = sprintf("%05d", $val['assignmnt_id']);
            $replc = array(
                $date,
                $name,
                $group,
                $id
            );

            /* Transform the template into assignment file. */
            $texstr = str_replace($codes, $replc, $templatestr);

            /* Write the template tex file. The file name has to contain the student id
               because there is possibility of duplicated `assignment_id` values in case
               when the number of students is higher than the number of assignments.*/
            $cmsFileName = $sval['login'] . "_" . $sCode . "_" . $id;
            $cmsFileBase = $tGeneBase . $cmsFileName;
            $filename = CMSFILES . "/" . $cmsFileBase . ".tex";
            $handle = fopen($filename, "w");
            fwrite($handle, $texstr);
            fclose($handle);
            error_log("* written $filename, base dir $tBaseDir");

            /* And LaTeX it. */
            $command = "TEXINPUTS=`kpsexpand -p tex`:$tBaseDir pdflatex -interaction=batchmode " . $filename . " > /dev/null ";
            error_log("* executing: $command");
            $ret = system($command);
            //$ret = system ( "TEXINPUTS=`kpsexpand -p tex`:$tBaseDir pdflatex ".$filename_tex." " );
            //echo "<!-- ".$ret."-->";
            error_log("* pdflatex call returned $ret");
        }
    }

    /**
     * Create PDF files with individual assignments.
     *
     * Usually typesets PDF documents with assignment text, one document for each student in `$studentList`,
     * wiping out all existing files in the corresponding directory identified by a subtask code of `$subtaskId`.
     * In case that `$isUpdate` is `true`, assignments will be generated but already existing files will not be
     * deleted.
     *
     * The assignment ids are selected randomly from the predefined set of assignments, unless `$fromSubtask`
     * is nonzero - in such a case the assignment ids are copied from those of subtask id `$fromSubtask`
     * (this might make sense if we have a sequence of assignments where students work on the same problem from
     * different viewpoints).
     *
     * In case that `$copy_from_subtask` is nonzero and the template file is missing, the reference to
     * the generated files is replaced by the reference to files of the `$copy_from_subtask`. This is used to
     * provide students with identical assignment files in case that the assignment has different phases and
     * files have been imported from outside (i.e. we have no template to generate assignment PDFs from).
     *
     * @param $id_subtask   integer Identifier of the subtask we will operate on
     * @param $id_list array   List of student/group identifiers
     * @param $isUpdate    boolean Do not erase older assignment files
     * @param $copy_from_subtask integer Copy assignment ids from this subtask
     * @throws Exception
     */
    function generateAssignments($id_subtask, $id_list, $isUpdate, $copy_from_subtask)
    {
        error_log("FormAssignmentBean::generateAssignments(id_subtask=$id_subtask,id_list=$id_list,is_update=$isUpdate,copy_from_subtask=$copy_from_subtask)");

        /* Get the code of the subtask id. */
        $subtaskBean = new SubtaskBean ($id_subtask, $this->_smarty, NULL, NULL);
        $subtask_code = $subtaskBean->getSubtaskCode($id_subtask);

        /* Construct the file DAO that implements also all operations on assignment files. */
        $fileBean = new FileBean (NULL, $this->_smarty, NULL, NULL);

        /* Construct DAO that interconnects the subtask, the student, and the file data. */
        $assignmentsBean = new AssignmentsBean (NULL, $this->_smarty, NULL, NULL);

        /* The number of assignments to generate is given by the number of ids in the id_list. */
        $num_assignments = count($id_list);

        /* Check the mode of new assignment selection. */
        $assignments_from_subtask = array();
        $new_assignment_ids = array();
        if ($copy_from_subtask > 0)
        {
            error_log("* copying from subtask $copy_from_subtask");
            /* Copy records from assignments of another task. */
            $assignment_list = $assignmentsBean->getAssignmentList($copy_from_subtask);
            /* We have to transform the list into a list indexed by student id. */
            foreach ($assignment_list as $val)
            {
                $assignments_from_subtask[$val['student_id']] = $val;
            }
            self::dumpVar('studentAssignments', $assignments_from_subtask);
        }
        else
        {
            /* Randomly select a number of records from the database. */
            error_log("* randomly selecting new assignments");
            $new_assignment_ids = DatabaseBean:: dbQuery("SELECT DISTINCT(assignmnt_id) FROM formassignmnt WHERE " .
                "subtask_id=" . $id_subtask . " ORDER BY count,RAND() " .
                "LIMIT " . $num_assignments);
            self::dumpVar("assignment ids", $new_assignment_ids);
        }

        $template_base_dir = CMSFILES . "/assignments/" . $subtask_code . "/";
        $generated_dir_path = "generated/" . $subtask_code . "/" . $this->schoolyear . "/";
        $template_file_name = $template_base_dir . $subtask_code . ".tex";
        error_log("* template_file_name=$template_file_name");

        /* Check the presence of the template file. */
        if (is_file($template_file_name))
        {
            /* Read the template. */
            $handle = fopen($template_file_name, "r");
            $template_as_string = fread($handle, filesize($template_file_name));
            fclose($handle);
            error_log("* template read");
        }
        else
        {
            /* Signal missing template by setting the template string to null. */
            $template_as_string = null;
            error_log("* template not available");
        }

        /* Change to the directory where files shall be generated, possibly creating it. */
        $generated_base_dir = CMSFILES . "/" . $generated_dir_path;
        if (!is_dir($generated_base_dir)) mkdir($generated_base_dir, 0775, true);
        chdir($generated_base_dir);
        error_log("* working directory changed to $generated_base_dir");

        /* Are we updating the existing set of assignments (for example due to typo or an error in the template)? */
        if (!$isUpdate)
        {
            /* Erase all file records for this task. */
            $fileBean->clearAssignmentFiles($id_subtask);

            /* Erase all remaining files in the directory. */
            system('rm -f *');
            error_log("* removed all files from directory");
        }

        /* Make a list of students that were ignored during the copy_from_subtask operation due to non-existent
           original assignment. */
        $ignored_students = array();
        $pos = 0;
        foreach ($id_list as $key => $val)
        {
            /* Remember the student id and login. */
            $studentId = $val['id'];
            $studentLogin = $val['login'];

            $date = date("d.m.Y");
            $u8name = $val['firstname'] . " " . $val['surname'];
            // $name = iconv("utf-8", "windows-1250", $u8name);
            $name = $u8name;
            $group = $val['yearno'] . "/" . $val['groupno'];

            /* Allow copying assignment ids from other subtasks. */
            if ($copy_from_subtask > 0)
            {
                /* It could happen that a student that is still in the list of students does not have an
                   assignment - this is quite frequent in lectures in the first and second year of study, where
                   students tend to drop off the faculty after enrolling for the current semester. */
                if (array_key_exists($studentId, $assignments_from_subtask))
                {
                    $student_assignment = $assignments_from_subtask[$studentId];
                    $id = $student_assignment['assignmnt_id'];
                }
                else
                {
                    $ignored_students[] = $val;
                    continue;
                }
            }
            else
            {
                $student_assignment = null; // so that the code analyzer does not complain
                $id = $new_assignment_ids[$pos]['assignmnt_id'];
            }

            /* Now check whether the template really exists and if so, use it to generate the subtask PDF. */
            if ($template_as_string)
            {
                /* Yep, we have a template. We will use batch replace using `str_replace` and for this we need
                   an array of replacement codes and array of strings that will replace them. */
                $template_codes = array(
                    "@DATE@",
                    "@NAME@",
                    "@GROUP@",
                    "@ID@"
                );
                $replacement_texts = array(
                    $date,
                    $name,
                    $group,
                    $id
                );

                /* Transform the template into assignment file. */
                $latex_src_string = str_replace($template_codes, $replacement_texts, $template_as_string);

                /* Record the assignment id for this student. */
                $id_list[$key]['assignmnt_id'] = $id;

                /* Write the template tex file. The file name has to contain the student id
                   because there is possibility of duplicated `assignment_id` values in case
                   when the number of students is higher than the number of assignments.*/
                $cmsFileName = $studentLogin . "_" . $subtask_code . "_" . $id;
                $cmsFileBase = $generated_dir_path . $cmsFileName;
                $filename_base = CMSFILES . "/" . $cmsFileBase;
                $filename_tex = $filename_base . ".tex";
                $filename_pdf = $filename_base . ".pdf";
                $handle = fopen($filename_tex, "w");
                fwrite($handle, $latex_src_string);
                fclose($handle);
                error_log("* wrote LaTeX to $filename_tex");

                /* And LaTeX it. */
                $command = "TEXINPUTS=`kpsexpand -p tex`:$template_base_dir pdflatex -interaction=batchmode " . $filename_tex . " > /dev/null ";
                error_log("* executing: $command");
                $ret = system($command);
                //$ret = system ( "TEXINPUTS=`kpsexpand -p tex`:$tBaseDir pdflatex ".$filename_tex." " );
                //echo "<!-- ".$ret."-->";
                error_log("* prflatex call returned $ret");

                /* Check that a PDF file has been produced. */
                if (!is_file($filename_pdf))
                {
                    throw new Exception("Failed to generate $filename_pdf");
                }

                system('rm -f *.tex *.log *.aux');

                /* Store information about the generated file in file table. */
                $cmsFileName = $cmsFileName . ".pdf";
                $cmsFileBase = $cmsFileBase . ".pdf";
                $fileId = $fileBean->addFile(FT_X_INDIV_ASSIGNMENT, $id_subtask, $studentId, $cmsFileBase, $cmsFileName,
                    "Úloha " . $subtask_code . ", příklad " . $id . ", student " . $u8name);
                error_log("* new file ID $fileId created for $cmsFileName");
            }
            else
            {
                /* No template. We will replace the reference to a generated file of this assignment with a reference
                   to an existing file of another assignment. */
                $fileId = $student_assignment['file_id'];
            }

            /* And finally update information about this assignment in the assignment mapping table. */
            $assignmentsBean->setAssignment($studentId, $id_subtask, $id, $fileId);

            /* The last step is counter update - we have to increase the counter for all records in `formassignmnt`
               table with the given `$subtaskId` and `$assignment_id`.
               @fixme Shouldn't the `forassignmnt` table be update in all cases? */
            if ($copy_from_subtask == 0)
            {
                $this->dbQuery(
                    "UPDATE formassignmnt SET count=count+1 " .
                    "WHERE subtask_id=" . $id_subtask . " " .
                    "AND assignmnt_id=" . $id
                );
            }

            /* Move to the next assignment record in the `$new_assignment_ids` array. */
            $pos++;
        }

        /* Pass information about non-existent template to Smarty so that we can react on it in presentation
           layer. */
        $this->assign('template_found', $template_as_string !== null);
        $this->assign('ignored_students', $ignored_students);

        if ($copy_from_subtask > 0)
        {
            $sb = new SubtaskBean($copy_from_subtask, $this->_smarty, null, null);
            $sb->dbQuerySingle();
            $this->assign('copysub', $sb->rs);
        }
    }

    function assignFull()
    {
        $rs = $this->_getFullList();
        $this->_smarty->assign('subtaskList', $rs);
        return $rs;
    }

    function assignSingle()
    {
    }

    /* -------------------------------------------------------------------
       HANDLER: SHOW
       ------------------------------------------------------------------- */
    function doShow()
    {
        /* Permission check - show() is not allowed for everyone. */
        if (!UserBean::isRoleAtLeast(SessionDataBean::getUserRole(), USR_LECTURER))
        {
            /* Signalise that the current user does not have permission
               for this action. */
            $this->action = 'e_perm';
            return;
        }

        /* Process and assign additional variables that we submitted as a part
           of a GET query. */
        $this->processGetVars();

        /* Get the lecture id. Lecture description is loaded automatically. */
        $lectureId = SessionDataBean::getLectureId();

        /* Load information of the subtask. */
        $subtaskBean = new SubtaskBean ($this->id, $this->_smarty, NULL, NULL);
        $subtaskBean->assignSingle();

        /* Generate new task set. First get the list of students. */
        /* Get the list of all exercises, assign it to the Smarty variable
           'exerciseList' and return it to us as well, we will need it later.
           $this->id will point to the lecture_id in this case. */
        //$exerciseBean = new ExerciseBean ( NULL, $this->_smarty, NULL, NULL );
        //$exerciseList = $exerciseBean->assignFull ( $lectureId );

        if (!empty ($this->catalogue))
        {
            /* Create a PDF catalogue with assignment solutions, ordered
               by student login. */
            $this->generateAssignmentCatalogue($this->id, $this->catalogue);
        }
        elseif (!empty ($this->regenerate))
        {
            /* Create again the PDF files with assignment text (useful in
               cases where the original .tex files contain some errors). */
            $this->regenerateAssignments($this->id);
        }
        else
        {
            /* Create an instance of StudentBean that will be used to query
               information about students. */
            $studentBean = new StudentBean (NULL, $this->_smarty, NULL, NULL);

            /* Check if we shall just update assignments for students that
               were added later or generate a completely new set of assignments. */
            $isUpdate = (!empty ($this->onlynew));

            if ($isUpdate)
            {
                /* Create assignments only for students that have been added
                   later and do not have the assignments generated yet. */
                $ids = $this->getReducedStudentIdList($this->id, $lectureId);
                $studentList = $studentBean->assignStudentIdList($ids);
            }
            else
            {
                /* Get the list of all students that are currently subscribed for the
                   lecture. */
                $studentList = $studentBean->dbQueryStudentListForLecture($lectureId);
            }

            /* Create the files with assignment text (one for each student, but note below), possibly generating
               only assignments for new students (in case that $isUpdate is true) and possibly not generating
               a new random set of assignments, but copying the ids of assignments from another
               assignment (this might make sense if we have a sequence of assignments where students work
               on the same problem from different viewpoints).
               Note: In some cases the `copysub` will just duplicate links to existing files, in fact not generating
               anything. This is controlled by the absence of the template file for the given subtask. */
            $this->generateAssignments($this->id, $studentList, $isUpdate, $this->copysub);
        }

        $this->assign('formassignment', $this->rs);
    }

    function uploadAssignmentCSV()
    {
        /* Open the file. */
        error_log('uploadAssignmentCSV');
        $handle = @ fopen($_FILES['assignfile']['tmp_name'], "r");
        if ($handle)
        {
            /* TODO: Only CSV format allowed here. Currently we do not check it. */
            /* Can be opened, it shall be a CSV, so delete all previous data
               from the table and parse the lines. */
            error_log('handle exists, processing CSV');
            $this->dbQuery(
                'DELETE FROM formassignmnt WHERE subtask_id=' .
                $this->subtask_id
            );
            /* CSV loop. */
            while (!feof($handle))
            {
                $buffer = fgets($handle, 4096);
                $trimmed = trim($buffer);

                /* Ignore empty lines. */
                if (empty ($trimmed))
                    continue;

                $la = explode(";", $trimmed);
                echo "\n<!-- la=";
                print_r($la);
                error_log('la=' . print_r($la, true));
                echo " -->";

                /* The record has to have 8 elements. Skip it otherwise. */
                if (count($la) != 8)
                    continue;

                $this->assignment_id = trim($la[0], " \t\n\r\"");
                $this->part = trim($la[1], " \t\n\r\"");
                $this->a = trim($la[2], " \t\n\r\"");
                $this->b = trim($la[3], " \t\n\r\"");
                $this->c = trim($la[4], " \t\n\r\"");
                $this->d = trim($la[5], " \t\n\r\"");
                $this->e = trim($la[6], " \t\n\r\"");
                $this->f = trim($la[7], " \t\n\r\"");
                $this->count = 0;

                $this->dbReplace();
            }

            fclose($handle);
        }
        else
        {
            /* Cannot open the file for reading. */
            $this->action = "e_cantread";
        }
    }

    /**
     * @throws Exception
     */
    function uploadAssignmentsPDFZIP()
    {
        /* Due to its typical size (often 30-60MB), the ZIP file is uploaded manually to
           a pre-defined directory on the server.
           Check that the file exists. */
        $file_name = SessionDataBean::getCode() . '-' . $this->schoolyear . '-' . $this->id . '.zip';
        $this->assign('file_name', $file_name);
        $file_path = STATICFILES . '/upload/' . SessionDataBean::getCode() . '/' . $file_name;
        $this->dumpVar('full zip file path', $file_path);
        if (!is_file($file_path))
        {
            throw new Exception(
                "Path $file_path is not a file!"
            );
        }
        /* Only ZIP format allowed here. */
        $zip = new ZipArchive();
        /* Open the file. */
        $res = $zip->open($file_path, ZipArchive::CHECKCONS);
        if ($res===true)
        {
            /* Can be opened, extract it to the directory where PDFs shall be generated.
               First get the code of the subtask id.
               TODO: We have constructed another SubtaskBean in doSave() ... */
            $subtaskBean = new SubtaskBean ($this->id, $this->_smarty, null, null);
            $subtask_code = $subtaskBean->getSubtaskCode($this->id);

            /* Construct the path where the PDFs should land and make sure it exists. */
            $gendir_path = "/generated/" . $subtask_code . "/" . $this->schoolyear . "/";
            $full_gendir_path = CMSFILES . $gendir_path;
            $this->dumpVar('full_gendir_path', $full_gendir_path);
            if (!is_dir($full_gendir_path))
            {
                mkdir($full_gendir_path, 0775, true);
            }
            else
            {
                /* Directory already exists, clean it. */
                removeDirContent($full_gendir_path);
            }

            /* And extract the ZIP file there. */
            $zip->extractTo($full_gendir_path);

            /* Sanity check. Loop over all files and check that they correspond to the expected pattern. */
            $lecture_code = SessionDataBean::getCode();
            $lecture_year = $this->schoolyear;  // cannot use $this->... inside "..."
            $error_str = "";
            $file_list = array();
            $files = glob($full_gendir_path . '*', GLOB_MARK);
            foreach ($files as $file)
            {
                if (is_dir($file))
                {
                    $error_str = "Archive is not in expected format. Found top-level subdirectory <tt>$file</tt>.";
                    break;
                }
                $file_info = pathinfo($file);
                list($file_code, $year, $num) = explode('-', $file_info['filename']);
                if ($file_code != $lecture_code)
                {
                    $error_str .= "<li>File code <tt>$file_code</tt> but <tt>$lecture_code</tt> expected.</li>";
                }
                if (empty($year))
                {
                    if (!empty($error_str)) $error_str .= "\n";
                    $error_str .= "<li>File year is empty, but <tt>$lecture_year</tt> expected.</li>";
                }
                elseif ($year != $this->schoolyear)
                {
                    if (!empty($error_str)) $error_str .= "\n";
                    $error_str .= "<li>File year <tt>$year</tt> but <tt>$lecture_year</tt> expected.</li>";
                }
                if (empty($num))
                {
                    if (!empty($error_str)) $error_str .= "\n";
                    $error_str .= "<li>File number is empty, but 5 digits expected.</li>";
                }
                elseif (strlen($num) != 5 || intval($num) <= 0 )
                {
                    if (!empty($error_str)) $error_str .= "\n";
                    $error_str .= "<li>File number is <tt>$num</tt> but 5 digits expected.</li>";
                }
                if ($file_info['extension'] != 'pdf')
                {
                    if (!empty($error_str)) $error_str .= "\n";
                    $error_str .= "<li>File extension <tt>" . $file_info['extenstion'] . "</tt> but <tt>pdf</tt> expected.</li>";
                }
                if (!empty($error_str)) {
                    $error_str = "<p>Inconsistencies in filename <tt>$file</tt>:</p>\n<ul>\n" . $error_str . "\n</ul>\n";
                    break;
                }
                /* Now it seems that the file is genuine, add information about it to the file list. */
                $file_list[intval($num)] = $gendir_path . $file_info['filename'] . '.' . $file_info['extension'];
            }

            if (!empty($error_str))
            {
                $this->assign('error_str',$error_str);
                $this->action = 'e_zipformat';
                return;
            }

            /* The format of the archive seems to be okay. As a precaution check that the number of students
               or the number of student groups is lower or equal to the number of assignments. */
            switch (SessionDataBean::getLectureGroupType())
            {
                case StudentGroupBean::GRPTYPE_NONE:
                    /* No student group, the number of assignments has to be greater or equal to the number
                       of students. */
                    break;
                case StudentGroupBean::GRPTYPE_EXERCISE:
                case StudentGroupBean::GRPTYPE_LECTURE;
                    /* Student groups, the number of assignments has to be greater or equal to the number of
                       student groups. */
                    $grpb = new StudentGroupBean(null, $this->_smarty, null, null);
                    $grp_count = $grpb->getGroupCount();  // by default it returns the number of groups for this lecture and year
                    $this->dumpVar('file count',count($files));
                    $this->dumpVar('grp_count',$grp_count);
                    $this->dumpVar('file_list',$file_list);
                    if (count($files) < $grp_count)
                    {
                        $this->action = 'e_grpcount';
                        return;
                    }
                    /* Construct the file DAO that implements also all operations on assignment files. */
                    $fileBean = new FileBean (null, $this->_smarty, null, null);
                    $sgb = new StudentGroupBean(null, $this->_smarty, null, null);
                    $ab = new AssignmentsBean(null, $this->_smarty, null, null);
                    /* Store information about all files in the database. */
                    foreach ($file_list as $num => $file)
                    {
                        /* Determine group id for this file. In case that $num is greater than the
                           number of opened groups, $grpid will be zero and such group will be ignored. */
                        $grpid = $sgb->getGroupIdByNum($num);
                        if ($grpid > 0 ) {
                            /* Record information into the database. */
                            $file_id = $fileBean->addFile(FT_X_GROUP_ASSIGNMENT, $this->id, $grpid,
                                $file, basename($file), "Úloha $subtask_code, zadání pro skupinu $num");
                            /* Loop over all students of the group and create assignment record for them. */
                            $student_list = $sgb->getStudentsIdsForGroupId($grpid);
                            $this->dumpVar('student_list for ' . $file, $student_list);
                            foreach ($student_list as $student_id) {
                                $ab->setAssignment($student_id, $this->id, $num, $file_id);
                            }
                        }
                    }
                    break;
                default:
                    throw new Exception("Unknown student group type in __method__");
            }
        }
        else
        {
            /* Cannot open the file for reading. */
            $this->action = "e_nozipfile";
        }
    }

    /* -------------------------------------------------------------------
       HANDLER: ADMIN
       ------------------------------------------------------------------- */
    function doAdmin()
    {
        /* Get a lecture that this subtask is related to. */
        $lectureBean = new LectureBean($this->id, $this->_smarty, "", "");
        $lectureBean->assignSingle();
        /* Get a list of subtask types. */
        $subtaskBean = new SubtaskBean(0, $this->_smarty, "", "");
        $subtaskList = $subtaskBean->getForLecture($this->id, array(
            TaskBean::TT_WEEKLY_FORM,
            TaskBean::TT_WEEKLY_SIMU,
            TaskBean::TT_WEEKLY_PDF,
            TaskBean::TT_WEEKLY_ZIP,
            TaskBean::TT_WEEKLY_TF,
            TaskBean::TT_SEMESTRAL_INDIV_PDF
        ));
        /* Add count and publish it. */
        $subtaskList = $this->updateSubtaskList($subtaskList);
        $this->_smarty->assign('subtaskList', $subtaskList);
        /* It could have been that doAdmin() has been called from another
               handler. Change the action to "admin" so that ctrl.php will
               know that it shall display the scriptlet for section.admin */
        $this->action = "admin";
    }

    /* -------------------------------------------------------------------
       HANDLER: EDIT
       ------------------------------------------------------------------- */
    function doEdit()
    {
        /* There is no edit for this type of object. User may just import
        another set of subtasks. */

        /* Get a list of subtask types. */
        $subtaskBean = new SubtaskBean($this->id, $this->_smarty, "", "");
        $subtaskBean->assignSingle();

        /* Process and assign additional variables that we submitted as a part
           of a GET query. */
        $this->processGetVars();

        /* Check what sub-action we were asked to perform. */
        if (!empty ($this->copysub))
        {
            /* Display a list of all subtasks that may server as a source
               of "copy subtask assignments" operation. */
            $subtaskBean->assignStudentSubtaskList();
            $this->action = 'edit.copysub';
        }
        elseif (!empty($this->pdfimport))
        {
            $this->action = 'edit.pdfimport';
        }

        $this->assign('formassignment', $this->rs);
    }

    /* -------------------------------------------------------------------
       HANDLER: SAVE
       ------------------------------------------------------------------- */
    function doSave()
    {
        /* Assign POST variables to internal variables of this class and
           remove evil tags where applicable. */
        $this->processPostVars();
        /* Get the description of the current subtask. */
        $subtaskBean = new SubtaskBean($this->id, $this->_smarty, "", "");
        $subtaskBean->assignSingle();

        $this->dumpVar('$_FILES',$_FILES);
        /* Check the uploaded file. */
        if (array_key_exists('assignfile', $_FILES) && is_uploaded_file($_FILES['assignfile']['tmp_name']))
        {
            $this->uploadAssignmentCSV();
        }
        elseif ($this->pdfimport)
        {
            $this->uploadAssignmentsPDFZIP();
            $this->action = 'save.pdfimport';
        }
        else
        {
            /* Possible file uplad attack. */
            $this->action = "e_uploadattack";
        }
    }

    /* -------------------------------------------------------------------
       HANDLER: DELETE
       ------------------------------------------------------------------- */
    function doDelete()
    {
        $this->assignSingle();

        /* Get a lecture that this subtask is related to. */
        $lectureBean = new LectureBean($this->id, $this->_smarty, "", "");
        $lectureBean->assignSingle();
    }

    /* -------------------------------------------------------------------
       HANDLER: REAL DELETE
       ------------------------------------------------------------------- */
    function doRealDelete()
    {
        $this->assignSingle();
        /* Delete the record */
        DatabaseBean:: dbDeleteById();

        /* Get a lecture that this subtask is related to. */
        $lectureBean = new LectureBean($this->id, $this->_smarty, "", "");
        $lectureBean->assignSingle();
    }
}

?>
