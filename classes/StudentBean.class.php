<?php

/* Result types */
define('SB_STUDENT_ANY', 1);
define('SB_STUDENT_POSITIVE', 2);
define('SB_STUDENT_NEGATIVE', 3);

/* Type of output sort */
define('SB_SORT_BY_ID', 1);
define('SB_SORT_BY_NAME', 2);
define('SB_SORT_BY_LOGIN', 3);

function studentListIdCmp($a, $b)
{
    /* Identifiers are unsigned integers that are stored as signed integers,
       because PHP does not have unsigned int. */
    $ha = dechex($a['id']);
    $hb = dechex($b['id']);

    /* Both hexa strings may be of different length and the following comparison
       will not work. */
    $ret = strcmp($ha, $hb);

    /*
    echo "<!-- comparing '";
    var_dump($a['id']);
    echo "' and '";
    var_dump($b['id']);
    echo "' as $ret -->\n";
    */

    /* So this is the parachute in case that one hexa string is shorter. */
    if (strlen($ha) < strlen($hb)) return -1;
    if (strlen($ha) > strlen($hb)) return 1;

    return $ret;
}

function studentListLoginCmp($a, $b)
{
    /* Identifiers are unsigned integers that are stored as signed integers,
       because PHP does not have unsigned int. */
    $ha = $a['login'];
    $hb = $b['login'];

    /* Compare both login strings lexicographically. */
    $ret = strcmp($ha, $hb);

    return $ret;
}

class StudentBean extends DatabaseBean
{
    private $login;
    private $password;
    private $hash;
    private $surname;
    private $firstname;
    private $yearno;
    private $groupno;
    private $email;
    private $calendaryear;
    private $coeff;

    private $twistMatrix;
    private $role;
    private $fullList;

    /* We will need to mangle the student ids in some deterministic way. We will
       do it by encrypting the id and this variable will hold the handle to
       initialised encrypting module. */
    var $td;

    function _setDefaults()
    {
        $this->login = $this->rs['login'] = "";
        $this->password = $this->rs['password'] = "";
        $this->hash = $this->rs['hash'] = "";
        $this->surname = $this->rs['surname'] = "";
        $this->firstname = $this->rs['firstname'] = "";
        $this->yearno = $this->rs['yearno'] = 0;
        $this->groupno = $this->rs['groupno'] = 0;
        $this->email = $this->rs['email'] = "";
        $this->calendaryear = $this->rs['calendaryear'] = 0;
        $this->role = $this->rs['role'] = USR_STUDENT;
        $this->fullList = false;
        $this->coeff = $this->rs['coeff'] = 1.0;
    }

    /* Constructor */
    function __construct($id, &$smarty, $action, $object)
    {
        /* Call parent's constructor first */
        parent::__construct($id, $smarty, "student", $action, $object);
        /* Initialise new properties to their default values. */
        $this->_setDefaults();
        /* Initialuse the Hill cipher matrix. */
        $this->twistMatrix = array(
            array(70, 173, 98, 96),
            array(105, 224, 179, 27),
            array(43, 162, 187, 22),
            array(8, 69, 150, 236));
    }

    function _getFullList($where = '')
    {
        $rs = DatabaseBean::dbQuery(
            "SELECT * FROM student" . $where . " ORDER BY surname,firstname,yearno,groupno");
        if (isset ($rs))
        {
            foreach ($rs as $key => $val)
            {
                $rs[$key]['surname'] = vlnka(stripslashes($val['surname']));
                $rs[$key]['firstname'] = vlnka(stripslashes($val['firstname']));
            }
        }
        return $rs;
    }

    /**
     * Check if the login is empty (which is the default).
     * This is used to detect possible login attacks and user errors.
     * @return bool True if the login name of the user is empty
     */
    function hasEmptyLogin()
    {
        return empty($this->login);
    }

    /**
     * Get the login.
     * @return string Login of the student.
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Get the surname of the student.
     * @return string Surname of the student.
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Get the first name of the student
     * @return string First name of the student.
     */
    public function getFirstName()
    {
        return $this->firstname;
    }

    /**
     * Get the time scaling coefficient for impaired students.
     * @return float The scaling coefficient, in general >= 1.0.
     */
    public function getCoeff()
    {
        return $this->coeff;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param mixed $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @param mixed $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstName($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @param mixed $yearno
     */
    public function setYearNo($yearno)
    {
        $this->yearno = $yearno;
    }

    /**
     * @param mixed $groupno
     */
    public function setGroupNo($groupno)
    {
        $this->groupno = $groupno;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param mixed $calendaryear
     */
    public function setCalendarYear($calendaryear)
    {
        $this->calendaryear = $calendaryear;
    }

    /**
     * Set the time scaling coefficient for impaired students.
     * @param float $coeff The scaling coefficient, in general >= 1.0.
     */
    public function setCoeff($coeff)
    {
        $this->coeff = $coeff;
    }


    /**
     * Replace or insert student entry in the database.
     * As we have to support both hash-based and id-based student identification (hashes being
     * historically the hashed values of Czech social security number (or personal id), which
     * were replaced by CVUT usermap ID some time ago), we have three choices when inserting
     * student information:
     * (a) student id is 0 and there is no corresponding entry in the database
     *     based on the hash - this is a new student that did not exist in the
     *     system before;
     * (b) student id is >0 and there is no corresponding entry in the database
     *     based on the hash - this is a new (or possibly duplicate, but exported
     *     from the new interface) student;
     * (c) student id is >0 and there is a corresponding entry in the database
     *     based on the hash - this is an existing student.
     * @param null $pw_field_text
     */
    function dbReplace($pw_field_text = null)
    {
        /* Temporary storage for student id based on the hash query. */
        $dbId = 0;

        /* In case that the hashes collide, the original entry will be automatically
           deleted by the update process and this is not what we want. Hence we will
           try to get the id of the existing entry based on the hash. */
        $rs = DatabaseBean::dbQuery(
            "SELECT id FROM student WHERE hash=MD5('" . $this->hash . "')");
        /* In case the query was not successful, `rs` will be empty. */
        if (!empty ($rs))
        {
            $dbId = intval($rs[0]['id']);
            self::dumpVar('got an existing student -- see the result of the hash query', $rs);
        }

        /* No id of the student or no id in the database. This suggests that the
           student is not in the database yet. */
        if (!$this->id || ($this->id && !$dbId))
        {
            /* Check for password override. When initialising the student record we have to mark the MD5 hash
               of the entry so that it cannot be equal to a MD5 hash of a password. */
            if (is_null($pw_field_text))
            {
                /* No password field override specified, we will encode the password given. */
                $pw_field = array('MD5(%s)', $this->password);
            }
            else
            {
                /* We shall put the text specified in $pw_field_test directly into the password field without
                   hashing it. This is used to indicate an invalid or locked password. */
                $pw_field = $this->password;
            }

            /* Standard replace creates also the hash. */
            $args = [
                'id' => $this->id,
                'hash%sql' => array('MD5(%s)', $this->hash),
                'login' => $this->login,
                'password' => $pw_field,
                'surname' => $this->surname,
                'firstname' => $this->firstname,
                'yearno' => $this->yearno,
                'groupno' => $this->groupno,
                'calndaryear' => $this->calendaryear,
                'email' => $this->email,
                'coeff' => $this->coeff
            ];
            dibi::query('REPLACE `student`', $args);

            /* New records have initial 'id' equal to zero and the proper value is
               set by the database engine. We have to retrieve the 'id' back so that
               we can later use it to reference this object. */
            $this->updateId();
        }
        else
        {
            /* Student exists in the database. Keep the hash intact and update
               everything else. */
            $args = [
                'login' => $this->login,
                'surname' => $this->surname,
                'firstname' => $this->firstname,
                'yearno' => $this->yearno,
                'groupno' => $this->groupno,
                'calndaryear' => $this->calendaryear,
                'email' => $this->email,
                'coeff' => $this->coeff
            ];
            dibi::query('UPDATE `student` SET ', $args, 'WHERE `id`=%i', $this->id);
        }
    }

    /* ---------------------------------------------------------------------
         LDAP verification.
         --------------------------------------------------------------------- */
    function ldapCheckLoginWithSearch($login, $password)
    {
        /* Prevent code and control character injection. */
        $login = addslashes($login);
        $password = addslashes($password);

        /* Ignore empty logins or passwords. */
        if (empty($login))
        {
            return FALSE;
        }

        if (empty($password) || strlen($password) < 1)
        {
            return FALSE;
        }

        /* Connect to our LDAP server. */
        $ldap = new LDAPConnection ($this->_smarty);

        /* Find if the user with the given login exists and
           if so, return the user information. */
        $cn = "cn=$login";
        $info = $ldap->searchSingle($cn);
        $this->dumpVar('ldap_info', $info);

        /* If the username has been found, try to verify it. */
        if (isset ($info))
        {
            $ldapbind = $ldap->bind($info['dn'], $password);
            $this->dumpVar('ldap_bind', $ldapbind);
            if ($ldapbind === TRUE)
            {
                return TRUE;
            }
            else
            {
                $errno = ldap_errno($ldap);
                $estr = ldap_err2str($errno);
                $res = "errno = $errno, estr  = $estr";
                $this->dumpVar('ldap_bind_error', $res);
            }
        }

        return FALSE;
    }

    /**
     *
     */
    function ldapCheckLogin($login, $password)
    {
        /* Prevent code and control character injection. */
        $login = addslashes($login);
        $password = addslashes($password);

        /* Ignore empty logins or passwords. */
        if (empty($login))
        {
            return FALSE;
        }

        if (empty($password) || strlen($password) < 1)
        {
            return FALSE;
        }

        /* Connect to our LDAP server. */
        $ldap = new LDAPConnection ($this->_smarty);

        /* Do not look for the user by anonymous search in the database,
           rather construct the full `dn` manually and try to bind
           directly. */
        $basedn = $ldap->getBaseDN();
        $dn = "cn=$login,$basedn";

        $ldapbind = $ldap->bind($dn, $password);
        $this->dumpVar('ldap_bind', $ldapbind);
        if ($ldapbind === TRUE)
        {
            return TRUE;
        }
        else
        {
            list($errno, $estr) = $ldap->getError();
            $res = "errno = $errno, estr  = $estr";
            $this->dumpVar('ldap_bind_error', $res);
            error_log("LDAP bind error for $dn -- $errno: $estr", 0);
        }

        return FALSE;
    }

    /* Update just the student password. The same function is in UserBean! */
    function dbUpdatePassword($pass)
    {
        /* Set the password */
        $this->password = $pass;
        /* Standard replace does not replace passwords */
        $args = [
            'password%sql' => array('MD5(%s)', $this->password)
        ];
        dibi::query('UPDATE `student` SET ', $args, 'WHERE `id`=%i', $this->id);
    }

    /**
     * Check the student login.
     * A similar function can be found in LoginBean - here we also check the LDAP
     * in case that verification against out database fails.
     */
    function dbCheckLogin($login, $password)
    {
        $rs = null;
        /* Query the database for the login and password tuple. */
        $args = [
            'login' => $login,
            'password%sql' => array('MD5(%s)', $password)
        ];
        $result = dibi::query('SELECT * FROM `user` WHERE %and', $args);

        Debug::barDump($result, 'StudentBean login check after db query');

        /* In case that the LDAP server is down for some reason, we have the
           possibility to skip the LDAP check. */
        if (empty ($result) && (LDAPConnection::isActive($this->_smarty)))
        {
            /* As a last resort, try to contact the LDAP server and verify
               the user. */
            $result = dibi::query('SELECT * FROM `student` WHERE `login`=%s', $login);
            if (!empty ($result))
            {
                /* Login exists, check the password. */
                $valid = $this->ldapCheckLogin($login, $password);
                /* If the password check failed, clear the contents of $rs. */
                if (!$valid) $result = null;
            }
        }

        /* Empty $result now signals that the student could not be verified. */
        if (!empty ($result))
        {
            Debug::barDump($result, 'StudentBean result after ldap check');

            /* If the verification succeeded, we have to update the information
               about the student so that the student's home page gets displayed
               correctly. */
            $row = $result->fetch();
            $this->id = $row['id'];
            $this->dbQuerySingle();
            /* Set the returned record. */
            $rs = $this->rs;
        }

        return $rs;
    }

    function dbQuerySingle($alt_id = 0)
    {
        /* Query the data of this student (ID has been already specified) */
        DatabaseBean::dbQuerySingle($alt_id);
        /* Initialize the internal variables with the data queried from the
           database. */
        $this->firstname = vlnka(stripslashes($this->rs['firstname']));
        $this->surname = vlnka(stripslashes($this->rs['surname']));
        $this->yearno = $this->rs['yearno'];
        $this->groupno = $this->rs['groupno'];
        $this->email = $this->rs['email'];
        $this->login = $this->rs['login'];
        $this->coeff = $this->rs['coeff'];
        /* Mask password if one exists */
        if (!empty ($this->rs['password']))
        {
            $this->password = $this->rs['password'] = PASSWORD_MASK;
        }
        else
        {
            $this->password = $this->rs['password'];
        }
        /* Publish the student data */
        $this->rs['firstname'] = $this->firstname;
        $this->rs['surname'] = $this->surname;
        /* Set the role. */
        $this->role = $this->rs['role'] = USR_STUDENT;
        /* Set the hashed/encrypted id. */
        $this->rs['twistid'] = $this->twistId($this->id);
    }

    /**
     * Check for additional variables submited as a part of GET request.
     */
    function processGetVars()
    {
        //assignGetIfExists ( $fullList, NULL, 'full' );
        //$this->fullList = (bool) $fullList;
        self::dumpThis();
    }

    /* Assign POST variables to internal variables of this class and
       remove evil tags where applicable. We shall probably also remove
       evil attributes et cetera, but this will be done later if ever. */
    function processPostVars()
    {
        $this->id = $_POST['id'];
        $this->login = trimStrip($_POST['login']);
        $this->surname = trimStrip($_POST['surname']);
        $this->firstname = trimStrip($_POST['firstname']);
        $this->hash = trimStrip($_POST['hash']);
        $this->yearno = (integer)trimStrip($_POST['yearno']);
        $this->groupno = (integer)trimStrip($_POST['groupno']);
        $this->email = trimStrip($_POST['email']);
        $this->calendaryear = intval($_POST['calendaryear']);
        $this->coeff = (float)$_POST['coeff'];
    }

    /* ---------------------------------------------------------------------
       Send an e-mail to the student id with the new password.
       --------------------------------------------------------------------- */
    function sendPassword($pass)
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

        $message = "Uživatel '" . $userLogin . "' (" . $userName . ") změnil vaše heslo\r\n";
        $message .= "pro přihlašování na stránky předmětů vyučovaných K611.\r\n";
        $message .= "\r\n";
        $message .= "Vaše uživatelské jméno: " . $this->login . "\r\n";
        $message .= "Vaše nové heslo zní: " . $pass . "\r\n";
        $message .= "\r\n";
        $message .= "Pokud máte konto na FD v Praze a v KOSu e-mail ve tvaru <x#####@fd.cvut.cz>,\r\n";
        $message .= "mohlo by fungovat i vaše primární heslo (to, kterým se přihlašujete na fakultní síť).\r\n";
        $message .= "Pokud ne, zkuste heslo uvedené výše.\r\n";
        $message .= "Přihlašování přes SSU bohužel stále ještě není možné.\r\n";
        $message .= "\r\n";
        $message .= "Stránky předmětů naleznete na URL\r\n";
        $message .= "\thttp://zolotarev.fd.cvut.cz/msap/ (11MSAP)\r\n";
        $message .= "\thttp://zolotarev.fd.cvut.cz/ma/ (11MA prezenční)\r\n";
        $message .= "\thttp://zolotarev.fd.cvut.cz/mak/ (11MA pro kombinovanou formu)\r\n";
        $message .= "\r\n";

        /* Now send the notification to the student ... */
        if (SEND_MAIL)
        {
            $subject = "=?utf-8?B?" . base64_encode('[K611LW] Nové heslo pro přístup') . "?=";
            mail($this->email, $subject, $message, $header);
        }

        /* ... and send a copy to the administrator. */
        $subject = "=?utf-8?B?" . base64_encode('[K611LW] Student <' . $this->email . '> - nové heslo pro přístup') . "?=";
        mail(ADMIN_EMAIL, $subject, $message, $header);
    }

    /**
     * Assign a list of students to Smarty variable.
     * Queries the database for a list of students, possibly limited by the contents of `where` string.
     * Assigns the result to `studentList` template variable.
     * @param string $where SQL WHERE clause, optional.
     * @param string $limit SQL LIMIT clause, optional.
     */
    function assignStudentList($where = '', $limit = NULL)
    {
        $limitStr = '';
        if (!empty ($limit))
        {
            $limitStr = ' LIMIT ' . $limit['offset'] . ',' . $limit['count'];
        }

        $rs = DatabaseBean::dbQuery(
            "SELECT id, surname, firstname, yearno, groupno " .
            " FROM student " . $where . " ORDER BY surname" . $limitStr
        );

        if (isset ($rs))
        {
            foreach ($rs as $key => $val)
            {
                $rs[$key]['surname'] = stripslashes($val['surname']);
                $rs[$key]['firstname'] = stripslashes($val['firstname']);
            }
        }

        $this->_smarty->assign('studentList', $rs);
    }

    /* $exerciseBinding contains exerciseList index for every valid student id. */
    function assignStudentListWithExercises(
        $lectureId,
        $numExercises,
        $exerciseBinding
    )
    {
        /* Initial list of students is empty. */
        $studentList = array();

        /* We want to see only those students who are studying the given lecture.
           The list of students can be empty, whould would result in an invalid
           `IN()` clause. Hence the one-parameter variant of `arrayToDBString()`
           which adds zero to the list. */
        $seb = new StudentLectureBean ($lectureId, $this->_smarty, "x", "x");
        $studentIds = $seb->getStudentListForLecture();
        $where = arrayToDBString($studentIds);

        $resultset = DatabaseBean::dbQuery(
            "SELECT id, surname, firstname, yearno, groupno " .
            "FROM student WHERE id IN(" . $where . ") ORDER BY surname, firstname"
        );

        if (isset ($resultset))
        {
            foreach ($resultset as $key => $val)
            {
                $id = $val['id'];
                $studentList[$key]['id'] = $id;
                $studentList[$key]['surname'] = stripslashes($val['surname']);
                $studentList[$key]['firstname'] = stripslashes($val['firstname']);
                $studentList[$key]['yearno'] = $val['yearno'];
                $studentList[$key]['groupno'] = $val['groupno'];

                /* This array will be added to every student. By default it is empty,
                   and only a single item will be modified to contain the "checked"
                   flag for the activation of the corrsponding radio button. */
                $checked = array_fill(0, $numExercises + 1, '');
                /* The `bindKey` contains the identifier of an exercise. If the
                   student is not assigned into any exercise yet, the array element
                   is empty. */
                if (array_key_exists($id, $exerciseBinding))
                {
                    $bindKey = $exerciseBinding[$id];
                    if ($bindKey === NULL || $bindKey < 0 || $bindKey >= $numExercises)
                    {
                        $trace = getDebugBacktrace();
                        trigger_error(
                            "Suspicious bind key '" .
                            var_export($bindKey, TRUE) . "' for student " .
                            $studentList[$key]['firstname'] . " " .
                            $studentList[$key]['surname'] . " (id " . $id . ")" .
                            $trace);
                    }
                }
                else
                {
                    $bindKey = NULL;
                }
                $this->dumpVar("bindkey[$id]", $bindKey);
                /* The last position in the list of exercises is a fall-through for
                   unassigned students. */
                if ($bindKey === NULL) $bindKey = $numExercises;
                /* Correct HTML has to contain a string parameter of the `checked`
                   attribute. */
                $checked[$bindKey] = ' checked="checked"';
                /* Finally modify the original contents of `studentList`. */
                $studentList[$key]['checked'] = $checked;
            }

            $this->dumpVar("userList", $studentList);
            $this->dumpVar("resultset", $resultset);
        }

        $this->_smarty->assign('studentList', $studentList);

        return $studentList;
    }

    function twistId($id)
    {
        $x = array();
        $x[0] = $id & 0x000000ff;
        $x[1] = ($id & 0x0000ff00) >> 8;
        $x[2] = ($id & 0x00ff0000) >> 16;
        $x[3] = ($id & 0xff000000) >> 24;

        $y = array();
        $a = $this->twistMatrix;
        for ($i = 0; $i < 4; $i++)
        {
            $y[$i] = ($a[$i][0] * $x[0] + $a[$i][1] * $x[1] +
                    $a[$i][2] * $x[2] + $a[$i][3] * $x[3]) % 256;
        }

        $yy = $y[0] + ($y[1] << 8) + ($y[2] << 16) + ($y[3] << 24);
        return $yy;
    }

    function twistId2($id)
    {

        $from = '0123456789abcdef';
        $to = '93b2e0c57f6ad841';

        /* Encrypt data */
        /*$tid = $id ^ 0xae1385;
        $strid1 = dechex ( $tid );
        $strid2 = strtr ( $strid1, $from, $to );
        $newid  = hexdec ( $strid2 );

        echo "<!--\n";
        echo "  id .......... " . decbin($id)  . "\n";
        echo "  tid ......... " . decbin($tid) . "\n";
        echo "  newid ....... " . decbin($newid) . "\n";
        echo "-->\n";
        */

        $strid1 = sprintf('%024b', $id);

        $strid2 = $strid1[2];
        $strid2 .= $strid1[12];
        $strid2 .= $strid1[13];
        $strid2 .= $strid1[15];
        $strid2 .= $strid1[0];
        $strid2 .= $strid1[18];
        $strid2 .= $strid1[17];
        $strid2 .= $strid1[20];
        $strid2 .= $strid1[8];
        $strid2 .= $strid1[22];
        $strid2 .= $strid1[3];
        $strid2 .= $strid1[6];
        $strid2 .= $strid1[16];
        $strid2 .= $strid1[4];
        $strid2 .= $strid1[9];
        $strid2 .= $strid1[5];
        $strid2 .= $strid1[11];
        $strid2 .= $strid1[10];
        $strid2 .= $strid1[7];
        $strid2 .= $strid1[21];
        $strid2 .= $strid1[14];
        $strid2 .= $strid1[23];
        $strid2 .= $strid1[1];
        $strid2 .= $strid1[19];

        echo "<!--\n";
        echo "  id .......... " . $strid1 . "\n";
        echo "  newid ....... " . $strid2 . "\n";
        echo "-->\n";

        $newid = bindec($strid2);

        return $newid;
    }

    function twistId1($id)
    {
        /* Open the cipher */
        $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');

        /* Create the IV and determine the keysize length, use MCRYPT_RAND
         * on Windows instead */
        $hxv = 'f1ce683e1557d988962ee453bb5429cf8770d27b7b34568b4d569b9a287cbeb7';
        //$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
        $iv = pack('H*', $hxv);
        $ks = mcrypt_enc_get_key_size($td);

        //$this->dumpVar ( 'iv', unpack ( 'H*iv', $iv ));

        /* Create key */
        $key = substr(md5('Zaphod Beeblebrox is the president of the Universe.'), 0, $ks);

        /* Intialize encryption */
        mcrypt_generic_init($td, $key, $iv);

        /* Limit the id length to 24 bits */
        $id = $id & 0xffffff;
        $packedid = pack('Cn', $id / 0x010000, $id & 0x00ffff);

        /* Encrypt */
        $encrypted = mcrypt_generic($td, $packedid);

        /* Parse the resulting three bytes. */
        $packstr = unpack('Chibyte/nloshort', $encrypted);
        $twid = $packstr['hibyte'] * 0x010000 + $packstr['loshort'];

        return $twid;
    }

    function _getTaskId($elem)
    {
        return $elem['id'];
    }

    /**
     * Fetch data for displaying student results table and store them in Smarty variables.
     * Returns the list of students and statistical data.
     * @param $idList
     * @param $points
     * @param $evaluation
     * @param $subtaskMap
     * @param $subtaskList
     * @param $taskList
     * @param int $resType
     * @param int $sortType
     * @param int $lectureId
     * @return array Student list as the first element, statistical data as the second one.
     */
    function getStudentDataFromList(
        $idList,
        $points,
        $evaluation,
        $subtaskMap,
        $subtaskList,
        $taskList,
        $resType = SB_STUDENT_ANY,
        $sortType = SB_SORT_BY_NAME,
        $lectureId = 1
    )
    {
        /* How many entries do we have here. */
        $numEntries = count($idList);
        if ($numEntries == 0) return;

        $dbList = arrayToDBString($idList);

        //$this->dumpVar ( "resType", $resType );
        //$this->dumpVar ( "idList", $idList );
        //$this->dumpVar ( "dbList", $dbList );

        $resultset = DatabaseBean::dbQuery(
            "SELECT id, login, surname, firstname, yearno, groupno FROM student WHERE id IN ("
            . $dbList
            . ") ORDER BY surname,firstname");

        $studentList = array();
        $statData = array('negative' => 0, 'exmAvg' => 0, 'average' => 0);

        /* Number of subtasks. */
        $numSubtasks = count($subtaskList);

        /* What is the number of tasks? */
        $numTskLst = count($taskList);

        /* Data storage used for statistics */
        $negTaskCount = ($numTskLst > 0) ? array_fill(0, $numTskLst, 0) : array();
        /* Number of participants that have some result for the task and subtask. */
        $parTaskCount = ($numTskLst > 0) ? array_fill(0, $numTskLst, 0) : array();
        $parSubCount = ($numSubtasks > 0) ? array_fill(0, $numSubtasks, 0) : array();
        /* Average value of points for the task. */
        $avgTaskData = ($numTskLst > 0) ? array_fill(0, $numTskLst, 0) : array();
        $avgSubtaskData = ($numSubtasks > 0) ? array_fill(0, $numSubtasks, 0) : array();

        $numStudents = count($resultset);

        if (isset ($resultset))
        {
            foreach ($resultset as $key => $val)
            {
                /* Database ID of this student. */
                $id = $val['id'];

                $studentData = array();
                $studentData['id'] = $this->twistId($id);
                $studentData['dbid'] = $id;
                $studentData['surname'] = stripslashes($val['surname']);
                $studentData['firstname'] = stripslashes($val['firstname']);
                $studentData['yearno'] = $val['yearno'];
                $studentData['groupno'] = $val['groupno'];
                $studentData['login'] = $val['login'];

                /* Now add subtask points (array $sPoints) and sum the task points according
                   to task definition (array $xPoints). */
                $sPoints = array();

                $xPoints = array();
                foreach ($taskList as $tval)
                {
                    $xPoints[$tval['id']] = '-';
                }

                //$this->dumpVar ( 'taskList (in assignStudentDataFromList())', $xTaskList );
                //$xPoints = ( $numTskLst > 0 ) ? array_fill_keys ( $xTaskList, '-' ) : array();
                //$xPoints = array_fill_keys ( $xTaskList, '-' );

                if (isset ($subtaskList))
                {
                    foreach ($subtaskList as $sKey => $sVal)
                    {
                        $subId = $sVal['id'];
                        $num = $points[$id][$subId];
                        if (empty ($num)) $num = 0;
                        $sPoints[$sKey] = $num;

                        /* Extract the number of points from the <points><comment> record. */
                        $numPoints = $num['points'];
                        /* It may be something like '-'. */
                        if (is_numeric($numPoints))
                        {
                            $this->dumpVar('numpoints (1) is ', $numPoints);
                            /* Honour the maximum number of points for the given task. */
                            $maxPoints = $sVal['maxpts'];
                            if ($maxPoints > 0 && $numPoints > $maxPoints)
                            {
                                /* Commented ourt by JP 2010-01-17 */
                                //$numPoints = $maxPoints;
                            }
                            $this->dumpVar('numpoints (1) is ', $numPoints);

                            /* $subtaskMap contains subtask_id as a key and task_id as a value. */
                            $tskId = $subtaskMap[$subId];

                            /* Comparing 0 == '-' will result in 'equal' as the dash will
                               be converted to integer, the conversion will fail, and the
                               result of the conversion will therefore be 0. */
                            //if ( strval( $num ) != '-' )
                            if ($xPoints[$tskId] != '-')
                            {
                                $xPoints[$tskId] += $numPoints;
                            }
                            else
                            {
                                $xPoints[$tskId] = 0 + $numPoints;
                            }
                            $this->dumpVar('num[points] is numeric', $num);
                            $this->dumpVar('xPoints (inside)', $xPoints);
                            /* Only in case that numPoints is numeric, count the student
                               as participant and update the average and the count of
                               participants. */
                            $avgSubtaskData[$sKey] = $avgSubtaskData[$sKey] + $numPoints;
                            $parSubCount[$sKey] = $parSubCount[$sKey] + 1;

                            /* TODO: Do not!
                               Increase the total sum of points for this student. */
                            //$sumPoints += $numPoints;
                        }
                        else
                        {
                            $this->dumpVar('num[points] is not numeric', $num);
                        }
                    }
                }
                $studentData['subpoints'] = $sPoints;

                /* ... and place the summed task points on appropriate places as taskList
                   is sorted according to task position field and not according to task id
                   We will also check the taks-bound limits for the evaluation. */
                $positiveEval = true;
                $finalResult = true;

                $tPoints = ($numTskLst > 0) ? array_fill(0, $numTskLst, 0) : array();
                $tClass = ($numTskLst > 0) ? array_fill(0, $numTskLst, '') : array();

                $this->dumpVar('xPoints (cumulative subtask points)', $xPoints);

                $sumPoints = 0;

                if (isset ($taskList))
                {
                    foreach ($taskList as $sKey => $sVal)
                    {
                        $this->dumpVar('sVal', $sVal);
                        $tskId = $sVal['id'];
                        $tskType = $sVal['type'];
                        /* Fetch the previously accumulated points for this task. */
                        $gotPoints = $xPoints[$tskId];
                        /* $gotPoints is a number or '-'. Assure $numPoints will be a number. */
                        $numPoints = 0 + $gotPoints;
                        /* Positive evaluation of this task means that either this task is
                           always positive ('minpts' is zero) or if this task has at least
                           'minpts' achieved. */
                        $minPts = $sVal['minpts'];
                        if ($minPts > 0)
                        {
                            $positive = (boolean)($gotPoints >= $minPts);
                        }
                        else
                        {
                            /* This is not an obligatory field. This means we will be always
                               positive. */
                            $positive = true;
                            /* Massage the output: we do not want to see '-' there and
                               we want to be assigned positive $tClass element. */
                            $gotPoints = $numPoints;
                        }

                        if ($this->_debugMode)
                        {
                            echo "<!-- tskId=$tskId  gotPoints='$gotPoints'  numPoints='$numPoints'  positive=$positive sKey=$sKey -->\n";
                        }

                        /* This will be displayed as a total number of points in this task. */
                        $tPoints[$sKey] = $gotPoints;
                        /* Positive evaluation is only possible if every preceeding task was
                           positive and this task is positive as well. */
                        $positiveEval = (boolean)($positiveEval && $positive);
                        /* Update the count of non-positive students. Make sure the given
                           key will exist even if all students are positive. */
                        if (!$positive) $negTaskCount[$sKey] = $negTaskCount[$sKey] + 1;
                        else $negTaskCount[$sKey] = $negTaskCount[$sKey] + 0;
                        $this->dumpVar('negTaskCount', $negTaskCount);
                        /* Update HTML class list. Again, as $gotPoints may be a string '-',
                           representing all non-numerical values (not classified, excused,
                           copied) or a float value, we have to specify that we want a string
                           comparison of $gotPoints to '-'. Otherwise, '-' would have been
                           converted to integer and the result would be wrong. */
                        if (strval($gotPoints) != '-')
                        {
                            /* Mark results with colour only in case that some measurable
                               result has been achieved. If the student has not been classified
                               yet ($gotPoints == '-'), do not mark the result (that could be
                               converted to 0pt) as positiver or negative. */
                            $tClass[$sKey] = ($positive) ? 'p' : 'n';
                            /* Update sum for the average */
                            $avgTaskData[$sKey] = $avgTaskData[$sKey] + $gotPoints;
                            /* Update the number of participants. */
                            $parTaskCount[$sKey] = $parTaskCount[$sKey] + 1;
                        }
                        else
                        {
                            /* As we got some dashed, this result is not final. Hence, do not
                               mark the overall point gain and classification fields as positive
                               or negative. */
                            $finalResult = false;
                        }

                        /* Finally update the total points of the student, but only in case that this
                           task adds to the total. */
                        if ($tskType != TaskBean::TT_NO_TOTAL_POINTS)
                        {
                            $sumPoints += $gotPoints;
                        }
                    }
                }
                else
                {
                    throw new UnexpectedValueException('Task list cannot be empty!');
                }

                $this->dumpVar('$parTaskCount', $parTaskCount);

                $studentData['taskpoints'] = $tPoints;
                $studentData['taskclass'] = $tClass;

                /* Exam points (0-6).
                   @TODO@ This is hardcoded and it is nonsense to do it this way. */
                if ($lectureId == 1 and $this->schoolyear <= 2009)
                {
                    /* 11MSP */
                    $this->dumpVar('tPoints', $tPoints);
                    if ($this->schoolyear == 2009)
                    {
                        $exmPoints = $tPoints[0] + $tPoints[1] + $tPoints[3];
                        $exmPoints = $exmPoints - 6;
                        $exmPoints = ($exmPoints < 0) ? 0 : (($exmPoints > 6) ? 6 : $exmPoints);
                    }
                    else
                    {
                        $exmPoints = $tPoints[1] + $tPoints[3];
                        $exmPoints = $exmPoints - 6;
                        $exmPoints = ($exmPoints < 0) ? 0 : (($exmPoints > 6) ? 6 : $exmPoints);
                        $exmPoints = $exmPoints + $tPoints[0];
                    }
                }
                elseif ($lectureId == 27)
                {
                    /* 11DOPM */
                    $this->dumpVar('11DOPM tPoints', $tPoints);
                    $this->dumpVar('11DOPM sumPoints', $sumPoints);
                    $intSumPoints = intval($sumPoints);
                    if ($intSumPoints > 90)
                        $exmPoints = 20;
                    elseif ($intSumPoints > 80)
                        $exmPoints = 10;
                    else
                        $exmPoints = 0;
                }
                else
                {
                    $exmPoints = $sumPoints;
                }

                /* There are two types of evaluation: boolean (labs, for example) and
                   graded (A to F).*/
                if ($evaluation->do_grades)
                {
                    if ($positiveEval)
                    {
                        if ($sumPoints >= $evaluation->pts_A)
                            $evalText = 'A';
                        elseif ($sumPoints >= $evaluation->pts_B)
                            $evalText = 'B';
                        elseif ($sumPoints >= $evaluation->pts_C)
                            $evalText = 'C';
                        elseif ($sumPoints >= $evaluation->pts_D)
                            $evalText = 'D';
                        elseif ($sumPoints >= $evaluation->pts_E)
                            $evalText = 'E';
                        else
                        {
                            /* Positive F? This is nonsense, but it seems to be the nature of how the evaluation
                               is set up: `positiveEval` needs another check, namely check of the total points
                               that the student gets. So we will force positiveEval to be false in this case. */
                            $evalText = 'F';
                            $positiveEval = false;
                        }
                    }
                    else
                    {
                        /* Even if they have enough points to get a grade better
                           than F, there are some prerequisities missing. Therefore
                           the final grade is still F. */
                        $evalText = 'F';
                    }
                }
                else
                {
                    /* If the student was positive from all tasks, it does not necessarily
                        mean she/he has got enough points to get the credit. */
                    $positiveEval = (boolean)($positiveEval && ($sumPoints >= $evaluation->pts_E));
                    $evalText = boolToYesNo($positiveEval);
                }

                /* Finally create a summary information. */
                $studentData['gotcredit'] = $evalText;
                $studentData['sumpoints'] = $sumPoints;
                $studentData['exmpoints'] = $exmPoints;
                $studentData['sumclass'] = ($finalResult) ? (($positiveEval) ? 'p' : 'n') : '';

                /* Sum up the statistics.
                   We have three situations, dependent on the value of `$resType':
                   - SB_RES_ANY means we provide output and statisitcs for all students
                   - SB_RES_POSITIVIE means we provide output and statisitcs just for positive students
                   - SB_RES_NEGATIVE means we provide output and statisitcs just for negative students
                    */
                $a1 = (boolean)($resType == SB_STUDENT_ANY);
                $a2 = (boolean)(($positiveEval) && ($resType == SB_STUDENT_POSITIVE));
                $a3 = (boolean)((!$positiveEval) && ($resType == SB_STUDENT_NEGATIVE));

                //$this->dumpVar('=========== any', $a1);
                //$this->dumpVar('pos', $a2);
                //$this->dumpVar('neg', $a3);
                $this->dumpVar('resType', $resType);

                if (($resType == SB_STUDENT_ANY) ||
                    (($positiveEval) && ($resType == SB_STUDENT_POSITIVE)) ||
                    ((!$positiveEval) && ($resType == SB_STUDENT_NEGATIVE))
                )
                {
                    if (!$positiveEval)
                    {
                        $statData['negative'] = $statData['negative'] + 1;
                    }
                    $statData['exmAvg'] = $statData['exmAvg'] + $exmPoints / floatval($numStudents);
                    $statData['average'] = $statData['average'] + $sumPoints / floatval($numStudents);

                    $this->dumpVar('student', $studentData);
                    $this->dumpVar('db value', $val);

                    $studentList[] = $studentData;
                }
            }
        }

        /* Finish computing the statistics. */
        if (isset ($subtaskList))
        {
            foreach ($subtaskList as $sKey => $sVal)
            {
                /* Divisor may be zero */
                $div = $parSubCount[$sKey];
                if ($div > 0)
                    $avgSubtaskData[$sKey] = (float)$avgSubtaskData[$sKey] / (float)$div;
                else
                    $avgSubtaskData[$sKey] = 'n/a';
            }
        }
        if (isset ($taskList))
        {
            foreach ($taskList as $tKey => $tVal)
            {
                /* Divisor may be zero */
                $div = $parTaskCount[$tKey];
                if ($div > 0)
                    $avgTaskData[$tKey] = (float)$avgTaskData[$tKey] / (float)$div;
                else
                    $avgTaskData[$tKey] = 'n/a';
            }
        }

        /* Update the $statData container with some more statistics. */
        $statData['negTaskCount'] = $negTaskCount;
        $statData['avgSubtask'] = $avgSubtaskData;
        $statData['parSubCount'] = $parSubCount;
        $statData['avgTask'] = $avgTaskData;
        $statData['parTaskCount'] = $parTaskCount;
        $statData['count'] = $numStudents;

        /* Sort the list according to encrypted user identifiers or logins. */
        switch ($sortType)
        {
            case SB_SORT_BY_ID:
                usort($studentList, 'studentListIdCmp');
                break;
            case SB_SORT_BY_LOGIN:
                usort($studentList, 'studentListLoginCmp');
                break;
        }

        return array($studentList, $statData);
    }

    /**
     * Fetch data for displaying student results table and store them in Smarty variables.
     * Assigns `studentList` and `statData`.
     * @param $idList
     * @param $points
     * @param $evaluation
     * @param $subtaskMap
     * @param $subtaskList
     * @param $taskList
     * @param int $resType
     * @param int $sortType
     * @param int $lectureId
     */
    function assignStudentDataFromList(
        $idList,
        $points,
        $evaluation,
        $subtaskMap,
        $subtaskList,
        $taskList,
        $resType = SB_STUDENT_ANY,
        $sortType = SB_SORT_BY_NAME,
        $lectureId = 1
    )
    {
        list($studentList, $statData) = $this->getStudentDataFromList(
            $idList, $points, $evaluation, $subtaskMap, $subtaskList, $taskList, $resType, $sortType, $lectureId);
        $this->assign('studentList', $studentList);
        $this->assign('statData', $statData);
    }

    /* ---------------------------------------------------------------------
       Query a list of student records for the given list of ids.
       --------------------------------------------------------------------- */
    function dbQueryStudentIdList($ids)
    {
        $where = " WHERE id IN (" . arrayToDBString($ids) . ")";
        $rs = $this->_getFullList($where);
        return $rs;
    }

    /* ---------------------------------------------------------------------
       Assign a list of student records for the given list of ids.
       --------------------------------------------------------------------- */
    function assignStudentIdList($ids)
    {
        $rs = $this->dbQueryStudentIdList($ids);
        $this->_smarty->assign('studentList', $rs);
        return $rs;
    }

    /* ---------------------------------------------------------------------
       Assign a full list of student records for the given lecture.
       --------------------------------------------------------------------- */
    function assignStudentListForLecture($lectureId = 0)
    {
        $rs = $this->dbQueryStudentListForLecture($lectureId);
        $this->_smarty->assign('studentList', $rs);
        return $rs;
    }

    /* ---------------------------------------------------------------------
       Assign a full list of student records for the given lecture.
       --------------------------------------------------------------------- */
    function dbQueryStudentListForLecture($lectureId = 0)
    {
        $slb = new StudentLectureBean ($lectureId, $this->_smarty, '', '');
        $ids = $slb->getStudentListForLecture();
        $rs = $this->dbQueryStudentIdList($ids);
        return $rs;
    }

    /**
     * Assign a list of students to `studentList`.
     * Dependent on the value of fullList this method published a complete
     * list of students registered in the system or only those students that
     * are registered as students of the lecture given by id in this
     * schoolyear.
     */
    function assignFull()
    {
        if ($this->fullList)
        {
            $rs = $this->_getFullList();
        }
        else
        {
            $rs = $this->dbQueryStudentListForLecture($this->id);
        }
        $this->assign('studentList', $rs);
        return $rs;
    }

    /**
     * Pass the data from internal variable `rs` to Smarty.
     */
    function assign_rs()
    {
        $this->assign('student', $this->rs);
    }

    /* Assign a single student record. */
    function assignSingle()
    {
        /* If id == 0, we shall create a new record. */
        if ($this->id)
        {
            /* Query data of this person. */
            $this->dbQuerySingle();
        }
        else
        {
            /* Initialize default values. */
            $this->_setDefaults();
            /* Find out the first non-standard candidate id, that is, an id lower than 50000. */
            $rs = $this->dbQuery("SELECT max(id)+1 AS id FROM student WHERE id<50000");
            if (!empty($rs))
            {
                $this->id = $this->rs['id'] = $rs[0]['id'];
            }
        }
        $this->assign_rs();
    }

    /**
     * Initialises all internal and template variables using data stored in
     * $this->rs[]. The function expects the contents of $this->rs[] to be
     * initialised already. Main reason for this construct is the process of
     * logging into the application - the `LoginBean` instance calls the
     * password checker provided by this class first and immediately after the
     * process of password verification the information about the user is queried
     * (otherwise the date of last successful login and the number of unsuccessful
     * logins would be lost).
     */
    function doShowWithoutQuery()
    {
        /* The function above sets $this->rs to values that shall be displayed. By assigning $this->rs to Smarty
           variable 'student' we can fill the values of $this->rs into a template. */
        $this->_smarty->assign('student', $this->rs);

        /* Get the lecture id from session. It may be under some circumstances empty. */
        $lectureId = SessionDataBean::getLectureId();
        if (empty ($lectureId))
        {
            /* Lecture not initialised. */
            $this->action = "e_nolecture";
            return;
        }

        /* Verify that the student is listed as studying the actual lecture. */
        $slb = new StudentLectureBean (0, $this->_smarty, NULL, NULL);
        $isListed = $slb->studentIsListed($this->id, $lectureId, $this->schoolyear);

        if (!$isListed)
        {
            /* Not studying this lecture. */
            $this->action = "e_notstudent";
            return;
        }

        /* Check for possible student group mode. */
        if (SessionDataBean::getLectureGroupType() != StudentGroupBean::GRPTYPE_NONE)
        {
            $grpb = new StudentGroupBean(null, $this->_smarty, null, null);
            $data = $grpb->assignGroupAndGroupStudentsOfStudent($this->id);
            /* If the student is not member of any student group, the content of `$data` are two empty lists.
               In that case, fetch the list of free student groups (but only in case that the student is not
               a member, as an attempt to fetch a list when all group places are already taken -- which could
               happen if the student is already a member -- results in an exception). */
            if (empty($data[0]))
            {
                $grpb->assignFreeGroupsList();
            }
        }

        /* Check the replacement exercises. */
        if (SessionDataBean::getLectureReplacements())
        {
            $termLimits = SchoolYearBean::getTermLimits($this->schoolyear, SessionDataBean::getLectureTerm());
            /* This lecture has replacement exercises activated, we will
               display everything related to the current student. */
            $repBookingBean = new ExerciseRepBookingBean ($lectureId, $this->_smarty, NULL, NULL);
            $bookings = $repBookingBean->getBookedReplacements($termLimits);
            $this->_smarty->assign('bookings', $bookings);
            /* Reset the pre-selected labtask group. */
            $repBookingBean->sessionPopLgrpId();
        }

        /* Create a SubtaskBean instance that will be used to fetch
              a list of tasks that shall be displayed. */
        $subtaskBean = new SubtaskBean (0, $this->_smarty, "x", "x");
        /* Get individual and other assignments that shall be
           displayed in an extra table. */
        $stsubtaskList = $subtaskBean->getStudentSubtaskList($lectureId, $this->id);
        $this->dumpVar('stsubtaskListA', $stsubtaskList);

        /* Fetch the evaluation scheme from the database.
           @TODO@ Allow for more than one scheme.
           @TODO@ Allow for more than one lecture !!!!! */
        $evaluationBean = new EvaluationBean ($lectureId, $this->_smarty, NULL, NULL);

        /* This will initialise EvaluationBean with the most recent evaluation
           scheme for lecture given by $this->lecture_id. The function returns
           'true' if the bean has been initialised. */
        $ret = $evaluationBean->initialiseFor($lectureId, $this->schoolyear);

        /* Check the initialisation status. */
        if (!$ret)
        {
            /* Nope, the evaluation of this lecture has not been initialised
               yet. */
            //$this->action = "e_noeval";
            return;
        }

        /* Get the list of tasks for evaluation of this exercise. The list will contain
           only task IDs and we will have to fetch task and subtask information
           by ourselves later. */
        $taskList = $evaluationBean->getTaskList();

        /* Fetch a verbose list of tasks. */
        $taskBean = new TaskBean (0, $this->_smarty, NULL, NULL);

        /* This will both create a full list of tasks corresponding to the
           evaluation scheme and assing this list to the Smarty variable
           'taskList'. */
        $fullTaskList = $taskBean->assignFullTaskList($taskList);

        /* This will both create a full list of subtasks corresponding to the
           tasks of the chosen evaluation scheme and assign this list to the
           Smarty variable 'subtaskList'. */
        $tsb = new TaskSubtasksBean (0, $this->_smarty, NULL, NULL);
        $subtaskMap = $tsb->getSubtaskMapForTaskList($taskList, $this->schoolyear);
        $subtaskList = TaskSubtasksBean::getSubtaskListFromSubtaskMap($subtaskMap);

        $fullSubtaskList = $subtaskBean->assignFullSubtaskList($subtaskList);

        /* Create a PointsBean instance that will be used to fetch
           points for subtasks from subtask list. */
        $pointsBean = new PointsBean (0, $this->_smarty, NULL, NULL);

        /* Get points of all students. */
        $points = $pointsBean->getPoints(
            array($this->id),
            $subtaskList,
            $this->schoolyear // this is probably not correct: SchoolYearBean::getSchoolYearStart()
        );
        $this->dumpVar('student points', $points);

        /* This will pass all the information about points and subtasks to
           Smarty template variables. */
        $this->assignStudentDataFromList(
            array($this->id),
            $points,
            $evaluationBean,
            $subtaskMap,
            $fullSubtaskList,
            $fullTaskList,
            SB_STUDENT_ANY,
            SB_SORT_BY_NAME,
            $lectureId
        );

        /* Get points for this student. */
        $stsubtaskList = $pointsBean->addPointsToSubtaskList(
            $this->id,
            $subtaskList,
            $stsubtaskList);

        /* Verify which assignments have been accomplished by the student. */
        $asgnBean = new AssignmentsBean (0, $this->_smarty, NULL, NULL);
        $stsubtaskList = $asgnBean->setClassifiedAssignments($this->id, $stsubtaskList);

        /* And publish the contents of $subtaskList */
        $this->_smarty->assign('studentSubtaskList', $stsubtaskList);
    }

    /* -------------------------------------------------------------------
       HANDLER: SHOW
       ------------------------------------------------------------------- */
    function doShow()
    {
        /* Check that the student with the given ID may be displayed.
           In general, it means that the current user is the student in question
           or someone with higher privileges. */
        if (LoginBean::canShowStudentId($this->id))
        {
            /* Query data of this student */
            $this->dbQuerySingle();

            /* The rest is taken care of by the following function. */
            $this->doShowWithoutQuery();
        }
        else
        {
            /* This user is not allowed to see private data of this particular
               student. Announce it. */
            $this->action = "error01";
        }
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
        /* If the password is empty or equal to the password mask, do not
           update it. Otherwise update the database record with the new
           password specified by user. */
        $pass = $_POST["pass1"];
        $passCheck = $_POST["pass2"];

        $this->dumpVar("pass", $pass);
        $this->dumpVar("passCheck", $passCheck);

        if (!empty ($pass) && $pass != PASSWORD_MASK)
        {
            /* User has specified something. Although client-side Java
               script should have checked that both passwords are the same,
               let's check it once more. */
            if ($pass == $passCheck)
            {
                $this->dbUpdatePassword($pass);
                $this->sendPassword($pass);
            }
            else
            {
                $this->action = 'error';
            }
        }
        else
        {
            if (empty ($pass))
            {
                $this->action = 'passerror';
            }
        }

        $this->assignSingle();
    }

    /* -------------------------------------------------------------------
       HANDLER: DELETE
       ------------------------------------------------------------------- */
    function doDelete()
    {
        $this->assignSingle();
    }

    /* -------------------------------------------------------------------
       HANDLER: REAL DELETE
       ------------------------------------------------------------------- */
    function doRealDelete()
    {
        $this->assignSingle();
        /* Delete the record */
        DatabaseBean::dbDeleteById();
    }

    /* -------------------------------------------------------------------
       HANDLER: ADMIN
       ------------------------------------------------------------------- */
    function doAdmin()
    {
        /* Check for 'full=true' as a part of URL. */
        $this->processGetVars();
        /* Get the list of all students for this lecture of the list of
           all students in the system. */
        $this->assignFull();
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
        /* Both above functions set $this->rs to values that shall be
           displayed. By assigning $this->rs to Smarty variable 'user'
           we can fill the values of $this->rs into a template. */
        $this->assignSingle();
    }
}

?>
