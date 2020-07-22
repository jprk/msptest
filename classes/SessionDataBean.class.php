<?php

class SessionDataBean
{

    /* Define all entries that will be stored in the session. */
    const SDB_LECTURE_DATA = 'lecture';
    const SDB_LAST_SECTION_ID = 'last_section_id';
    const SDB_SCHOOL_YEAR = 'school_year';
    const SDB_USER_DATA = 'user';
    const SDB_FROM_POWER_USER = 'from_power_user';
    const SDB_FLARUM_SSO = 'flarum_sso';

    /**
     * Constructor is empty in this case. This class has only static methods.
     */
    function __construct()
    {
    }

    /**
     * Conditionally initialise some parts of session storage.
     * @param $schoolYearStart
     */
    static function conditionalInit($schoolYearStart)
    {
        /* Check the school year stored in the session. */
        if (!self::hasSchoolYear())
        {
            /* If the school year is empty, set it to the current
               school year. */
            self::setSchoolYear($schoolYearStart);
        }

        /* Check the lecture id stored in the session. */
        if (!self::hasLecture())
        {
            /* Make sure that we have some lecture identifier. The
               default layout has an id==0. */
            self::setDefaultLecture();
        }
    }

    /**
     * Clear the user information record stored in session data block.
     */
    static function clearUserInformation()
    {
        unset ($_SESSION[self::SDB_USER_DATA]);
        self::setUserRole(USR_ANONYMOUS);
    }

    /**
     * Return the array with parameters of the currently selected lecture.
     */
    static function getLecture()
    {
        return $_SESSION[self::SDB_LECTURE_DATA];
    }

    /**
     * Return the identifier of the currently selected lecture.
     */
    static function getLectureId()
    {
        return LectureBean::getId($_SESSION[self::SDB_LECTURE_DATA]);
    }

    /**
     * Return the identifier of the current locale.
     */
    static function getLectureLocale()
    {
        return LectureBean::getLocale($_SESSION[self::SDB_LECTURE_DATA]);
    }

    /**
     * Return the identifier of the term when the lecture is being lectured.
     */
    static function getLectureTerm()
    {
        return LectureBean::getTermFromData($_SESSION[self::SDB_LECTURE_DATA]);
    }

    /**
     * Return the availability of replacement exercises for the current lecture.
     */
    static function getLectureReplacements()
    {
        return LectureBean::getReplacements($_SESSION[self::SDB_LECTURE_DATA]);
    }

    /**
     * Return the group mode flag for the current lecture.
     */
    static function getLectureGroupType()
    {
        return LectureBean::getGroupType($_SESSION[self::SDB_LECTURE_DATA]);
    }

    /**
     * Return the deadline grace period for the current lecture.
     */
    static function getLectureGraceMinutes()
    {
        return LectureBean::getGraceMinutes($_SESSION[self::SDB_LECTURE_DATA]);
    }

    /**
     * Return the identifier of the root section of the currently selected lecture.
     */
    static function getRootSection()
    {
        return LectureBean::getRootSectionFromData($_SESSION[self::SDB_LECTURE_DATA]);
    }

    /**
     * Return the code of the currently selected lecture.
     */
    static function getCode()
    {
        return LectureBean::getCodeFromData($_SESSION[self::SDB_LECTURE_DATA]);
    }

    /**
     * Return the user role number.
     */
    static function getUserRole()
    {
        return UserBean::getRole($_SESSION[self::SDB_USER_DATA]);
    }

    /**
     * Return the user's login.
     */
    static function getUserLogin()
    {
        return UserBean::getLogin($_SESSION[self::SDB_USER_DATA]);
    }

    /**
     * Return the user's login.
     */
    static function getUserFullName()
    {
        return UserBean::getFullName($_SESSION[self::SDB_USER_DATA]);
    }

    /**
     * Return the user's system identifier.
     */
    static function getUserId()
    {
        return UserBean::getId($_SESSION[self::SDB_USER_DATA]);
    }

    /**
     * Get the last visited section identifier.
     */
    static function getLastSectionId()
    {
        if (array_key_exists(self::SDB_LAST_SECTION_ID, $_SESSION))
            return $_SESSION[self::SDB_LAST_SECTION_ID];
        else
            return NULL;
    }

    /**
     * Return the current school year.
     */
    static function getSchoolYear()
    {
        return $_SESSION[self::SDB_SCHOOL_YEAR];
    }

    /**
     * Return the Flarum SSO object holding the discussion forum SSO session cookie.
     * @return Flarum Flarum SSO object.
     */
    static function getFlarumSSOObject()
    {
        return $_SESSION[self::SDB_FLARUM_SSO];
    }

    /**
     * Return true if the current school year has been set.
     */
    static function hasSchoolYear()
    {
        return isset ($_SESSION[self::SDB_SCHOOL_YEAR]);
    }

    /**
     * Return true if the current lecture has been set.
     */
    static function hasLecture()
    {
        return isset ($_SESSION[self::SDB_LECTURE_DATA]);
    }

    /**
     * Return true if we have a valid user data record.
     */
    static function hasUserData()
    {
        return isset ($_SESSION[self::SDB_USER_DATA]);
    }

    /**
     * Set the array with parameters of the currently selected lecture.
     * @param $lectureBean LectureBean Lecture description and configuration object.
     */
    static function setLecture(&$lectureBean)
    {
        $_SESSION[self::SDB_LECTURE_DATA] = $lectureBean->getLectureData();
    }

    /**
     * Directly set the array with parameters of the currently selected lecture.
     * @param array $lectureData Associative array representing the lecture data.
     */
    static function setLectureData(&$lectureData)
    {
        $_SESSION[self::SDB_LECTURE_DATA] = $lectureData;
    }

    /**
     * Set or update the user role.
     * @param $role
     */
    static function setUserRole($role)
    {
        UserBean::setRole($_SESSION[self::SDB_USER_DATA], $role);
        UserBean::setLogin($_SESSION[self::SDB_USER_DATA], 'anonymní');
    }

    /**
     * Update user data provided by login bean.
     * @param $loginBean LoginBean
     */
    static function setUserInformation($loginBean)
    {
        $_SESSION[self::SDB_USER_DATA] = $loginBean->rs;
    }

    /**
     * Set the lecture id to be empty.
     */
    static function setDefaultLecture()
    {
        $fakeSmartyInstance = NULL;
        $lectureBean = new LectureBean (NULL, $fakeSmartyInstance, NULL, NULL);
        $lectureBean->_setDefaults();
        self::setLecture($lectureBean);
    }

    /**
     * Set the last visited section identifier.
     * @param $lastSectionId int
     */
    static function setLastSectionId($lastSectionId)
    {
        $_SESSION[self::SDB_LAST_SECTION_ID] = $lastSectionId;
    }

    /**
     * Set the current school year.
     * The value is the year of the beginning of the current school year,
     * i.e. 2008 for 2008/2009.
     * @param $schoolYear
     */
    static function setSchoolYear($schoolYear)
    {
        $_SESSION[self::SDB_SCHOOL_YEAR] = $schoolYear;
    }

    /**
     * Store the Flarum SSO object holding the discussion forum SSO session cookie.
     * @param $flarum Flarum
     */
    static function setFlarumSSOObject(&$flarum)
    {
        $_SESSION[self::SDB_FLARUM_SSO] = $flarum;
    }

?>