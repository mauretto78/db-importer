<?php

namespace DbImporter\QueryBuilder;

class MySqlQueryBuilder extends AbstractQueryBuilder
{
    const MULTIPLE_QUERY_IMPORT_LIMIT = 4000;

    /**
     * @return string
     */
    private function getQueryHead()
    {
        $sql = 'INSERT ';

        if (true === $this->debug) {
            $sql .= 'IGNORE ';
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
     * @return string
     */
    private function getQueryTail()
    {
        $sql = ' ON DUPLICATE KEY UPDATE ';
        $c = 1;
        $values = array_keys($this->mapping);

        foreach ($values as $value) {
            $sql .= '`'.$value.'`=VALUES('.$value.')';
            $sql .= $this->appendComma($c, $values);
            $c++;
        }

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
            $sql[] = $this->getQueryHead().$query.$this->getQueryTail();
        }

        return $sql;
    }
}
