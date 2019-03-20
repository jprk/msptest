<?php

/**
 * Created by PhpStorm.
 * User: prikryl
 * Date: 5.10.2016
 * Time: 14:30
 */
class ExerciseTutorsBean extends DatabaseBean
{
    function __construct($id, $smarty, $action, $object)
    {
        parent::__construct($id, $smarty, 'exer_tutors', $action, $object);
    }

    function fetchTutorsForExercises(&$exercise_rs)
    {
        /* Convert the exercise dictionary to a list of exercise ids. */
        $exercise_id_list = array2ToDBString($exercise_rs, 'id');

        $rs = $this->dbQuery(
            "SELECT et.exercise_id,le.* FROM exer_tutors AS et "
            . "LEFT JOIN lecturer AS le ON et.tutor_id=le.id "
            . "WHERE et.exercise_id IN ($exercise_id_list) "
            . "ORDER BY et.order,le.surname,le.firstname"
        );

        $exer_tutors = array();
        foreach ($exercise_rs as $exercise)
        {
            $exer_tutors[$exercise['id']] = array();
        }

        if (!empty($rs))
        {
            foreach ($rs as $tutor)
            {
                $exer_id = $tutor['exercise_id'];
                $exer_tutors[$exer_id][] = $tutor;
            }
        }

        $this->dumpVar('tutors for exercises:', $exer_tutors);
        return $exer_tutors;
    }


    function addToExercises(&$exercise_rs)
    {
        $tutors = $this->fetchTutorsForExercises($exercise_rs);

        foreach ($exercise_rs as $key => $val)
        {
            $exercise_rs[$key]['tutors'] = $tutors[$val['id']];
        }
        return $exercise_rs;
    }

    /**
     * Return an array of tutor ids for the given exercise.
     * @param $exercise_id int Identifier of the exercise.
     * @return array An array of tutor id values.
     */
    function getTutorsIdsForExercise($exercise_id)
    {
        /* Note that the school year is part of exercise definition, therefore the check for tutor ids only checks
           against the exercise id. */
        $rs = DatabaseBean::dbQuery(
            "SELECT tutor_id FROM exer_tutors" .
            " WHERE exercise_id=$exercise_id"
        );

        $this->dumpVar('tutor id array', $rs);
        $tutor_ids = array();
        if (!empty($rs))
        {
            foreach($rs as $val)
            {
                $tutor_ids[] = $val['tutor_id'];
            }
        }
        return $tutor_ids;
    }

    /**
     * Set tutors for the given exercise.
     * If the list of tutor is empty, it will only delete existing tutors from an exercise.
     * TODO: Implement ordering so that the first tutor is tne person responsible for the given exercise.
     * @param $tutors array Array of tutor identifiers. May be empty.
     * @param $exercise_id int Identifier of the exercise.
     */
    function setTutorsIdsForExercise($tutors, $exercise_id)
    {
        /* Remove everything related to this exercise from the mapping table. */
        DatabaseBean::dbQuery(
            "DELETE FROM exer_tutors WHERE exercise_id=$exercise_id"
        );
        $ids_to_insert = "";
        $comma = "";
        foreach ($tutors as $tutor)
        {
            $ids_to_insert .= $comma . "($exercise_id,$tutor,0)";
            $comma = ",";
        }
        $this->dumpVar('ids_to_insert', $ids_to_insert);
        /* Add tutors given by the tutor list. */
        if (!empty($ids_to_insert))
        {
            DatabaseBean::dbQuery(
                "INSERT INTO exer_tutors VALUES $ids_to_insert"
            );
        }
    }
}