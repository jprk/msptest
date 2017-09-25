<?php

// load helper functions
require('tools.php');

// load Smarty library
require('smarty3/Smarty.class.php');

use Tracy\Debugger;

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
    private $_currencies;
    private $_submenu;
    private $_yesno;
    private $_dayMap;
    private $_yearMap;
    private $_locale;
    private $_config;  // Smarty3 does not have this

    private $connection;

    public $debug;

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
     */
    function __construct($config, $isPageOutput)
    {
        /* Call parent constructor first. */
        parent::__construct();

        /* Database layer is not initialised yet. */
        $this->connection = null;

        /* Set default locale. This is just an information for our plugins,
           it does not change the locale of the system.
           @todo check if the locale can be changed for the complete smarty
                 as well, it would influence also date formatting etc. */
        $this->_locale = 'cs';

        /* Set the application directories. Value of `APP_BASE_DIR`
           is defined in configuration file. */
        $this->setTemplateDir(APP_BASE_DIR . DIRECTORY_SEPARATOR . 'templates');
        $this->setCompileDir(APP_BASE_DIR . DIRECTORY_SEPARATOR . 'templates_c');
        $this->setConfigDir(APP_BASE_DIR . DIRECTORY_SEPARATOR . 'configs');
        $this->setCacheDir(APP_BASE_DIR . DIRECTORY_SEPARATOR . 'cache');

        /* We will place (and look for) plugins into a subdirectory
           of directory where class files are located. */
        $this->addPluginsDir(REQUIRE_DIR . DIRECTORY_SEPARATOR . 'plugins');

        $this->_yesno = array(0 => '&nbsp;ne', 1 => '&nbsp;ano');
        $this->_dayMap = self::_assignDayMap();
        $this->_yearMap = self::_assignYearMap();

        $this->assign('app_name', 'LectwebSmarty');
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

        /* Get remote address. If the request came from certain computer,
           switch on the debugging. */
        $ra = $_SERVER['REMOTE_ADDR'];
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
            /* Enable Tracy debugger, https://tracy.nette.org/ */
            Debugger::enable();
        }
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
     * Initialise database connection.
     * Internally creates a Dibi\Connection() instance.
     * @throws Exception
     */
    function db_connect()
    {
        /* Open connection to the database server. */
        $options = $this->_config['dibi'];
        $options['charset'] = 'utf8';
        try
        {
            dibi::connect($options);
        }
        catch (Exception $e)
        {
            /* Log the error and rethrow it. */
            $error = "<p>Cannot connect to database as <tt>'" .
                $options['username'] . "@" . $options['host'] . "'</tt></p>\n" .
                "<p>Error message: " . $e->getMessage() . "</p>";
            logSystemError($error);
            throw $e;
        }
    }

    /**
     * Disconnect from database.
     */
    function db_disconnect()
    {
        if (dibi::isConnected()) dibi::disconnect();
    }

    function db_log($time_start, $object, $action)
    {
        /* Remove sensitive information from POST data. */
        $post_copy = $_POST;
        if (array_key_exists('password', $post_copy))
        {
            $post_copy['password'] = '*****';
        }

        /* Prepare insert elements */
        $data = [
            'timestamp' => null,
            'time_start' => $time_start,
            'lecture_id' => SessionDataBean::getLectureId(),
            'user_id' => SessionDataBean::getUserId(),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'object' => $object,
            'action' => $action,
            'get_data' => json_encode($_GET),
            'post_data' => json_encode($post_copy)
        ];

        dibi::query('INSERT INTO log', $data);
    }

    function db_log_exception($time_start, $message)
    {
        /* Prepare insert elements */
        $data = [
            'timestamp' => null,
            'time_start' => $time_start,
            'lecture_id' => SessionDataBean::getLectureId(),
            'user_id' => SessionDataBean::getUserId(),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'object' => 'error',
            'action' => 'exception',
            'get_data' => '',
            'post_data' => $message
        ];

        dibi::query('INSERT INTO log', $data);
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
        $result = dibi::query($query);

        /* Is the result a meaningful `resource` or did an error occur? */
        if (!$result)
        {
            $error = "<p>Invalid query: <tt>" . mysql_error() . "</tt></p>\n";
            $error .= "<p>Query string: <tt>" . $query . "</tt></p>\n";
            logSystemError($error);
            throw new Exception ('Neplatný SQL dotaz.');
        }

        /* Allocate an array for the query result. */
        $asr = array();

        /* If dbQuery was used to update some information, the Dibi will return the number of
           affected rows. This is irrelevant. */
        if (!is_int($result))
        {
            /* If normal indexing has been requested, copy the returned rows exactly
             * in the order they have been returned by the database. */
            if ($idx === null)
            {
                $asr = $result->fetchAll();
            }
            else
            {
                /* Assume that every fetched row contains a field with name given by
                 * $idx and use the value of that field as an index. */
                $asr = $result->fetchAssoc($idx);
            }

            /* Release Dibi resources. */
            unset($result);
        }

        return $asr;
    }

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

        $result = dibi::query($query);

        if ($this->debug) {
            echo '<!-- ';
            print_r($result);
            echo "-->\n";
        }

        /* Is the result a meaningful `resource` or did an error occur? */
        if (!$result)
        {
            $error = "<p>Invalid query: <tt>" . mysql_error() . "</tt></p>\n";
            $error .= "<p>Query string: <tt>" . $query . "</tt></p>\n";
            logSystemError($error);
            throw new Exception ('Neplatný SQL dotaz.');
        }

        $row = $result->fetch();

        if ($this->debug) {
            echo '<!-- ';
            print_r($row);
            echo "-->\n";
        }

        if (!$row)
        {
            $row = null;
        }

        unset($result);

        return $row;
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
        return array(NEWS_SECTION => "Novinka k sekci",
            NEWS_ARTICLE => "Novinka ke �l�nku");
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

    function assignSettings()
    {
        /* Get settings data */
        $settings = $this->dbQuerySingle('SELECT * FROM settings WHERE Id=1');
        $this->assign('settings', $settings);
        return $settings;
    }

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
