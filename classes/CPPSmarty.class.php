<?php

// load helper functions
require('tools.php');

// load Smarty library
require('smarty3/Smarty.class.php');

/* News types */
define('NEWS_SECTION', 1);
define('NEWS_ARTICLE', 2);

define('IMG_SIZE', 190);

/**
 * Modified Smarty class for LectWeb.
 * @method void assign(string $smartyVariable, mixed $data) Assigns data to Smarty variable.
 */
class LectwebSmarty extends Smarty
{
    private $_config;
    private $_currencies;
    //private $_submenu;
    private $_yesno;
    private $_dayMap;
    private $_yearMap;
    private $_locale;
    private $_link;

    public $debug;
    public $upload_max_filesize;
    public $post_max_size;

    /* Create mapping array for HTML <select> containing year as a key and school year
       identification as a value. */
    static function _assignYearMap()
    {
        /* Get the current year. */
        $date = getdate();

        $yearMap = array();
        foreach (range(MIN_YEAR, $date['year'] + 1) as $year)
        {
            $nextYear = $year + 1;
            $yearMap[$year] = $year . '/' . $nextYear;
        }

        return $yearMap;
    }

    /* Create mapping for working days. */
    static function _assignDayMap()
    {
        $days = array(1, 2, 3, 4, 5, 11, 12, 13, 14, 15, 21, 22, 23, 24, 25);
        $dayMap = array();
        foreach ($days as $day)
        {
            $dayMap[$day] = numToDayString($day);
        }
        return $dayMap;
    }

    /**
     * Class constructor
     * @param $config
     * @param $isPageOutput
     */
    function __construct($config, $isPageOutput)
    {
        /* Call parent constructor first. */
        parent::__construct();

        /* Set default locale. This is just an information for our plugins,
           it does not change the locale of the system.
           @todo check if the locale can be changed for the complete smarty
                 as well, it would influence also date formatting etc. */
        $this->_locale = 'cs';

        /* Set the application directories. Value of `APP_BASE_DIR`
           is defined in configuration file. */
        $this->setTemplateDir(APP_BASE_DIR . '/templates');
        $this->setCompileDir(APP_BASE_DIR . '/templates_c');
        $this->setConfigDir(APP_BASE_DIR . '/configs');
        $this->setCacheDir(APP_BASE_DIR . '/cache');

        /* We will place (and look for) plugins into a subdirectory
           of directory where class files are located. */
        $this->addPluginsDir(REQUIRE_DIR . '/plugins');

        $this->_yesno = array(0 => '&nbsp;ne', 1 => '&nbsp;ano');
        $this->_dayMap = self::_assignDayMap();
        $this->_yearMap = self::_assignYearMap();

        $this->assign('app_name', 'CPPSmarty');
        $this->assign('yesno', $this->_yesno);
        $this->assign('daySelect', $this->_dayMap);
        $this->assign('yearSelect', $this->_yearMap);
        $this->assign('basedir', BASE_DIR);
        $this->assign('BASE_DIR', BASE_DIR);
        $this->assign('global_alert', GLOBAL_ALERT_FILE);

        $this->_config = $config;
        $this->compile_check = $this->_config['compile_check'];
        $this->use_sub_dirs = $this->_config['use_sub_dirs'];
        $this->caching = false;

        /* No database connection. */
        $this->_link = null;

        /* Get remote address. If the request came from certain computer,
           switch on the debugging. */
        $ra = $_SERVER['REMOTE_ADDR'];
        // echo "`$ra`";
        $this->debug = ($isPageOutput) ? in_array($ra, $this->_config['debug_hosts']) : false;
        $this->assign('debugmode', $this->debug);

        /* Hardcoded lecture id for cases where no lecture id is read from the
           database. */
        //$lecture = array ( 'id' => '1', 'code' => 'K611MSAP' );
        //$this->assign ( 'lecture', $lecture );

        /* More strict error checking and reporting in case of debugging session. */
        if ($this->debug)
        {
            ini_set('display_errors', 1);
            error_reporting(E_ALL | E_STRICT);
        }

        /* File upload handler needs information about maximum file upload size and
           maximum post size. */
        $this->upload_max_filesize = ini_get_bytes('upload_max_filesize');
        $this->post_max_size = ini_get_bytes('post_max_size');
    }

    /**
     * Return the configuration record for the given key.
     * @param $key string The requested configuration key
     * @return mixed The configuration value corresponding to the key
     */
    function getConfig($key)
    {
        return $this->_config[$key];
    }

    function displayLocale($locale)
    {
        return ($this->_locale == $locale);
    }

    /**
     * @return mysqli
     * @throws Exception
     */
    function dbOpen()
    {
        /* Open connection to the database server. */
        $db = $this->_config['db'];
        $link = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['data'], 3307);
        if (!$link)
        {
            $error = "<p>Cannot connect to mySQL as <tt>'" .
                $db['user'] . "@" . $db['host'] . "'</tt></p>" . PHP_EOL;
            $error .= "<p>Debugging errno: " . mysqli_connect_errno() . "</p>" . PHP_EOL;
            $error .= "<p>Debugging error: " . mysqli_connect_error() . "</p>" . PHP_EOL;
            echo $error;
            logSystemError($error);
            throw new Exception ('Nelze se připojit k databázovému serveru.');
        }

        /* Support for UTF-8 data exchange. */
        $res = mysqli_query($link, "SET NAMES utf8");
        if (!$res)
        {
            $error = "<p>Cannot set charset to utf8: <tt>" . mysqli_error($link) .
                "</tt></p>\n";
            logSystemError($error);
            throw new Exception ('Nelze zvolit znakovou sadu pro komunikaci s databází.');
        }

        $this->_link = $link;
        // TODO: Check whether we shall return something or not.
        return $link;
    }

    function dbClose($link)
    {
        if ($link) mysqli_close($link);
    }

    /**
     * Create a legal SQL string that you can use in an SQL statement.
     * The given string is encoded to an escaped SQL string, taking into account
     * the current character set of the connection. Just a wrapper around
     * mysqli_real_escape_string().
     *
     * @param $escape_str string String to be escaped.
     * @return string Escaped string.
     */
    function dbEscape($escape_str)
    {
        return mysqli_real_escape_string($this->_link, $escape_str);
    }

    function dbLog($time_start, $object, $action, $internal_post = null)
    {
        /* Allow for specifying an array that will be stored as POST. */
        if (is_null($internal_post))
        {
            $internal_post = $_POST;
        }

        /* Do not store password information */
        if ($object == 'login' && $action == 'verify')
        {
            $internal_post['password'] = '**********';
        }
        $user_id = SessionDataBean::getUserId();
        $lecture_id = SessionDataBean::getLectureId();
        $get_data = $this->dbEscape(json_encode($_GET));
        $post_data = $this->dbEscape(json_encode($internal_post));
        $ip_address = $this->dbEscape($_SERVER['REMOTE_ADDR']);

        $this->dbQuery(
            "INSERT INTO log " .
            "(`timestamp`,time_start,lecture_id,user_id,ip_address,object,action,get_data,post_data) " .
            "VALUES " .
            "(NULL,'$time_start','$lecture_id','$user_id','$ip_address','$object','$action','$get_data','$post_data')");
    }

    function dbLogException($time_start, $message)
    {
        $user_id = SessionDataBean::getUserId();
        $lecture_id = SessionDataBean::getLectureId();
        $ip_address = $this->dbEscape($_SERVER['REMOTE_ADDR']);
        $message = $this->dbEscape($message);

        $this->dbQuery(
            "INSERT INTO log " .
            "(`timestamp`,time_start,lecture_id,user_id,ip_address,object,action,get_data,post_data) " .
            "VALUES " .
            "(NULL,'$time_start','$lecture_id','$user_id','$ip_address','error','exception','','$message')");
    }

    /**
     * Process a database query with possible custom field as an index.
     * Enter description here ...
     * @param string $query The query string.
     * @param string $idx The index in case of custom indexing.
     * @return array Array of associative arrays holding result set data.
     * @throws Exception In case of an invalid SQL query.
     */
    function dbQuery($query, $idx = null)
    {
        if ($this->debug)
        {
            $bt = debug_backtrace();
            array_shift($bt);
            $caller = array_shift($bt);
            $ucaller = array_shift($bt);
            echo "<!-- " . $caller['file'] . ":" . $caller['line'] . " in `" . $ucaller['function'] . "()`\n";
            echo "     dbQuery():'" . $query . "' -->\n";
        }
        $result = mysqli_query($this->_link, $query);

        /* Is the result a meaningful `resource` or did an error occur? */
        if (!$result)
        {
            $error = "<p>Invalid query: <tt>" . mysqli_error($this->_link) . "</tt></p>\n";
            $error .= "<p>Query string: <tt>" . $query . "</tt></p>\n";
            // echo $error;
            logSystemError($error);
            throw new Exception ('Neplatný SQL dotaz.');
        }

        /* Allocate an array for the query result. */
        $asr = array();

        /* If dbQuery was used to update some information, the result is irrelevant. */
        if (!is_bool($result))
        {
            /* If normal indexing has been requested, copy the returned rows exactly
             * in the order they have been returned by the database. */
            if ($idx === null)
            {
                while ($row = $result->fetch_assoc())
                {
                    $asr[] = $row;
                }
            }
            else
            {
                /* Assume that every fetched row contains a field with name given by
                 * $idx and use the value of that field as an index. */
                while ($row = $result->fetch_assoc())
                {
                    $asr[$row[$idx]] = $row;
                }
            }

            $result->free();
        }

        return $asr;
    }

    /**
     * @param $query
     * @return array|null
     * @throws Exception
     */
    function dbQuerySingle($query)
    {
        if ($this->debug)
        {
            $bt = debug_backtrace();
            array_shift($bt);
            $caller = array_shift($bt);
            $ucaller = array_shift($bt);
            echo "<!-- " . $caller['file'] . ":" . $caller['line'] . " in `" . $ucaller['function'] . "()`\n";
            echo "     dbQuerySingle():'" . $query . "' -->\n";
        }
        $result = mysqli_query($this->_link, $query);

        /* Is the result a meaningful `resource` or did an error occur? */
        if (!$result)
        {
            $error = "<p>Invalid query: <tt>" . mysqli_error($this->_link) . "</tt></p>\n";
            $error .= "<p>Query string: <tt>" . $query . "</tt></p>\n";
            logSystemError($error);
            throw new Exception ('Neplatný SQL dotaz.');
        }

        if (!($row = $result->fetch_assoc()))
        {
            $row = NULL;
        }

        $result->free();

        return $row;
    }

    /**
     * Return id of the last insert operation.
     * Wrapper around mysqli_insert_id.
     * @return int|string
     */
    function dbInsertId()
    {
        return mysqli_insert_id($this->_link);
    }

    function dbQueryMenuHier($parentId)
    {
        $resultset = $this->dbQuery("SELECT * FROM section WHERE parent='" . $parentId . "' ORDER BY position,title");

        if (isset ($resultset))
        {
            foreach ($resultset as $key => $val)
            {
                $resultset[$key]['children'] = $this->dbQueryMenuHier($val['Id']);
            }
        }

        return $resultset;
    }

    /**
     * @return array
     * @throws Exception
     */
    function dbQueryArticleIdSet()
    {
        $resultset = $this->dbQuery("SELECT Id, title FROM articles ORDER BY title");

        $idSet = array();

        if (isset ($resultset))
        {
            foreach ($resultset as $key => $val)
            {
                $idSet[$val['Id']] = stripslashes($val['title']);
            }
        }

        return $idSet;
    }

    function getNewsTypes()
    {
        return array(
            NEWS_SECTION => "Novinka k sekci",
            NEWS_ARTICLE => "Novinka ke článku");
    }

    function getNewsTypeString($newsTypeId)
    {
        switch ($newsTypeId)
        {
            case NEWS_SECTION:
                return "section";
            case NEWS_ARTICLE:
                return "article";
        }
        return "error";
    }

    function getCurrencies()
    {
        return $this->_currencies;
    }

    function getCurrencyString($currencyId)
    {
        return $this->_currencies[$currencyId];
    }

    function icon($filename)
    {
        $type = 'bin';

        $ext = strtolower(substr($filename, -3));
        switch ($ext)
        {
            case "rtf" :
                $ext = "doc";
            case "doc" :
            case "xls" :
            case "ppt" :
            case "pdf" :
                $type = $ext;
            default :
        }

        return $type;
    }

    /**
     * @return array|null
     * @throws Exception
     */
    function assignSettings()
    {
        /* Get settings data */
        $settings = $this->dbQuerySingle('SELECT * FROM settings WHERE Id=1');
        $this->assign('settings', $settings);
        return $settings;
    }

    /**
     * @param $intId
     * @param $fileType
     * @throws Exception
     */
    function assignFileList($intId, $fileType)
    {
        $resultset = $this->dbQuery('SELECT * FROM files WHERE parent=' . $intId . ' AND type=' . $fileType . ' ORDER BY position,description');
        if (!empty ($resultset))
        {
            foreach ($resultset as $key => $val)
            {
                $resultset[$key]['description'] = stripslashes($val['description']);
                $resultset[$key]['icon'] = $this->icon($val['filename']);
            }
            $this->assign('fileList', $resultset);
        }
    }

    function getArticleFiles($intId)
    {
        $resultset = $this->dbQuery('SELECT * FROM files WHERE parent=' . $intId . ' AND type=' . FT_A_DATA . ' ORDER BY position,description');
        if (!empty ($resultset))
        {
            foreach ($resultset as $key => $val)
            {
                $resultset[$key]['description'] = stripslashes($val['description']);
                $resultset[$key]['icon'] = $this->icon($val['filename']);
            }
        }

        return $resultset;
    }

    function assignSectionArticles($smartyVar)
    {
        /* Get all sections */
        $sectionSet = $this->dbQuerySectionIdSet();

        $output = array();
        foreach ($sectionSet as $key => $val)
        {
            $resultset = $this->dbQuery('SELECT Id,title FROM articles WHERE parent=' . $key . ' ORDER BY position,title');
            if (!empty ($resultset))
            {
                foreach ($resultset as $rkey => $rval)
                {
                    $resultset[$rkey]['title'] = stripslashes($rval['title']);
                }

                $r['sname'] = $val;
                $r['articles'] = $resultset;
                $output[] = $r;
            }
        }

        $this->assign($smartyVar, $output);
        return $output;
    }

    function setLocale($localeString)
    {
        $this->_locale = $localeString;
    }
}

?>
