<?php

class FormSolutionsBean extends DatabaseBean
{
    var $student_id;
    var $subtask_id;
    var $assignmnt_id;
    var $part;
    var $a, $b, $c, $d, $e, $f, $g, $h;
    var $timestamp;
    var $aa, $bb, $cc, $dd, $ee, $ff, $gg, $hh;

    private $confirmed;

    private static $templateMap = array(
        TT_WEEKLY_SIMU => 'formsolution.simu',
        TT_WEEKLY_PDF => 'formsolution.pdf',
        TT_WEEKLY_ZIP => 'formsolution.zip',
        TT_WEEKLY_TF => 'formsolution.tf'
    );

    private static $stabilityMap = array(
        0 => 'stabilní',
        1 => 'na mezi stability',
        2 => 'nestabilní',
    );

    /* Fill in reasonable defaults. */
    function _setDefaults()
    {
        $this->student_id = $this->rs['student_id'] = 0;
        $this->subtask_id = $this->rs['subtask_id'] = 0;
        $this->assignmnt_id = $this->rs['assignmnt_id'] = 0;
        $this->part = $this->rs['part'] = '';

        $this->a = $this->aa = $this->rs['a'] = NULL;
        $this->b = $this->bb = $this->rs['b'] = NULL;
        $this->c = $this->cc = $this->rs['c'] = NULL;
        $this->d = $this->dd = $this->rs['d'] = NULL;
        $this->e = $this->ee = $this->rs['e'] = NULL;
        $this->f = $this->ff = $this->rs['f'] = NULL;
        $this->g = $this->gg = $this->rs['g'] = NULL;
        $this->h = $this->hh = $this->rs['h'] = NULL;

        $this->timestamp = $this->rs['timestamp'] = '';
        $this->confirmed = $this->rs['confirmed'] = false;
    }

    /* Constructor */
    function __construct($id, &$smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, "formsolutions", $action, $object);
        /* Initialise new properties to their default values. */
        $this->_setDefaults();
    }

    function dbReplace()
    {
        if ($this->a === NULL) $this->a = 'NULL';
        if ($this->b === NULL) $this->b = 'NULL';
        if ($this->c === NULL) $this->c = 'NULL';
        if ($this->d === NULL) $this->d = 'NULL';
        if ($this->e === NULL) $this->e = 'NULL';
        if ($this->f === NULL) $this->f = 'NULL';
        if ($this->g === NULL) $this->g = 'NULL';
        if ($this->h === NULL) $this->h = 'NULL';

        DatabaseBean::dbQuery(
            "REPLACE formsolutions VALUES ("
            . $this->student_id . ","
            . $this->subtask_id . ","
            . $this->assignmnt_id . ",'"
            . mysql_escape_string($this->part) . "',"
            . $this->a . ","
            . $this->b . ","
            . $this->c . ","
            . $this->d . ","
            . $this->e . ","
            . $this->f . ","
            . $this->g . ","
            . $this->h . ", NULL)"
        );
    }

    /**
     * Update POST variables.
     */
    function processPostVars()
    {
        assignPostIfExists($this->aa, $this->rs, 'a');
        assignPostIfExists($this->bb, $this->rs, 'b');
        assignPostIfExists($this->cc, $this->rs, 'c');
        assignPostIfExists($this->dd, $this->rs, 'd');
        assignPostIfExists($this->ee, $this->rs, 'e');
        assignPostIfExists($this->ff, $this->rs, 'f');
        assignPostIfExists($this->gg, $this->rs, 'g');
        assignPostIfExists($this->hh, $this->rs, 'h');
        assignPostIfExists($this->confirmed, $this->rs, 'confirmed');
    }

    function haveSolution($subtaskId, $students, $assignmentId)
    {
        /* Limit the solution check to solutions submitted within the
           limits of the actual school year.
           TODO: possibly extend `formsolutions` table with schoolyear column. */
        $this->dumpVar('students',$students);
        $student_id_list = array2ToDBString($students, 'id');
        $limits = SchoolYearBean::getTermLimits($this->schoolyear, SessionDataBean::getLectureTerm());
        $rs = DatabaseBean::dbQuery(
            "SELECT * FROM formsolutions WHERE "
            . "subtask_id=" . $subtaskId . " AND "
            . "student_id IN (" . $student_id_list . ") AND "
            . "assignmnt_id=" . $assignmentId . " AND "
            . "timestamp>='" . $limits['from'] . "' AND "
            . "timestamp<='" . $limits['to'] . "'"
        );

        if (!empty ($rs))
        {
            $this->assign('timestamp', $rs[0]['timestamp']);
            $student_id = $rs[0]['student_id'];
            $this->assign('uploader', $students[$student_id]);
        }

        $this->dumpVar('haveSolution', $rs);

        return (!empty ($rs));
    }

    /**
     * Get the full list of records corresponding to the given WHERE clause.
     * If `$where` is empty, returns the full list of all form assignments.
     */
    function _getFullList($where = '')
    {
        $rs = DatabaseBean::dbQuery(
            "SELECT * FROM formsolutions " . $where . " ORDER BY subtask_id,assignmnt_id,part");

        return $rs;
    }

    /**
     * Fetch a complete list of assigments for a single subtask.
     */
    function getFullSubtaskList($subtaskList)
    {
        $dbList = arrayToDBString($subtaskList);
        $rs = $this->_getFullList(" WHERE subtask_id IN (" . $dbList . ")");
        return $rs;
    }

    /**
     * Fetch solution data for the given assignment id and given student.
     * @TODO Add schoolyear as well!
     */
    function getSolutionData($studentId, $assignmentId)
    {
        return $this->_getFullList(
            'WHERE assignmnt_id=' . $assignmentId . ' ' .
            'AND subtask_id=' . $this->id . ' ' .
            'AND student_id=' . $studentId
        );
    }

    function updateSubtaskList($subtaskList)
    {
        $rs = $this->dbQuery(
            'SELECT subtask_id,part,COUNT(*) AS mc FROM formsolutions ' .
            'GROUP BY subtask_id,part');
        if (!empty ($rs))
        {
            $num = array();
            foreach ($rs as $val)
            {
                $num[$val['subtask_id']] = $val['mc'];
            }
            foreach ($subtaskList as $key => $val)
            {
                $myCount = $num[$val['id']];
                if (empty ($myCount)) $myCount = 0;
                $subtaskList[$key]['count'] = $myCount;
            }
        }

        return $subtaskList;
    }

    function assignFull()
    {
        $rs = $this->_getFullList();
        $this->_smarty->assign('formsolutionList', $rs);
        return $rs;
    }

    function assignSingle()
    {
    }

    /**
     * Assign string representation of a pole value in case it exists.
     * TODO: Move this code to Smarty plugin function.rootfactor.php and let Smarty do the formatting.
     */
    function assignStringRep($numeric_strings, $smarty_var, $suffix='')
    {
        $str_array = array();
        foreach ($numeric_strings as $key => $num_str)
        {
            if (empty($num_str))
            {
                $str = '';
            }
            else
            {
                /* Again, this is designed with pole representation in mind. Pole values have the opposite values
                   than those that are written in the denominator of the transfer function. Hence the minus sign. */
                $float_num = -floatval($num_str);
                $int_num = intval($float_num);
                if ($float_num == $int_num)
                {
                    /* Special case is +/- 1 in case that there is a non-empty suffix. */
                    if (abs($int_num) == 1 && !empty($suffix))
                    {
                        $format_string = '%s';
                        $num = ($int_num > 0) ? '+' : '-';
                    }
                    else
                    {
                        $format_string = '%+d';
                        $num = $int_num;
                    }
                }
                else
                {
                    $format_string = '%+f';
                    $num = $float_num;
                }
                $str = sprintf($format_string, $num) . $suffix;
            }
            $str_array[$key] = $str;
        }
        $this->assign($smarty_var, $str_array);
    }

    function assignStringRepS($stability_strings, $smarty_var)
    {
        $str_array = array();
        foreach ($stability_strings as $key => $num_str)
        {
            /* The contents of $num_str may be numeric, but it is not guaranteed.
               The number has to be >=0. */
            $str = '???';
            if (ctype_digit($num_str))
            {
                /* No we may be sure that `intval()` will return something meaningful. */
                $num = intval($num_str);
                if (array_key_exists($num, self::$stabilityMap))
                {
                    //self::dumpVar('num',$num);
                    $str = self::$stabilityMap[$num];
                }
            }
            $str_array[$key] = $str;
        }
        $this->assign($smarty_var, $str_array);
    }

    /**
     * Save a solution represented by a PDF file.
     * @param $subtaskBean  SubtaskBean Initialised to subtask of the solution.
     * @param $fieldName    string Name of the form field that contains the file.
     * @param $assignmentId int Number of the assignment, if any.
     * @param $studentBean  StudentBean Information about the student.
     */
    function saveSolutionPDF($subtaskBean, $fieldName, $assignmentId, $studentBean)
    {
        /* Construct a file bean that implements also all operations on
           assigment files. */
        $fileBean = new FileBean (NULL, $this->_smarty, NULL, NULL);

        /* Subtask id and code */
        $suId = $subtaskBean->id;
        $suCode = $subtaskBean->ttitle;

        /* Get the student id, login and full name. */
        $stId = $studentBean->id;
        $stLogin = $studentBean->login;
        $stFullName = UserBean::getFullName($studentBean->rs);

        $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension

        /* Loop over all uploaded files for this subtask. */
        foreach ($_FILES[$fieldName]['name'][$suId] as $part => $val)
        {
            /* Temporary file name of the stored file. */
            $fn = $_FILES[$fieldName]['tmp_name'][$suId][$part];
            /* Original file name is actually $val. */
            $nn = $val;
            /* Check for MIME type */
            $file_mime = finfo_file($finfo, $nn);
            if ($file_mime != 'application/pdf')
            {
                $this->dumpVar('file_mime', $file_mime);
                $this->action = 'e_no_pdf';
                break;
            }
            if (is_uploaded_file($fn))
            {
                /* Upload ok, copy it. */
                $solPath = 'solutions/' . strtolower($suCode) . '/' . $this->schoolyear . '/';
                $solName = $stLogin . '_' . $suId . $part . '.pdf';
                $solFile = $solPath . $solName;

                /* Make the directory, if necessary. */
                @ mkdir(CMSFILES . '/' . $solPath, 0777, TRUE);
                copy($fn, CMSFILES . '/' . $solFile);

                self::dumpVar('copy src', $fn);
                self::dumpVar('copy dst', CMSFILES . '/' . $solFile);

                if ($subtaskBean->type == TT_LECTURE_PDF)
                {
                    $fileDesc = "Řešení hromadné úlohy " .
                        $suCode . ", student " . $stFullName;
                    $assignmentId = 0;
                }
                else
                {
                    $fileDesc = "Řešení úlohy " . $suCode . ", " .
                        "příklad " . $assignmentId . "(" . $part . "), " .
                        "student " . $stFullName;
                }
                /* Store information about the generated file in file table. */
                $fileId = $fileBean->addFile(FT_X_SOLUTION,
                                             $suId, $stId, $solFile,
                                             $nn, $fileDesc);

                /* Update the solution database with this solution.
                 * Subtask id and part number and assignment id. */
                $this->a = 0;
                $this->b = 0;
                $this->c = 0;
                $this->d = 0;
                $this->e = 0;
                $this->f = 0;
                $this->subtask_id = $suId;
                $this->assignmnt_id = $assignmentId;
                $this->part = $part;
                $this->student_id = $stId;
                /* And store the data. */
                $this->dbReplace();
            }
            else
            {
                $this->action = 'err03';
                /* Break the loop. */
                break;
            }
        }
    }

    /**
     * @param $stb SubtaskBean
     * @param %students Array
     */
    function sendConfirmationEmail($stb, $students)
    {
        /* Send an e-mail to the user saying that the password has been changed. */

        $header = "From: " . SENDER_FULL . "\r\n";
        // $header  .= "To: <" . $this->email . ">\r\n";
        $header .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
        $header .= "Errors-To: " . ADMIN_FULL . "\r\n";
        $header .= "Reply-To: " . ADMIN_FULL . "\r\n";
        $header .= "X-Mailer: PHP";

        $userLogin = SessionDataBean::getUserLogin();
        $userName = SessionDataBean::getUserFullName();

        $message = "Uživatel '" . $userLogin . "' (" . $userName . ") nahrál za vaši skupinu řešení úlohy\r\n";
        $message .= $stb->ttitle . " (" . $stb->title . ")\r\n";
        $message .= "\r\n";
        $message .= "Řešení tímto považujeme za odevzdané.\r\n";
        $message .= "\r\n";

        $lecture_data = SessionDataBean::getLecture();
        $message_title = '[' . $lecture_data['code'] . '] Student `' . $userLogin . '` odevzdal úlohu ' . $stb->ttitle;
        $subject = "=?utf-8?B?" . base64_encode($message_title) . "?=";

        /* Now send the notification to the student ... */
        if (SEND_MAIL)
        {
            foreach ($students as $val)
            {
                mail($val['email'], $subject, $message, $header);
            }
        }

        /* ... and send a copy to the administrator. */
        mail(ADMIN_EMAIL, $subject, $message, $header);
    }

    /**
     * Save a set of subtask solutions.
     * This function allows for saving a set of files containing solved
     * problems submitted by a student.
     */
    function saveSolutionSet()
    {
        /* Get data of the student. */
        $studentBean = new StudentBean ($this->id, $this->_smarty, NULL, NULL);
        $studentBean->assignSingle();
        $this->dumpVar('studentBean', $studentBean);

        /* Create an instances of SubtaskBean and AssignmentBean. They will be
           used in the loop below to query particular subtask and assignment
           data. */
        $subtaskBean = new SubtaskBean (NULL, $this->_smarty, NULL, NULL);
        $assignmentBean = new AssignmentsBean (NULL, $this->_smarty, NULL, NULL);

        /* Check that the $_FILES contain something that resembles correct
           submission. */
        if (!array_key_exists('solutions', $_FILES))
        {
            throw new Exception('No applicable solution data found.');
        }

        /* Loop over the contents of $_FILES and look what has been actually
           submitted. Some of the form fields may be empty. */
        $this->dumpVar('_FILES', $_FILES);

        /* Initialise the result container */
        $res = array();

        foreach ($_FILES['solutions']['error'] as $subtaskId => $parts)
        {
            /* This is a possibly uploaded file. The value of $subtaskId
               contains the subtask that the file is related to. We have
               to aslo find out the appropriate $assignemntId. */
            $subtaskBean->assignSingle($subtaskId);
            $assignmentId = $assignmentBean->getAssignmentId($this->id, $subtaskId, $subtaskBean->type);
            self::dumpVar('subtask', $subtaskBean);
            self::dumpVar('assignment id', $assignmentId);

            /* Initialise the result container holding information about
               individual parts of the submitted solution. */
            $pres = array();

            foreach ($parts as $partId => $status)
            {
                if ($status == UPLOAD_ERR_OK)
                {
                    /* Save the submitted file or files. */
                    switch ($subtaskBean->type)
                    {
                        case TT_LECTURE_PDF:
                        case TT_WEEKLY_PDF:
                            $this->saveSolutionPDF($subtaskBean, 'solutions', $assignmentId, $studentBean);
                            break;
                        default:
                            throw new Exception ('Currently can save only PDF solutions!');
                    }
                }
                else
                {
                    /* Some error happened (or no file has been submitted, which
                          may be perfecly okay. */
                }
                $pres[] = array('part' => $partId, 'status' => $status);
            }
            $res[] = array('subtask' => $subtaskBean->rs,
                'assignmentId' => $assignmentId,
                'parts' => $pres);
        }
        $this->assign('saveSet', $res);
    }

    /**
     * Process a subtask submission of a student.
     */
    function saveSingleSolutionAsStudent()
    {
        /* Get data of this subtask. */
        $subtaskBean = new SubtaskBean ($this->id, $this->_smarty, "", "");
        $subtaskBean->assignSingle();
        self::dumpVar('subtaskBean', $subtaskBean);

        /* Remember student id. */
        $studentId = SessionDataBean::getUserId();
        /* Construct assignments bean and fetch data of the assignment. */
        $assignmentsBean = new AssignmentsBean ($this->id, $this->_smarty, "", "");
        /* This will query the assignment for the currently logged in student. */
        $assignmentId = $assignmentsBean->getAssignmentId($studentId, $this->id, $subtaskBean->type);

        /* Construct the deadline extenion bean and prepare data for
           querying the extension status. */
        $deadextBean = new DeadlineExtensionBean ($this->id, $this->_smarty, NULL, NULL);

        /* Determine the number of parts this assignment has got. */
        $faBean = new FormAssignmentBean ($this->id, $this->_smarty, "", "");
        /* Update the internal data of this object from POST variables. */
        $this->processPostVars();

        $ca = array();
        $cb = array();
        $cc = array();
        $cd = array();
        $ce = array();
        $cf = array();

        /* Get student group */
        if (SessionDataBean::getLectureGroupFlag())
        {
            $sgb = new StudentGroupBean(null, $this->_smarty, null, null);
            $students = $sgb->getGroupStudentsOfStudent($studentId);
        }
        else
        {
            $students = StudentGroupBean::getDefaultGroupStudents($studentId);
        }

        /* Check that the assignment has not been submitted already by this student. */
        if ($this->haveSolution($this->id, $students, $assignmentId))
        {
            /* Refuse to overwrite existing assignment submission. */
            $this->action = "err01";
        }
        /* Check that the assignment still can be submitted. */
        else if (!$subtaskBean->active && !$deadextBean->isActive())
        {
            /* Refuse to submit a subtask that is not active. */
            $this->action = "err02";
        } /* Store the assignment. */
        else
        {
            switch ($subtaskBean->type)
            {
                case TT_WEEKLY_TF:
                    /* Form assignment.
                       This is the total number of matches. */
                    $match = 0;
                    foreach ($this->aa as $key => $val)
                    {
                        /* This is the response of a student. Elements 'a', 'c',
                           and 'e' contain real part of a complex number, or a
                           real number. In cases where students forget to fill
                           in zeros, we will automatically add them. The same
                           will happen with imaginary parts - real numbers have
                           the imaginary part zero. */
                        $this->a = returnDefault($this->aa[$key], 0);
                        $this->b = returnDefault($this->bb[$key], 0);
                        $this->c = returnDefault($this->cc[$key], 0);
                        $this->d = returnDefault($this->dd[$key], 0);
                        $this->e = returnDefault($this->ee[$key], 0);
                        $this->f = returnDefault($this->ff[$key], 0);
                        $this->g = returnDefault($this->gg[$key], NULL);
                        $this->h = NULL;

                        if ($this->a !== NULL) $this->a = 100000 * $this->a;
                        if ($this->b !== NULL) $this->b = 100000 * $this->b;
                        if ($this->c !== NULL) $this->c = 100000 * $this->c;
                        if ($this->d !== NULL) $this->d = 100000 * $this->d;
                        if ($this->e !== NULL) $this->e = 100000 * $this->e;
                        if ($this->f !== NULL) $this->f = 100000 * $this->f;

                        $a = $this->a;
                        $b = $this->b;
                        $c = $this->c;
                        $d = $this->d;
                        $e = $this->e;
                        $f = $this->f;
                        $g = $this->g;

                        $this->dumpThis();

                        /* Subtask id and part number and assignment id. */
                        $this->subtask_id = $this->id;
                        $this->assignmnt_id = $assignmentId;
                        $this->part = $key;
                        $this->student_id = SessionDataBean::getUserId();
                        /* And store the data. */
                        $this->dbReplace();

                        /* Evaluate the answer.
                           The value of `$match` will be from 0 to 7. */
                        $match += $faBean->matchSolution(
                            $assignmentId, $this->part, TT_WEEKLY_TF,
                            $a, $b, $c, $d, $e, $f, $g);
                        $this->dumpVar('match', $match);
                    }

                    /* Match percentage from 0.0 to 1.0. */
                    $fmatch = floatval($match) / (count($this->aa) * 7.0);
                    //$this->dumpVar ( 'fmatch', $fmatch );

                    /* Compute the number of points corresponding to this
                       match percentage. Do not round, the number of points
                       has one decimal place, it will be rounded by SQL when
                       written to the database. */
                    $this->dumpVar('stb', $subtaskBean);
                    $points = $fmatch * $subtaskBean->maxpts;
                    $this->dumpVar('points', $points);

                    /* And store the points. The `dbReplace()` funciton
                       expects to find an array of subtask results indexed by
                       student ids in `points` variable. We will have to
                       create it for this single student. */
                    $pointsBean = new PointsBean (0, $this->_smarty, "", "");
                    /* TODO: Implement this as a method of PointsBean. */
                    $pointsBean->points = array(
                        SessionDataBean::getUserId() => array($this->id => $points));
                    $pointsBean->setSchoolYear(SessionDataBean::getSchoolYear());
                    $pointsBean->dbReplace();
                    $this->object = 'formsolution.tf';
                    break;

                case TT_WEEKLY_SIMU:
                    /* Construct the file bean that implements also all operations on 
                       assigment files. */
                    $fileBean = new FileBean(0, $this->_smarty, "", "");

                    /* Student name is stored in the session. */
                    $u8name = SessionDataBean::getUserFullName();

                    /* Subtask code */
                    $sCode = $subtaskBean->ttitle;

                    /* Check the uploaded file. */
                    $this->dumpVar('Files', $_FILES);

                    $fn = $_FILES['mdl']['tmp_name']['a'];
                    $nn = $_FILES['mdl']['name']['a'];
                    if (is_uploaded_file($fn))
                    {
                        /* Upload ok, copy it. */
                        $solPath = 'solutions/' . strtolower($sCode) . '/' . $this->schoolyear . '/';
                        $solName = SessionDataBean::getUserLogin() . '_' . $this->id . '.mdl';
                        $solFile = $solPath . $solName;

                        /* Make the directory. */
                        @ mkdir(CMSFILES . '/' . $solPath, 0777, TRUE);
                        copy($fn, CMSFILES . '/' . $solFile);

                        /* Store information about the generated file in file table. */
                        $fileId = $fileBean->addFile(
                            FT_X_SOLUTION, $this->id, $studentId, $solFile, $nn,
                            "Řešení úloha " . $sCode . ", příklad " . $assignmentId . ", student " . $u8name);

                        /* Update the solution database with this solution.
                         * Subtask id and part number and assignment id. */
                        $this->a = 0;
                        $this->b = 0;
                        $this->c = 0;
                        $this->d = 0;
                        $this->e = 0;
                        $this->f = 0;
                        $this->subtask_id = $this->id;
                        $this->assignmnt_id = $assignmentId;
                        $this->part = 'a';
                        $this->student_id = SessionDataBean::getUserId();
                        /* And store the data. */
                        $this->dbReplace();

                        /* Slightly different output. */
                        $this->object = 'formsolution.simu';
                    }
                    else
                    {
                        $this->action = 'err03';
                    }
                    break;

                case TT_WEEKLY_PDF:
                case TT_LECTURE_PDF:
                case TT_SEMESTRAL_IND:
                    /* Construct the file bean that implements also all operations on 
                       assigment files. */
                    $fileBean = new FileBean(null, $this->_smarty, null, null);

                    /* Student name and login is stored in the session. */
                    $u8name = SessionDataBean::getUserFullName();
                    $login = SessionDataBean::getUserLogin();

                    /* Subtask code */
                    $sCode = $subtaskBean->ttitle;

                    /* Check the uploaded file or files. */
                    $this->dumpVar('_FILES', $_FILES);

                    /* In theory, it could happen that the $_FILES variable does not contain all files that shall be
                       uploaded. The most severe error is the case when $_FILES is empty even if a file has been
                       uploaded. */
                    if (!isset($_FILES) || empty($_FILES) || empty($_FILES['pdf']['name']))
                    {
                        $this->action = 'err03';
                    }
                    else
                    {
                        /* Loop over all uploaded files. */
                        foreach ($_FILES['pdf']['name'] as $key => $val)
                        {
                            /* Temporary file name of the stored file. */
                            $fn = $_FILES['pdf']['tmp_name'][$key];
                            /* Original file name is actually $val. */
                            $nn = $val;
                            if (is_uploaded_file($fn))
                            {
                                /* Verify the format using `pdftk`.
                                   TODO: This will not work for multiple parts of
                                   a solution. */
                                exec("pdftk $fn cat output /dev/null 2>&1", $output, $ret);
                                if ($ret)
                                {
                                    /* The command did not succeed. */
                                    $this->action = 'e_nopdf';
                                    $this->assign('errormsg', $output[0]);
                                    return;
                                }
                                /* Upload ok, copy it. */
                                $solPath = 'solutions/' . strtolower($sCode) . '/' . $this->schoolyear . '/';
                                $solName = $login . '_' . $this->id . $key . '.pdf';
                                $solFile = $solPath . $solName;

                                /* Make the directory. */
                                @ mkdir(CMSFILES . '/' . $solPath, 0777, TRUE);
                                copy($fn, CMSFILES . '/' . $solFile);

                                if ($subtaskBean->type == TT_LECTURE_PDF)
                                {
                                    $fileDesc = "Řešení hromadné úlohy " .
                                        $sCode . ", student " . $u8name;
                                    $assignmentId = 0;
                                }
                                else
                                {
                                    $fileDesc = "Řešení úlohy " . $sCode . ", " .
                                        "příklad " . $assignmentId . "(" . $key . "), " .
                                        "student " . $u8name;
                                }
                                /* Store information about the generated file in file table. */
                                $fileId = $fileBean->addFile(
                                    FT_X_SOLUTION, $this->id, $studentId, $solFile,
                                    $nn, $fileDesc);

                                /* Update the solution database with this solution.
                                 * Subtask id and part number and assignment id. */
                                $this->a = 0;
                                $this->b = 0;
                                $this->c = 0;
                                $this->d = 0;
                                $this->e = 0;
                                $this->f = 0;
                                $this->subtask_id = $this->id;
                                $this->assignmnt_id = $assignmentId;
                                $this->part = $key;
                                $this->student_id = SessionDataBean::getUserId();
                                /* And store the data. */
                                $this->dbReplace();

                                if ($subtaskBean->type == TT_SEMESTRAL_IND && SessionDataBean::getLectureGroupFlag())
                                {
                                    /* Send a confirmation e-mail to the whole group */
                                    $this->sendConfirmationEmail($subtaskBean, $students);
                                }

                                /* Slightly different output. */
                                $this->object = 'formsolution.pdf';
                            }
                            else
                            {
                                $this->action = 'err03';
                                /* Break the loop. */
                                break;
                            }
                        }
                    }
                    break;

                case TT_WEEKLY_ZIP:
                case TT_SEMESTRAL:
                    /* Construct the file bean that implements also all operations on 
                       assigment files. */
                    $fileBean = new FileBean(0, $this->_smarty, "", "");

                    /* Student name and login is stored in the session. */
                    $u8name = SessionDataBean::getUserFullName();
                    $login = SessionDataBean::getUserLogin();

                    /* Subtask code */
                    $sCode = $subtaskBean->ttitle;

                    /* Check the uploaded file or files. */
                    $this->dumpVar('Files', $_FILES);

                    /* In theory, it could happen that the $_FILES variable does not contain all files that shall be
                       uploaded. The most severe error is the case when $_FILES is empty even if a file has been
                       uploaded. */
                    if (!isset($_FILES) || empty($_FILES) || empty($_FILES['zip']['name']))
                    {
                        $this->action = 'err03';
                    }
                    else
                    {
                        /* Loop over all uploaded files. */
                        foreach ($_FILES['zip']['name'] as $key => $val)
                        {
                            /* Temporary file name of the stored file. */
                            $fn = $_FILES['zip']['tmp_name'][$key];
                            /* Original file name is actually $val. */
                            $nn = $val;
                            if (is_uploaded_file($fn))
                            {
                                /* Upload ok, copy it. */
                                $solPath = 'solutions/' . strtolower($sCode) . '/' . $this->schoolyear . '/';
                                $solName = $login . '_' . $this->id . $key . '.zip';
                                $solFile = $solPath . $solName;

                                /* Make the directory. */
                                @ mkdir(CMSFILES . '/' . $solPath, 0777, TRUE);
                                copy($fn, CMSFILES . '/' . $solFile);

                                /* Difference between semestral and weekly tasks is
                                   in the absence of assignmentId for the former. */
                                if ($subtaskBean->type == TT_SEMESTRAL)
                                {
                                    $fileDesc = "Řešení semestrální úlohy " .
                                        $sCode . ", student " . $u8name;
                                    $assignmentId = 0;
                                }
                                else
                                {
                                    $fileDesc = "Řešení úlohy " . $sCode . ", " .
                                        "příklad " . $assignmentId . "(" . $key . "), " .
                                        "student " . $u8name;
                                }
                                /* Store information about the generated file in
                                   file table. */
                                $fileId = $fileBean->addFile(
                                    FT_X_SOLUTION, $this->id, $studentId, $solFile,
                                    $nn, $fileDesc
                                );

                                /* Update the solution database with this solution.
                                 * Subtask id and part number and assignment id. */
                                $this->a = 0;
                                $this->b = 0;
                                $this->c = 0;
                                $this->d = 0;
                                $this->e = 0;
                                $this->f = 0;
                                $this->subtask_id = $this->id;
                                $this->assignmnt_id = $assignmentId;
                                $this->part = $key;
                                $this->student_id = SessionDataBean::getUserId();
                                /* And store the data. */
                                $this->dbReplace();

                                /* Slightly different output. */
                                $this->object = 'formsolution.pdf';
                            }
                            else
                            {
                                $this->action = 'err03';
                                /* Break the loop. */
                                break;
                            }
                        }
                    }
                    break;

                case TT_WEEKLY_FORM:
                    /* Form assignment.
                       This is the total number of matches. */
                    $match = 0;
                    foreach ($this->aa as $key => $val)
                    {
                        /* This is the response of a student. */
                        $this->a = returnDefault($this->aa[$key]);
                        $this->b = returnDefault($this->bb[$key]);
                        $this->c = returnDefault($this->cc[$key]);
                        $this->d = returnDefault($this->dd[$key]);
                        $this->e = returnDefault($this->ee[$key]);
                        $this->f = returnDefault($this->ff[$key]);

                        /* Save the data only in case that the student explicitly
                           confirmed that this is the correct submission. */
                        if ($this->confirmed)
                        {
                            /* Save information about this submission - set subtask
                                  id and part number and assignment id. */
                            $this->subtask_id = $this->id;
                            $this->assignmnt_id = $assignmentId;
                            $this->part = $key;
                            $this->student_id = SessionDataBean::getUserId();
                            /* And store the data. */
                            $this->dbReplace();

                            /* Evaluate the answer.
                               The value of `$match` will be from 0 to 6. */
                            $match += $faBean->matchSolution(
                                $assignmentId, $this->part, TT_WEEKLY_FORM,
                                $this->a, $this->b, $this->c, $this->d, $this->e, $this->f);

                            self::dumpVar('key', $key);
                            self::dumpVar('match', $match);
                        }
                        else
                        {
                            $ca[] = $this->a;
                            $cb[] = $this->b;
                            $cc[] = $this->c;
                            $cd[] = $this->d;
                            $ce[] = $this->e;
                            $cf[] = $this->f;
                        }
                    }

                    if ($this->confirmed)
                    {
                        /* Match percentage from 0.0 to 1.0. */
                        $fmatch = floatval($match) / (count($this->aa) * 6.0);
                        self::dumpVar('fmatch', $fmatch);

                        /* Compute the number of points corresponding to this match
                         percentage. Do not round, the number of points has one decimal
                         place, it will be rounded by SQL when written to database. */
                        self::dumpVar('stb', $subtaskBean);
                        $points = $fmatch * $subtaskBean->maxpts;
                        self::dumpVar('points', $points);

                        /* And store the points. The `dbReplace()` funciton expects to find an
                         array of subtask results indexed by student ids in `points` variable.
                         We will have to create it for this single student. */
                        $pointsBean = new PointsBean (0, $this->_smarty, "", "");
                        /* TODO: Implement this as a method of PointsBean. */
                        $pointsBean->points = array(
                            SessionDataBean::getUserId() => array($this->id => $points));
                        /* TODO: Does PointsBean inherit `schoolyear`? I think so. */
                        $pointsBean->setSchoolYear($this->schoolyear);
                        $pointsBean->dbReplace();
                    }
                    break;

                default:
                    throw Exception('Neznámý typ odevzdávané úlohy.');
            }
        }

        if (!$this->confirmed)
        {
            /* Get a list of subtask types. */
            $subtaskBean = new SubtaskBean ($this->id, $this->_smarty, "", "");
            $subtaskBean->assignSingle();
            //$this->dumpVar ( 'subtaskBean 1', $subtaskBean );
            /* Construct assignments bean and fetch data of the assignment. */
            $assignmentsBean = new AssignmentsBean ($this->id, $this->_smarty, "", "");
            /* This will query the assignment for the currently logged in student. */
            $assignment = $assignmentsBean->assignSingle();
            /* Determine the number of parts this assignment has got. */
            $faBean = new FormAssignmentBean (0, $this->_smarty, "", "");
            $partList = $faBean->assignParts($this->id);

            $this->assign('aa', $ca);
            $this->assign('bb', $cb);
            $this->assign('cc', $cc);
            $this->assign('dd', $cd);
            $this->assign('ee', $ce);
            $this->assign('ff', $cf);
        }
    }

    /* -------------------------------------------------------------------
       HANDLER: SHOW
       ------------------------------------------------------------------- */
    function doShow()
    {
        /* Generate new task set. First get the list of students. */
        /* Get the list of all excersises, assign it to the Smarty variable
           'excersiseList' and return it to us as well, we will need it later.
           $this->id will point to the lecture_id in this case. */
        $excersiseBean = new ExcersiseBean (0, $this->_smarty, "x", "x");
        $excersiseList = $excersiseBean->assignFull(1);

        /* Get the lecture description, just to fill in some more-or-less
           useful peieces of information. */
        $lectureBean = SessionDataBean::getLecture();
        $lectureBean->assignSingle();

        /* Now create an array that contains student id as an key and _index_ to
           the $excersiseList as a value (that is, not the excersise ID, but the
           true index into the array. */
        $studexcBean = new StudentExcersiseBean (0, $this->_smarty, "x", "x");
        $exerciseBinding = $studexcBean->getExcersiseBinding($excersiseList);
        $this->dumpVar('exerciseBinding', $exerciseBinding);

        /* Get the list of all students. Additionally, create a field 'checked'
           that contains text ' checked="checked"' on the position of the exercise
           that the particular student visits, and '' otherwise. */
        $studentBean = new StudentBean (0, $this->_smarty, "x", "x");
        $studentList = $studentBean->assignStudentListWithExcersises($lectureBean->id, count($excersiseList), $exerciseBinding);

        $this->generateAssignments($this->id, $studentList);
    }

    /* -------------------------------------------------------------------
       HANDLER: ADMIN
       ------------------------------------------------------------------- */
    function doAdmin()
    {
        /* Id we got is the id of the student that submitted the solutions
           by e-mail or brought them personally. We want to upload the
           solutions for him/her.*/
        $studentBean = new StudentBean ($this->id, $this->_smarty, NULL, NULL);
        $studentBean->assignSingle();

        /* List all subtasks for the current lecture that are being evaluated
           manually. */
        $solutionBean = new SolutionBean (NULL, $this->_smarty, NULL, NULL);
        $solutionBean->assignAdminList();
    }

    /* -------------------------------------------------------------------
       HANDLER: EDIT
       Let user input solution to the given subtask.
       ------------------------------------------------------------------- */
    function doEdit()
    {
        /* Get a lecture that this subtask is related to. */
        $lectureBean = new LectureBean (1, $this->_smarty, "", "");
        $lectureBean->assignSingle();
        /* Get a list of subtask types. */
        $subtaskBean = new SubtaskBean ($this->id, $this->_smarty, "", "");
        $subtaskBean->assignSingle();
        //$this->dumpVar ( 'subtaskBean 1', $subtaskBean );
        /* Construct assignments bean and fetch data of the assignment. */
        $assignmentsBean = new AssignmentsBean ($this->id, $this->_smarty, "", "");
        /* This will query the assignment for the currently logged in student. */
        $assignmentsBean->assignSingle();
        /* Determine the number of parts this assignment has got. */
        $faBean = new FormAssignmentBean (0, $this->_smarty, "", "");
        $faBean->assignParts($this->id);

        /* This has been moved to SubtaskBean::assignSingle() */
        //if ($subtaskBean->type == TT_WEEKLY_TF)
        //{
        //    /* Transfer function task needs 'p' and 'z' */
        //    $varList = array(0 => 'p', 1 => 'z', 'a' => 'p', 'b' => 'z');
        //    $this->assign('varList', $varList);
        //}
        //$this->dumpVar ( 'subtaskBean 2', $subtaskBean );

        /* Determine the proper template (depends on task type). Use default
           template for subtasks that do not have an entry in template map. */
        if (array_key_exists($subtaskBean->type, self::$templateMap))
            $this->object = self::$templateMap [$subtaskBean->type];
    }

    /**
     * Handle the VERIFY event.
     */
    function doVerify()
    {
        /* Get the subtask. */
        $subtaskBean = new SubtaskBean ($this->id, $this->_smarty, NULL, NULL);
        $subtaskBean->assignSingle();
        /* Construct the assignment class instance and fetch data of the assignment for the currently logged-in
           student. */
        $assignmentsBean = new AssignmentsBean ($this->id, $this->_smarty, NULL, NULL);
        $assignmentsBean->assignSingle();
        /* Determine the number of parts this assignment has got. */
        $faBean = new FormAssignmentBean (0, $this->_smarty, NULL, NULL);
        $faBean->assignParts($this->id);
        /* Determine the proper template (depends on task type). Use default
           template for subtasks that do not have an entry in template map. */
        if (array_key_exists($subtaskBean->type, self::$templateMap))
            $this->object = self::$templateMap [$subtaskBean->type];
        /* Update the internal data of this object from POST variables. */
        $this->processPostVars();
        /* TODO: This shall go to SubtaskBean as the class knows the task type and can act accordingly. */
        $this->assignStringRep($this->aa,'a');
        $this->assignStringRep($this->bb,'b', 'i');
        $this->assignStringRep($this->cc,'c');
        $this->assignStringRep($this->dd,'d', 'i');
        $this->assignStringRep($this->ee,'e');
        $this->assignStringRep($this->ff,'f', 'i');
        $this->assignStringRepS($this->gg,'g');
        /* Replicate the contents of the POST array. The data shall at the end arrive via POST request to
           the `doSave` method, hence the page we are going to display has to contain all input data
           provided by user on the edit page ... */
        $this->assign('sol_data', $_POST);

    }

    /**
     * Handle the SAVE event.
     * This handler is called in two different configurations: (1) when some
     * student saves her or his solution of an enxcersise, or (2) when some
     * lecturer or administrator saves a subset of solutions on behalf of some
     * student.
     */
    function doSave()
    {
        /* Check user role. */
        if (UserBean::isRoleAtLeast(SessionDataBean::getUserRole(), USR_LECTURER))
        {
            $this->saveSolutionSet();
            /* Change the template. Do not change the action, as the contents
               of the left column may be action dependent. */
            $this->object .= ".set";
        }
        else
        {
            /* This is the origininal code, it will use its original
               template. */
            $this->saveSingleSolutionAsStudent();
        }
        $this->assign('confirmed', $this->confirmed);
    }

    /* -------------------------------------------------------------------
       HANDLER: DELETE
       ------------------------------------------------------------------- */
    function doDelete()
    {
        $this->assignSingle();

        /* Get a lecture that this subtask is related to. */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, "", "");
        $lectureBean->assignSingle();
    }

    /* -------------------------------------------------------------------
       HANDLER: REAL DELETE
       ------------------------------------------------------------------- */
    function doRealDelete()
    {
        $this->assignSingle();
        /* Delete the record */
        DatabaseBean::dbDeleteById();

        /* Get a lecture that this subtask is related to. */
        $lectureBean = new LectureBean ($this->id, $this->_smarty, "", "");
        $lectureBean->assignSingle();
    }
}

?>
