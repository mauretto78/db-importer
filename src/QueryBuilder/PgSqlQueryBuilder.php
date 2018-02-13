<?php

namespace DbImporter\QueryBuilder;

class PgSqlQueryBuilder extends AbstractQueryBuilder
{
    const MULTIPLE_QUERY_IMPORT_LIMIT = 4000;

    /**
     * @return string
     */
    private function getQueryHead()
    {
        $sql = 'INSERT ';

        $sql .= 'INTO '.$this->table.' (';
        $c = 1;
        $values = array_keys($this->mapping);

        foreach ($values as $value) {
            $sql .= $value.$this->appendComma($c, $values);
            $c++;
        }

        $sql .= ') VALUES ';

        return $sql;
    }

    /**
     * @return string
     */
    private function getQueryTail()
    {
        return ' ON CONFLICT DO NOTHING';
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

            $sqlString = $this->getQueryHead().$query;

            if (true === $this->debug) {
                $sqlString .= $this->getQueryTail();
            }

            $sql[] = $sqlString;
        }

        return $sql;
    }
}
