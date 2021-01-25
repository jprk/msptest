<?php

class ImportBean extends DatabaseBean
{
    const FORMAT_WEBKOS_2020 = 6;
    const FORMAT_WEBKOS_2017 = 5;
    const FORMAT_IKOS_ROZ = 4;
    const FORMAT_IKOS = 3;
    const FORMAT_WEBKOS_2015 = 2;
    const FORMAT_TERMINAL = 1;

    private $firstname;
    private $surname;
    private $yearno;
    private $groupno;
    private $email;
    private $hash;
    private $login;
    private $cvutid;
    private $groups;

    function _setDefaults()
    {
        $this->firstname = array();
        $this->surname = array();
        $this->yearno = array();
        $this->groupno = array();
        $this->email = array();
        $this->hash = array();
        $this->login = array();
        $this->cvutid = array();
        $this->groups = array();
    }

    /* Constructor */
    function __construct($id, &$smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, NULL, $action, $object);
        /* Initialise new properties to their default values. */
        $this->_setDefaults();
    }

    function processPostVars()
    {
        $this->firstname = $_POST['firstname'];
        $this->surname = $_POST['surname'];
        $this->hash = $_POST['hash'];
        $this->yearno = $_POST['yearno'];
        $this->groupno = $_POST['groupno'];
        $this->login = $_POST['login'];
        $this->email = $_POST['email'];
        $this->cvutid = $_POST['cvutid'];
        $this->groups = $_POST['groups'];
    }

    function dbReplace()
    {

    }

    /* -------------------------------------------------------------------
       HANDLER: ADMIN
       ------------------------------------------------------------------- */
    function doAdmin()
    {
        /* Get the information about evaluation. We have to initialise
           points to PTS_NOT_CLASSIFIED. Abort if there is no evaluation. */
        $evaluationBean = new EvaluationBean (0, $this->_smarty, NULL, NULL);
        /* This will initialise EvaluationBean with evaluation scheme for
           the current lecture (the id of the lecture is stored in the
           session). The function returns 'true' if the evaluation scheme
           has been found and the object has been initialised. */
        $ret = $evaluationBean->initialiseFor(SessionDataBean::getLectureId(), $this->schoolyear);

        /* Check the initialisation status. */
        if (!$ret)
        {
            /* No evaluation for this school year, abort. */
            $this->action = 'e_init';
            return ERR_ADMIN_MODE;
        }
        /* If a valid evaluation scheme exists, this action only displays a
           static page template. */

        return RET_OK;
    }

    /**
     * Edit handler.
     * @throws Exception
     */
    function doEdit()
    {
        /* Check the version of the imported data.
           'v1' is the old textfile from terminal version of KOS,
           'v2' is the newer format provided by WebKOS that unfortunately
                lacks student e-mails and logins. */
        $format = (int)$_POST['format'];

        /* Check if the automatic study year increment has been requested. */
        $addyear = (isset ($_POST['addyear']));

        /* Open an LDAP connection. */
        $ldap = new LDAPConnection ($this->_smarty);

        /* The LDAP server does not provide anonymous binds anymore. We have to log in using a proxy user and
           passwords that is stored in configuration files of the application. */
        $proxy_cn = $this->_smarty->getConfig('ldap_proxy_cn');
        $proxy_pw = $this->_smarty->getConfig('ldap_proxy_pw');
        if (! $ldap->bind($proxy_cn, $proxy_pw))
        {
            throw new Exception("Cannot bind to LDAP server as `$proxy_cn`!");
        }

        /* Initialise the list of students that will be imported. */
        $studentList = array();

        /* Remember the file information and publish it. */
        $kosfile = $_FILES['kosfile'];
        $this->assign('file', $kosfile);

        /* Initial value of $errStr is empty string. */
        $errStr = '';

        if (is_uploaded_file($kosfile['tmp_name']))
        {
            $handle = @fopen($kosfile['tmp_name'], "r");
            if ($handle)
            {
                /* In case of FORMAT_WEBKOS_2015 or FORMAT_IKOS we have to skip the first line (or two) of
                   the imported CSV file - it contains header information. */
                $skipHeader = ($format == self::FORMAT_WEBKOS_2015) || ($format == self::FORMAT_IKOS) || ($format == self::FORMAT_IKOS_ROZ);
                /* First row of the resulting list. */
                $row = 0;
                /* And loop while we have something to chew on ... */
                while (!feof($handle))
                {
                    /* Read a line of text from the submitted file. */
                    $buffer = fgets($handle, 4096);
                    /* The file contains sometimes also form feed character
                       (^L, 0x0c) which shall be removed as well. */
                    $trimmed = trim($buffer, " \t\n\r\0\x0b\x0c\xa0");
                    /* The file may also contain some empty lines, and trimming
                       the form feed will generate another empty line. */
                    if (empty ($trimmed))
                    {
                        /* Skip empty lines. */
                        continue;
                    }
                    if ($skipHeader)
                    {
                        /* Skip the header line in case of FORMAT_WEBKOS_2015/IKOS.
                           The (again) updated WEBKOS format adds an extra header line in the format
                              KOSI export_prez_sez_<term_id e.g. B162>_<lecture_id e.g. 11FY1>;;;;;;;;;;;;
                           and an extra column "Typ programu". */
                        if ($row == 0 && strpos($buffer, 'KOSI export_prez_sez') === 0) {
                            /* New WEBKOS format:
                             * - the initial version goes back to cca 2017 and had 14 columns
                             * - in 09/2020 a new version with 15 columns has been introduced
                             */
                            $semester_str = substr($trimmed, 22, 3);
                            if ($semester_str === false) {
                                throw new Exception('WEBKOS export format mismatch: cannot find semester number');
                            }
                            $semester = intval($semester_str);
                            error_log("semester_str = $semester_str");
                            error_log("semester = $semester");
                            if ($semester < 200) {
                                $format = self::FORMAT_WEBKOS_2017;
                                error_log('import::FORMAT_WEBKOS_2017');
                            } else {
                                $format = self::FORMAT_WEBKOS_2020;
                                error_log('import::FORMAT_WEBKOS_2020');
                            }
                        }
                        else
                        {
                            /* For other formats only the first line will be skipped, but for the new WEBKOS
                               format we will skip at least two lines. */
                            $skipHeader = false;
                        }
                        continue;
                    }
                    /* The line contains several fields separated by semicolon. */
                    $la = explode(";", $trimmed);
                    self::dumpVar('la', $la);

                    /* Convert data from the file. */
                    $data = array();
                    switch ($format)
                    {
                        case self::FORMAT_TERMINAL:
                            $data['surname'] = iconv("windows-1250", "utf-8", trim($la[1], " \t\n\r\""));
                            $data['firstname'] = iconv("windows-1250", "utf-8", trim($la[2], " \t\n\r\""));
                            $data['yearno'] = trim($la[3], " \t\n\r\"");
                            $data['groupno'] = trim($la[4], " \t\n\r\"");
                            $data['email'] = trim($la[5], " \t\n\r\"");
                            $emailex = explode("@", $data['email']);
                            $data['login'] = trim($emailex[0], " \t\n\r\"");
                            $data['hash'] = trim($la[6], " \t\n\r\"");
                            /* We have the possibility to not specify `cvutid` at all - this corresponds to the
                               original terminal version of KOS. */
                            if (count($la) <= 7)
                                $data['cvutid'] = 0;
                            else
                                $data['cvutid'] = trim($la[7], " \t\n\r\"");
                            /* In some years, students from Decin do not have a group
                                     number. We will assign them with group id 0 which is not
                                     used anywhere. */
                            if (empty ($data['groupno'])) $data['groupno'] = "0";
                            break;
                        case self::FORMAT_WEBKOS_2015:
                        case self::FORMAT_WEBKOS_2017:
                        case self::FORMAT_WEBKOS_2020:
                            $idx = array(
                                self::FORMAT_WEBKOS_2015 => array(
                                    'yearno' => 9,
                                    'groupno' => 11,
                                    'numcols' => 13,
                                    'manual_login' => 12,
                                    'manual_email' => 13),
                                self::FORMAT_WEBKOS_2017 => array(
                                    'yearno' => 10,
                                    'groupno' => 13,
                                    'numcols' => 14,
                                    'manual_login' => 13,
                                    'manual_email' => 14),
                                self::FORMAT_WEBKOS_2020 => array(
                                    'yearno' => 10,
                                    'groupno' => 13,
                                    'numcols' => 15,
                                    'manual_login' => 14,
                                    'manual_email' => 15));
                            $data['surname'] = iconv("windows-1250", "utf-8", trim($la[0], " \t\n\r\"\xa0"));
                            $data['firstname'] = iconv("windows-1250", "utf-8", trim($la[1], " \t\n\r\"\xa0"));
                            // up to 2012-11-05 ... $data['yearno']    = trim ( $la[8], " \t\n\r\"" );
                            $i = $idx[$format]['yearno'];
                            $data['yearno'] = trim($la[$i], " \t\n\r\"");
                            // up to 2012-11-05 ... $data['groupno']   = trim ( $la[10], " \t\n\r\"" );
                            $i = $idx[$format]['groupno'];
                            $data['groupno'] = trim($la[$i], " \t\n\r\"");
                            $cvutid = trim($la[2], " \t\n\r\"");
                            $data['cvutid'] = $cvutid;
                            $data['hash'] = $cvutid;
                            /* In some years, students from Decin do not have a group
                             number. We will assign them with group id 0 which is not
                            used anywhere. */
                            if (empty ($data['groupno'])) $data['groupno'] = "0";

                            /* We have the possibility to append `login` and `e-mail` information manually, circumventing
                               the need for LDAP queiries (again, this is mostly necessary for Decin). */
                            $this->dumpVar('count(la)', count($la));
                            if (count($la) <= $idx[$format]['numcols']) {
                                /* Fetch information from LDAP about this student. */
                                $info = $ldap->searchSingle("cvutid=$cvutid");

                                /* Check that the returned record has `dn` field */
                                if (is_null($info)) {
                                    throw new Exception (
                                        "Row $row: LDAP info neobsahuje záznam pro ČVUT ID `$cvutid`"
                                    );
                                }

                                /* Check that the returned record has `dn` field */
                                if (!array_key_exists('dn', $info)) {
                                    throw new Exception (
                                        'LDAP info neobsahuje DN záznam pro studenta ' .
                                        $data['firstname'] . ' ' .
                                        $data['surname'] . ' (cvutid ' .
                                        $cvutid . ')'
                                    );
                                }

                                /* First `cn` record in DN string contains the login. */
                                $dn_data = $ldap->parseLdapDn($info['dn']);
                                self::dumpVar('dn_data', $dn_data);
                                if (!array_key_exists('cn', $dn_data)) {
                                    throw new Exception (
                                        'LDAP DN záznam pro studenta ' .
                                        $data['firstname'] . ' ' .
                                        $data['surname'] . ' (cvutid ' .
                                        $cvutid . ') neobsahuje `cn`.'
                                    );
                                }
                                $data['login'] = $dn_data['cn'][0];

                                /* Check that the returned record has also `mail` field. */
                                if (!array_key_exists('mail', $info)) {
                                    $errStr .=
                                        'LDAP info neobsahuje údaje o e-mailu pro studenta ' .
                                        $data['firstname'] . ' ' .
                                        $data['surname'] . ' (cvutid ' .
                                        $cvutid . ')<br/>';
                                    /* ... guess the missing information. */
                                    $data['email'] = $data['login'] . '@fd.cvut.cz';
                                } else {
                                    /* ... and fill in the missing information. */
                                    $data['email'] = $info['mail'][0];
                                }
                            } else {
                                /* Manually extended WebKOS output with login and e-mail information. */
                                $i = $idx[$format]['manual_login'];
                                $data['login'] = $la[$i];
                                /* Check that the data contains something meaningful */
                                if (empty ($data['login'])) {
                                    throw new Exception("Row $row: Expected field $i to contain login, but the field is empty.");
                                }
                                $i = $idx[$format]['manual_email'];
                                $data['email'] = $la[$i];
                                /* Check that the data contains something meaningful */
                                if (empty ($data['login'])) {
                                    throw new Exception("Row $row: Expected field $i to contain e-mail address, but the field is empty.");
                                }
                            }

                            break;
                        case self::FORMAT_IKOS:
                            $data['surname'] = iconv("windows-1250", "utf-8", trim($la[1], " \t\n\r\""));
                            $data['firstname'] = iconv("windows-1250", "utf-8", trim($la[2], " \t\n\r\""));
                            $data['yearno'] = trim($la[11], " \t\n\r\"");
                            $data['groupno'] = trim($la[12], " \t\n\r\"");
                            $data['login'] = strtolower(trim($la[5], " \t\n\r\""));
                            $data['email'] = $data['login'] . "@fd.cvut.cz";
                            $data['hash'] = trim($la[3], " \t\n\r\"");
                            $data['cvutid'] = trim($la[4], " \t\n\r\"");
                            /* In some years, students from Decin do not have a group
                                     number. We will assign them with group id 0 which is not
                                     used anywhere. */
                            if (empty ($data['groupno'])) $data['groupno'] = "0";
                            break;
                        case self::FORMAT_IKOS_ROZ:
                            /* This type of output contains a complete name of student in a single field.
                               We will do out best to decipher the name and surname part, but obviously in some
                               cases we will be wrong (for multi-word surnames) */
                            $namefield = explode(" ", $la[2], 2);
                            $data['surname'] = iconv("windows-1250", "utf-8", trim($namefield[0], " \t\n\r\""));
                            $data['firstname'] = iconv("windows-1250", "utf-8", trim($namefield[1], " \t\n\r\""));
                            $data['yearno'] = trim($la[10], " \t\n\r\"");
                            $data['groupno'] = trim($la[11], " \t\n\r\"");
                            $data['login'] = strtolower(trim($la[4], " \t\n\r\""));
                            $data['email'] = $data['login'] . "@fd.cvut.cz";
                            $data['hash'] = trim($la[3], " \t\n\r\"");
                            $data['cvutid'] = trim($la[3], " \t\n\r\"");
                            /* In some years, students from Decin do not have a group
                               number. We will assign them with group id 0 which is not
                               used anywhere. */
                            if (empty ($data['groupno'])) $data['groupno'] = "0";
                            break;
                        default:
                            throw new Exception ('Neplatný formát vstupního souboru.');
                    }

                    /* If requested, increment the year number. */
                    if ($addyear)
                    {
                        $data['yearno']++;
                    }

                    /* Append the group number to the list of group numbers. */
                    $group = (int)$data['groupno'];
                    $groupList[$group] = $group;

                    /* Check the format of the file. */

                    /* Append the record to the list of displayed names. */
                    $studentList[$row] = $data;
                    $row++;
                }

                /* Close the input file. */
                fclose($handle);
            }
            else
            {
                /* The file cannot be opened for reading. */
                $this->action = 'e_open';
                return ERR_ADMIN_MODE;
            }
        }
        else
        {
            /* Possible file upload attack.. */
            $this->action = 'e_upload';
            return ERR_FILE_UPLOAD_ATTACK;
        }

        /* Close the LDAP connection. */
        $ldap->close();

        /* Make the group list sorted. */
        sort($groupList);

        /* Publish the list of students and groups. */
        $this->assign('studentList', $studentList);
        $this->assign('groupList', $groupList);
        $this->assign('errors', $errStr);
        return RET_OK;
    }

    /* -------------------------------------------------------------------
       HANDLER: SAVE
       ------------------------------------------------------------------- */
    function doSave()
    {
        /* Assign POST variables to internal variables of this class and
           remove evil tags where applicable. */
        $this->processPostVars();

        $errstr = '';
        if (empty ($this->firstname)) $errstr .= "<li>Chybí křestní jména</li>\n";
        if (empty ($this->surname)) $errstr .= "<li>Chybí příjmení</li>\n";
        if (empty ($this->yearno)) $errstr .= "<li>Chybí ročníky</li>\n";
        if (empty ($this->groupno)) $errstr .= "<li>Chybí skupiny</li>\n";
        if (empty ($this->login)) $errstr .= "<li>Chybí loginy</li>\n";
        if (empty ($this->email)) $errstr .= "<li>Chybí emaily</li>\n";

        if (empty ($this->hash)) $errstr .= "<li>Chybí heše</li>\n";
        if (empty ($this->cvutid)) $errstr .= "<li>Chybí ČVUT ID</li>\n";
        if (empty ($this->groups)) $errstr .= "<li>Chybí seznam importovaných studijních skupin</li>\n";

        if (empty ($errstr))
        {
            //self::dumpVar('cvutid', $this->cvutid );
            //self::dumpVar('cvutid', $this->firstname );

            /* Get the information about evaluation. We have to initialise
               points to PTS_NOT_CLASSIFIED. Abort if there is no evaluation. */
            $evaluationBean = new EvaluationBean (0, $this->_smarty, NULL, NULL);
            /* This will initialise EvaluationBean with evaluation scheme for
               the current lecture (the id of the lecture is stored in the
               session). The function returns 'true' if the evaluation scheme
               has been found and the object has been initialised. */
            $ret = $evaluationBean->initialiseFor(SessionDataBean::getLectureId(), $this->schoolyear);

            /* Check the initialisation status. */
            if (!$ret)
            {
                /* No evaluation for this school year, abort. */
                $this->action = 'e_init';
                return ERR_ADMIN_MODE;
            }

            $num = count($this->firstname);

            //$urole  = SessionDataBean::getUserRole();
            //$ulogin = SessionDataBean::getUserLogin();

            /* Get the list of tasks for evaluation of this exercise. The list
               will contain only task IDs and we will have to fetch task and
               subtask information by ourselves later. */
            $taskList = $evaluationBean->getTaskList();

            /* This will both create a full list of subtasks corresponding to
               the tasks of the chosen evaluation scheme and assign this list
               to the Smarty variable 'subtaskList'. */
            $tsbean = new TaskSubtasksBean (0, $this->_smarty, null, null);
            $subtaskMap = $tsbean->getSubtaskMapForTaskList($taskList, $evaluationBean->eval_year);
            $subtasks = array_keys($subtaskMap);

            self::dumpVar("subtaskMap", $subtaskMap);
            self::dumpVar("subtasks", $subtasks);

            /* Flip the groups[] array so that we can identify the group using
               simple isset() call. */
            $groups = array_flip($this->groups);

            $row = 0;
            //$date = getdate ();
            $stlist = array();

            $ptbean = new PointsBean (0, $this->_smarty, null, null);
            while ($row < $num)
            {
                /* Get the numeric group number. */
                $groupno = intval($this->groupno[$row]);
                /* Check that this group is to be imported. */
                if (!isset ($groups[$groupno]))
                {
                    $row++;
                    continue;
                }

                $sb = new StudentBean (null, $this->_smarty, null, null);
                $sb->setId($this->cvutid[$row]);
                $sb->setHash($this->hash[$row]);
                $sb->setSurname($this->surname[$row]);
                $sb->setFirstName($this->firstname[$row]);
                $sb->setGroupNo($groupno);
                $sb->setYearNo(intval($this->yearno[$row]));
                $sb->setCalendarYear($this->schoolyear);
                $sb->setLogin($this->login[$row]);
                $sb->setEmail($this->email[$row]);
                $sb->setCoeff(1.0);
                $sb->setPassword(null);
                $sb->dbReplace('(locked, logs in via USERMAP)');
                $id = $sb->getObjectId();

                /* Now that the id of the student is valid we can add it to the
                   list of students that attend the lecture. */
                $stlist[] = $id;

                /* Mark all the subtasks as "not classified". */
                foreach ($subtasks as $val)
                {
                    /* Do not convert point value to its corresponding numeric
                       representation as it has been passed as a numeric
                       value. */
                    $ptbean->updatePoints(
                        $id, $val, $this->schoolyear,
                        PointsBean::PTS_NOT_CLASSIFIED, '', false);
                }

                $row++;
            }

            self::dumpVar('stlist', $stlist);

            /* Write the list of students attending the lecture into the
               database. */
            $seb = new StudentLectureBean (SessionDataBean::getLectureId(), $this->_smarty, NULL, NULL);
            $seb->year = $this->schoolyear;
            $seb->setStudentList($stlist, false);
        }
        else
        {
            /* Cannot process the submitted data. */
            $this->action = 'e_process';
            $this->assign('errormsg', $errstr);
            return ERR_ADMIN_MODE;
        }

        return RET_OK;
    }

    /* -------------------------------------------------------------------
       HANDLER: DELETE
       ------------------------------------------------------------------- */
    function doDelete()
    {
        trigger_error(
            "Delete action not implemented - " .
            " (" . get_class($this) . ")");
        return ERR_INVALID_ACTION;
    }

    /* -------------------------------------------------------------------
       HANDLER: REAL DELETE
       ------------------------------------------------------------------- */
    function doRealDelete()
    {
        trigger_error(
            "Real delete action not implemented - " .
            " (" . get_class($this) . ")");
        return ERR_INVALID_ACTION;
    }
}

?>
