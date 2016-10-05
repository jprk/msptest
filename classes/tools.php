<?php
// -----------------------------------------------------------------------
//   tools.php
//
//   Utility functions that do not deserve their own class.
// -----------------------------------------------------------------------

// Remove HTML entities.
function unhtmlentities($string)
{
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    $trans_tbl = array_flip($trans_tbl);
    return strtr($string, $trans_tbl);
}

// Add nonbreakable spaces after some Czech prepositions.
function vlnka($text)
{
    return preg_replace('/(\s[AIiUuKkSsVvOoZz])\s+(\w+)/', '$1&nbsp;$2', $text);
}

// Remove special HTML entities, convert all unallowed HTML characters into
// corresponding HTML entities and add nonbreakable spaces after some Czech
// prepositions.
function vlnkahtml($text)
{
    // Remove special HTML entities (&nbsp; is crucial for us)
    // PHP > 4.3.0 has a library function for this, but we are running 4.1.2
    $text = unhtmlentities($text);
    // Mask unallowed characters (&,<,>) as HTML entities
    $text = htmlspecialchars($text);
    return vlnka($text);
}

/* Remove all HTML tags (or subset defined by $entities) from the text in
   $field. */
function trimStrip($field, $entities = "")
{
    if ($entities == "")
    {
        return trim(strip_tags($field));
    }
    else
    {
        return trim(strip_tags($field, $entities));
    }
}

/* Determine MIME type of the file represented by $filename first by trying
   PHP internal mime_content_type() function and then by checking our own
   list of filename extensions.
   TODO: Call mime_content_type or finfo after using the harcoded extension-based type assigment. We have problems
   with new MS Office format being identified as application/zip.
*/
function mimetype($filename)
{
    $type = '';

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (function_exists("mime_content_type"))
    {
        $type = mime_content_type($filename);
    }

    if ($type == '' || $type == 'unknown/unknown' || $type == 'application/zip')
    {
        switch ($ext)
        {
            case "pdf" :
                $type = "application/pdf";
                break;
            case "doc" :
                $type = "application/vnd.ms-word";
                break;
            case "xls" :
                $type = "application/vnd.ms-excel";
                break;
            case "ppt" :
                $type = "application/vnd.ms-powerpoint";
                break;
            case "bmp" :
                $type = "image/bmp";
                break;
            case "gif" :
                $type = "image/gif";
                break;
            case "png" :
                $type = "image/x-png";
                break;
            case "jpg" :
                $type = "image/jpeg";
                break;
            case "dwg" :
                $type = "application/dwg";
                break;
            case "mdl" :
                $type = "application/vnd.matlab";
                break;
            /* See http://stackoverflow.com/questions/4212861/ for the following */
            case "xlsx" :
                $type = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
                break;
            case "xltx" :
                $type = "application/vnd.openxmlformats-officedocument.spreadsheetml.template";
                break;
            case "potx" :
                $type = "application/vnd.openxmlformats-officedocument.presentationml.template";
                break;
            case "ppsx" :
                $type = "application/vnd.openxmlformats-officedocument.presentationml.slideshow";
                break;
            case "pptx" :
                $type = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
                break;
            case "sldx" :
                $type = "application/vnd.openxmlformats-officedocument.presentationml.slide";
                break;
            case "docx" :
                $type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
                break;
            case "dotx" :
                $type = "application/vnd.openxmlformats-officedocument.wordprocessingml.template";
                break;
            case "xlam" :
                $type = "application/vnd.ms-excel.addin.macroEnabled.12";
                break;
            case "xlsb" :
                $type = "application/vnd.ms-excel.sheet.binary.macroEnabled.12";
                break;
            default:
                $type = "unknown/unknown";
        }
    }

    if ($ext == "mdl")
    {
        $type = "application/vnd.matlab";
    }

    return $type;
}

/* Assign POST value to the specified variable. Call trimStrip() if
   required. */
function assignPostIfExists(&$var, &$rs, $postKey, $doTrim = false, $entities = "")
{
    if (isset ($_POST[$postKey]))
    {
        $postVal = $_POST[$postKey];
        if ($doTrim)
        {
            $postVal = trimStrip($postVal, $entities);
        }
        $var = $rs[$postKey] = $postVal;
    }
}

/* Assign an existing HTTP GET value to the specified variable. Call
   trimStrip() if required, optionally with a list of allowed entities. */
function assignGetIfExists(&$var, &$rs, $getKey, $doTrim = false, $entities = "", $defaultValue = "")
{
    if (isset ($_GET[$getKey]))
    {
        $getVal = $_GET[$getKey];
        if ($doTrim)
        {
            $getVal = trimStrip($getVal, $entities);
        }
        $var = $rs[$getKey] = $getVal;
    }
    else
    {
        if (!empty ($defaultValue))
        {
            $var = $rs[$getKey] = $defaultValue;
        }
    }
}

/**
 * Convert an array of entities into a comma-separated list of characters.
 *
 * This function will transform an array into a comma-separated list. It is
 * means to be used for transformation of integer `id` lists into a set that
 * can be used in SQL WHERE `id` IN(...) clause. No checking is done to verify
 * the type of operands.
 *
 * @param array $list One-dimensional list of numeric elements.
 * @param bool $defRet Whether the default value of 0 shall be added to the
 *                       returned list. In case that the $list is empty, this
 *                       assures that the returned string may be safely used
 *                       as a parameter of IN(...) without further checking,
 *                       as 0 is not a valid `id`.
 * @return string String containing a comma-separated list of elements.
 */
function arrayToDBString(&$list, $defRet = true)
{
    /* Default return value */
    $ret = $defRet ? "0" : "";
    if (!empty ($list))
    {
        $strList = implode($list, ",");
        if ($defRet) $ret = $ret . ",";
        $ret = $ret . $strList;
    }

    return $ret;
}

/**
 * Convert a two-level dictionary into a comma-separated list of elements.
 *
 * This function will transform a two level list of entities indexed by
 * $index at the second level into a string containing a comma-separated list
 * of characters. It is meant to be used for transformation of integer `id`
 * lists into a set that can be used in SQL WHERE `id` IN(...) clause.
 * No checking is done to verify the type of operands.
 *
 * @param array $list Two-dimensional list of elements.
 * @param mixed $index Index of the `id` element in the second level.
 * @param bool $defRet
 * @return string String containing a comma-separated list of `id` values.
 */
function array2ToDBString(&$list, $index, $defRet = true)
{
    /* Default return value */
    $ret = $defRet ? "0" : "";
    if (!empty ($list))
    {
        $firstVal = true;
        foreach ($list as $val)
        {
            $ret = ($firstVal && !$defRet) ? $val[$index] : $ret . "," . $val[$index];
            $firstVal = false;
        }
    }

    return $ret;
}

/**
 * Transform resultset array into an array indexed by `id`.
 *
 * @todo Could be migrated directly into initial processing of SQL queries.
 *
 * @param array $rs Resultset array.
 * @param mixed $index Index of the `id` element inside an element of $rs.
 * @return array Resultset indexed by the content of $index.
 */
function resultsetToIndexKey(&$rs, $index)
{
    $ret = array();
    if (!empty ($rs))
    {
        foreach ($rs as $val)
        {
            $ret[$val[$index]] = $val;
        }
    }
    return $ret;
}

/**
 * Get textual description of a day in a week for odd-week, even-week and every-week actions.
 * TODO: Better have an array (initialised from XML for different languages) for this.
 * @param $num int Number id of the given day of the week.
 * @return string
 */

function numToDayString($num)
{
    switch ($num)
    {
        case  1:
            $name = "pondělí";
            break;
        case  2:
            $name = "úterý";
            break;
        case  3:
            $name = "středa";
            break;
        case  4:
            $name = "čtvrtek";
            break;
        case  5:
            $name = "pátek";
            break;
        case  6:
            $name = "sobota";
            break;
        case  7:
            $name = "neděle";
            break;
        case 11:
            $name = "liché pondělí";
            break;
        case 12:
            $name = "liché úterý";
            break;
        case 13:
            $name = "lichá středa";
            break;
        case 14:
            $name = "lichý čtvrtek";
            break;
        case 15:
            $name = "lichý pátek";
            break;
        case 16:
            $name = "lichá sobota";
            break;
        case 17:
            $name = "lichá neděle";
            break;
        case 21:
            $name = "sudé pondělí";
            break;
        case 22:
            $name = "sudé úterý";
            break;
        case 23:
            $name = "sudá středa";
            break;
        case 24:
            $name = "sudý čtvrtek";
            break;
        case 25:
            $name = "sudý pátek";
            break;
        case 26:
            $name = "sudá sobota";
            break;
        case 27:
            $name = "sudá neděle";
            break;
        default:
            $name = "?????";
    }

    return $name;
}

/**
 * Convert day enumerator as defined by CPPSmarty::_assignDayMap() to time offset string.
 *
 * Used to determine all dates of exercises during a schoolyear term.
 *
 * @param int $date Date of the Monday when the event sequence begins.
 * @param int $num Day of occurrence of the event.
 * @return array Offsets Time shift string needed for
 */
function daynumToOffsets($date, $num)
{
    /* Is the first week odd or even? If the week is even, $firstWeek will
       be 0, otherwise it will be 1. */
    $firstWeek = intval(date('W', $date)) % 2;

    /* The $date is usually a Monday, but not always. We have to account for that.
       We will use `0` for Monday and `6` for Sunday. */
    $dateWeekdayOffset = (intval(date('w', $date)) + 6) % 7;

    /* Some events occur every week (days represented by 1 to 5), some event occur
       on odd (11-15) or even (21-25) weeks. */
    $eventSpacing = ($num < 10) ? 7 : 14;

    /* Offset of the first occurrence of the event in days. Zero offset means that
       the event occurs right on $date. Offset greater than 6 is used for events
       that occur every odd (11-15) or even (21-25) week, dependent
       on which week has stared on $date. */
    $eventWeekdayOffset = ($num - 1) % 10;
    /* Now we have to compute the number of days since $date when the first event
       that starts on $eventWeekdayOffset. */
    $weekdayOffset = $eventWeekdayOffset - $dateWeekdayOffset;
    /* In rare cases when $dateWeekdayOffset is not `0` (i.e. Monday) the value of
       $weekdayOffset may be negative. If this happens, we will have to move the
       first occurrence by 7 or 14 days. */
    if ($weekdayOffset < 0) $weekdayOffset += $eventSpacing;

    /* The odd/even week event have to be further manipulated in order to account
       for $date representing an odd or event week number. */
    if ($eventSpacing == 14)
    {
        /* The event occurs on odd or even weeks, we have to modify the
           value of $weekdayOffset taking into account also $firstWeek. */
        if (($firstWeek == 1 && $num > 20) ||
            ($firstWeek == 0 && $num > 10 && $num < 20)
        )
        {
            /* The event occurs first time in the second week from $date. */
            $weekdayOffset += 7;
        }
    }

    return array(
        'offset' => $weekdayOffset,
        'spacing' => $eventSpacing);
}

function numToDay($num)
{
    $nd = array();
    $nd['num'] = $num;
    $nd['name'] = numToDayString($num);

    return $nd;
}

function boolToYesNo($val)
{
    return $val ? "ano" : "ne";
}

function dumpToString($mixed = null)
{
    ob_start();
    var_dump($mixed);
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}

/**
 * Convert Czech date format 'DD.MM.YYYY HH:MM:SS' into classical SQL   format 'YYYY-MM-DD HH:MM:SS'.
 * Time portion of the dateTime is optional. DateTime is supposed to be trimmed.
 * @param string $dateTime
 * @param string $defaultTimePart
 * @return string
 */
function czechToSQLDateTime($dateTime, $defaultTimePart = '')
{
    /* Default date. */
    $date = '0000-00-00 00:00:00';

    if (strlen($dateTime) > 0)
    {
        $dateArray = explode('.', $dateTime);

        $year = $dateArray[2];
        $yearArray = explode(' ', $year);

        $date = $yearArray[0] . '-' . $dateArray[1] . '-' . $dateArray[0];

        if (count($yearArray) > 1)
        {
            $date = $date . ' ' . $yearArray[1];
        }
        else
        {
            $date = $date . ' ' . $defaultTimePart;
        }
    }

    return $date;
}

/**
 * Convert an interger timestamp into SQL datetime.
 *
 * @param $timestamp int Integer timestamp.
 * @return string SQL datetime string "YYYY-MM-DD HH:MM:SS"
 */
function timestampToSQL($timestamp)
{
    if (isset ($timestamp))
        return strftime('%Y-%m-%d %H:%M:%S', $timestamp);
    else
        return NULL;
}

/**
 * Given the timestamp, return another timestamp corresponding to the
 * last second of a Sunday of the preceding week.
 * @param $timestamp int PHP timestamp value.
 * @return int Timestamp of the end of the previous week.
 */
function previousWeekEnd($timestamp)
{
    $a = getdate($timestamp);
    return mktime(23, 59, 59, $a['mon'], $a['mday'] - $a['wday'] + 1);
}

/* Returns timestamp of the end of the current term. */
function termEnd()
{
    /* Timestamp of the end of this term. */
    $eTime = 0;

    /* Month beginnings. Winter term starts in the week that
       contains 1.10., summer term in the first week that
       contains 1.3. But maybe the algorithm is different, this
       is just a guess. */
    $mar01 = mktime(0, 0, 0, 3, 1);
    $oct01 = mktime(0, 0, 0, 10, 1);

    /* These are ends of winter and summer terms: Winter term
       ends the last Sunday before summer term starts and vice
       versa. */
    $wEnd = previousWeekEnd($mar01);
    $sEnd = previousWeekEnd($oct01);

    /* This is the current time. */
    $cTime = time();

    /* Now find out which part of the shool year we have today. */
    if ($cTime <= $wEnd)
    {
        /* Winter term of the school year that ends in this year. */
        $eTime = $wEnd;
    }
    elseif ($cTime <= $sEnd)
    {
        /* Summer term. */
        $eTime = $sEnd;
    }
    else
    {
        /* Winter term of the school year that started in this
           year. We have to find out when this term ends. */
        $cDate = getdate($cTime);
        $mar01ny = mktime(0, 0, 0, 3, 1, $cDate['year'] + 1);
        $eTime = previousWeekEnd($mar01ny);
    }

    return $eTime;
}

/* Return year in which the current term started. */
function yearOfTermStart()
{
    /* This is the current time. */
    $cTime = time();
    $cDate = getdate($cTime);

    /* The crucial point is the 1st of March of this year.
     Anything that started before the March 1st needs
     the youer number to be decreased by one.  */
    $mar01 = mktime(0, 0, 0, 3, 1);
    $wEnd = previousWeekEnd($mar01);

    /* Now find if we have passed the date or not. */
    if ($cTime < $wEnd)
    {
        /* Winter term. */
        $tYear = $cDate['year'] - 1;
    }
    else
    {
        /* Summer term. */
        $tYear = $cDate['year'];
    }
    return $tYear;
}

/* Return the year where the schoolyear started. */
function schoolYearStart()
{
    /* This is the current time. */
    $cTime = time();
    $cDate = getdate($cTime);

    /* Up to 2008/2009 we start the year on Monday of the week containing
     October 1. So if we call this function before Monday of that particular
     week, we shall return the current year decreased by one. */
    $oct01 = mktime(0, 0, 0, 10, 1);
    $wEnd = previousWeekEnd($oct01);

    /* Now find if we have passed the date or not. */
    if ($cTime < $wEnd)
    {
        /* We are still in the schoolyear that ends this year. */
        $tYear = $cDate['year'] - 1;
    }
    else
    {
        /* We are already in the schoolyear that started this year. */
        $tYear = $cDate['year'];
    }
    return $tYear;
}

/* Return a boolean identifier of the role. */
function isRole($roleId)
{
    return ((integer)SessionDataBean::getUserRole() == (integer)$roleId) ? TRUE : FALSE;
}

/**
 * Return `true` if the current user has logged in.
 * Logged-in users have some role assigned and the role is not USR_ANONYMOUS.
 */
function isLoggedIn()
{
    /* Role may be also empty. */
    return (SessionDataBean::getUserRole() !== USR_ANONYMOUS);
}

/**
 * Return a default value if the first parameter is an empty string.
 * Used to give a meaningful value to student responses.
 * @param string $var Parameter that shall be subject of default if empty
 * @param string $default Default value to return
 * @return string Updated value of $var
 */
function returnDefault($var, $default = '0')
{
    $var = strtr($var, ',', '.');
    return (trim($var) === '' ? $default : $var);
}

/**
 * aryel at iku dot com dot br
 * 29-Jan-2008 11:40
 * http://cz2.php.net/manual/en/function.debug-backtrace.php
 */
function getDebugBacktrace($stackTrace = NULL, $pfx = "<li>", $sfx = "</li>\n")
{
    $dbgMsg = '';
    $dbgMsgList = getDebugBacktraceList($stackTrace);
    foreach ($dbgMsgList as $dbgInfo)
    {
        $dbgMsg .= $pfx . $dbgInfo . $sfx;
    }
    /* Return the backtrace. */
    return $dbgMsg;
}

/**
 * Return debug backtrace as an array of strings.
 * Modified from:
 * aryel at iku dot com dot br
 * 29-Jan-2008 11:40
 * http://cz2.php.net/manual/en/function.debug-backtrace.php
 */
function getDebugBacktraceList($dbgTrace = NULL)
{
    /* If user did not supply their own stack trace (for example, the backtrace
     * associated with an exception), fetch it here. */
    if ($dbgTrace === NULL)
    {
        $dbgTrace = debug_backtrace();
    }
    $dbgList = array();
    foreach ($dbgTrace as $dbgIndex => $dbgInfo)
    {
        /* As $dbgInfo['args'] could be an array, we have to preprocess
           it to a string. */
        $args = array();
        foreach ($dbgInfo['args'] as $key => $val)
        {
            $args[$key] = print_r($val, true);
        }
        $dbgList[] = "at " . $dbgInfo['file'] . " (line {$dbgInfo['line']}) -> {$dbgInfo['function']}(" . join(",", $args) . ")";
    }
    /* Return the backtrace list. */
    return $dbgList;
}

/**
 * Log an error into the webserver error log and send an e-mail.
 */
function logSystemError($errorMsgHTML, $stackTrace = NULL)
{
    /* Get the backtrace in HTML and plain text format. */
    $dbgTraceHTML = getDebugBacktrace($stackTrace);
    $dbgTraceList = getDebugBacktraceList($stackTrace);

    /* Get the lecture data. */
    $lecData = SessionDataBean::getLecture();
    $lecCode = $lecData['code'];

    /* Get the user login and role. */
    $login = SessionDataBean::getUserLogin();
    $role = UserBean::getRoleName(SessionDataBean::getUserRole());

    /* Log the text version of the log the error log. It will occupy several
       log lines so we will distinguish the information by using the request
       time as the first part of the information.*/
    $timestamp = $_SERVER["REQUEST_TIME"];
    foreach ($dbgTraceList as $stackElem)
    {
        error_log("[$timestamp] " . $stackElem, 0);
    }
    error_log("[$timestamp] lecture=$lecCode", 0);
    error_log("[$timestamp] user=$login, role=$role", 0);

    /* Send an error e-mail in HTML form as well. */
    $errorMsgHTML = "<html>\n<body>\n" . $errorMsgHTML;
    if (!empty ($dbgTraceList))
    {
        $errorMsgHTML .= "<p>Stack trace:</p>";
        $errorMsgHTML .= "<ol>\n";
        $errorMsgHTML .= $dbgTraceHTML;
        $errorMsgHTML .= "</ol>\n";
    }
    else
    {
        $errorMsgHTML .= "<p>No stack trace available for this error.</p>";
    }
    $errorMsgHTML .= "<p>Lecture: <tt>" . $lecCode . "</tt><br/>\n";
    $errorMsgHTML .= "User login: <tt>" . $login . "</tt><br/>\n";
    $errorMsgHTML .= "User role: <tt>" . $role . "</tt></p>\n";
    $errorMsgHTML .= "Request URI: <tt>" . $_SERVER['REQUEST_URI'] . "</tt></p>\n";
    $errorMsgHTML .= "</body>\n</html>\n";
    error_log($errorMsgHTML, 1, ADMIN_EMAIL, 'Content-type: text/html; charset=utf-8');
}

/* -------------------------------------------------------------------------
 *  MUTEX CODE 
 * ------------------------------------------------------------------------- */

/** Define return codes of mutex routines. */
define('MUTEX_OK', 0);
define('MUTEX_E_CANTACQUIRE', -1);
define('MUTEX_E_ISLOCKED', -2);
define('MUTEX_E_FTOK', -3);
define('MUTEX_LOCK_STOLEN_OK', 1);

/**
 * Get the lock file name.
 * The parameters are in fact arbitrary, but keeping proper values of $className and $resourceId
 * will help you in identifying what and why has been locked.
 * @param $className string Class that the lock will be acquired for.
 * @param $resourceId string Resource within the class that shall be locked.
 * @return string Lock file name.
 */
function lockFileName($className, $resourceId)
{
    return '/tmp/_' . $className . '.' . $resourceId . '.lock';

}

/**
 * Lock the access to a resource.
 * Uses a rather complicated non-blocking mutex construct requiring a
 * semaphore and a temporary file.
 */
function mutexLock($class, $resourceId, &$lockTime, &$lockLogin)
{
    /* Get the name of the class. */
    $className = get_class($class);

    /* Get the name of the class file. */
    $classFile = REQUIRE_DIR . $className . '.class.php';

    /* Construct a semaphore id. */
    $semId = ftok($classFile, $resourceId);
    if ($semId < 0)
    {
        /* Call to ftok() failed. Return with error. */
        return MUTEX_E_FTOK;
    }

    /* Get the semaphore. */
    $semaphore = sem_get($semId);

    /* Unconditional blocking wait for the semaphore. */
    if (sem_acquire($semaphore))
    {
        /* Create the name of a temporary file that will be used to implement
           the lock. */
        $lockFile = lockFileName($className, $resourceId);
        //echo "<!-- lock file: " . $lockFile . " -->\n";

        /* Timestamp of the lock file. */
        $lockTime = @filemtime($lockFile);

        /* Check if the file exists and if the lock is not stale. */
        if (file_exists($lockFile) && ((time() - $lockTime) < 1800))
        {
            /* Read the login of the user that owns the lock. It is
               stored in the file. */
            $fs = fopen($lockFile, 'r');
            $lockLogin = fgets($fs);
            fclose($fs);

            /* Compare it to the name of the current user. If they are
               the same, let the user continue editing. */
            if (strcmp($lockLogin, SessionDataBean::getUserLogin()))
            {
                /* Different logins, the resource is locked. */
                $ret = MUTEX_E_ISLOCKED;
            }
            else
            {
                /* Same logins, update the modification time of the lock
                   file and allow access to the resource. */
                @touch($lockFile);
                $ret = MUTEX_OK;
            }
        }
        else
        {
            /* The resource is not locked at all or the lock is stale. */
            if (file_exists($lockFile))
            {
                /* Read the login of the user that owns the lock. It is
                   stored in the file. */
                $fs = fopen($lockFile, 'r');
                $lockLogin = fgets($fs);
                fclose($fs);

                $ret = MUTEX_LOCK_STOLEN_OK;
            }
            else
            {
                $ret = MUTEX_OK;
            }

            /* Write the login of the user that own the lock into the
               lock file. */
            $fs = fopen($lockFile, 'w');
            fputs($fs, SessionDataBean::getUserLogin());
            fclose($fs);
        }

        /* And finally release the semaphore so that other threads may check
           the existence of the lock file. */
        sem_release($semaphore);
    }
    else
    {
        /* We could not acquire the semaphore. */
        $ret = MUTEX_E_CANTACQUIRE;
    }

    return $ret;
}

/**
 * Unlock the shared resource is locked.
 * Uses a rather complicated non-blocking mutex construct requiring a
 * semaphore and a temporary file.
 */
function mutexUnlock($class, $resourceId)
{
    /* Get the name of the class. */
    $className = get_class($class);

    /* Get the name of the class file. */
    $classFile = REQUIRE_DIR . $className . '.class.php';

    /* Construct a semaphore id. */
    $semId = ftok($classFile, $resourceId);
    if ($semId < 0)
    {
        /* Call to ftok() failed. Return with error. */
        return MUTEX_E_FTOK;
    }

    /* Get the semaphore. */
    $semaphore = sem_get($semId);

    /* Unconditional blocking wait for the semaphore. */
    if (sem_acquire($semaphore))
    {
        /* Create the name of a temporary file that will be used to implement
           the lock. */
        $lockFile = lockFileName($className, $resourceId);
        //echo "<!-- unlock file: " . $lockFile . " -->\n";

        /* Release the lock. */
        @unlink($lockFile);

        /* Return success. */
        $ret = MUTEX_OK;

        /* And finally release the semaphore so that other threads may check
           the existence of the lock file. */
        sem_release($semaphore);
    }
    else
    {
        /* We could not acquire the semaphore. */
        $ret = MUTEX_E_CANTACQUIRE;
    }

    return $ret;
}

?>
