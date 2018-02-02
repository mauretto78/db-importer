<?php

namespace DbImporter\QueryBuilder\Contracts;

interface  QueryBuilderInterface
{
    /**
     * Returns the full query
     *
     * @return string
     */
    public function getQuery();
}
