<?php

namespace DbImporter\QueryBuilder\Contracts;

interface QueryBuilderInterface
{
    /**
     * Default multiple query import limit
     * Override this value in concrete implementation
     */
    const MULTIPLE_QUERY_IMPORT_LIMIT = 1000;

    /**
     * Returns the array of insert queries
     * @param string $mode
     *
     * @return array
     */
    public function getQueries($mode = 'multiple');
}
