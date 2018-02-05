<?php

namespace DbImporter\QueryBuilder\Contracts;

interface QueryBuilderInterface
{
    /**
     * Returns the full multiple insert query
     *
     * @return string
     */
    public function getMultipleInsertQuery();

    /**
     * Returns the array of single insert query
     *
     * @return array
     */
    public function getSingleInsertQueries();
}
