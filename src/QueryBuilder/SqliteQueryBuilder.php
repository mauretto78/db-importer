<?php

namespace DbImporter\QueryBuilder;

class SqliteQueryBuilder extends AbstractQueryBuilder
{
    const MULTIPLE_QUERY_IMPORT_LIMIT = 100;

    /**
     * @return string
     */
    private function getQueryHead()
    {
        $sql = 'INSERT ';

        if (true === $this->debug) {
            $sql .= 'OR IGNORE ';
        }

        $sql .= 'INTO `'.$this->table.'` (';
        $c = 1;
        $values = array_keys($this->mapping);

        foreach ($values as $value) {
            $sql .= '`'.$value.'`';
            $sql .= $this->appendComma($c, $values);
            $c++;
        }

        $sql .= ') VALUES ';

        return $sql;
    }

    /**
     * Returns the array of insert queries
     * @param string $mode
     *
     * @return array
     */
    public function getQueries($mode = 'multiple')
    {
        $sql = [];

        switch ($mode) {
            case 'multiple':
                $queries = $this->getMultipleInsertQueriesBody(self::MULTIPLE_QUERY_IMPORT_LIMIT);
                break;

            case 'single':
                $queries = $this->getSingleInsertQueriesBody();
                break;
        }

        foreach ($queries as $query) {
            $sql[] = $this->getQueryHead().$query;
        }

        return $sql;
    }
}
