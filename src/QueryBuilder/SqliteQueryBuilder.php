<?php

namespace DbImporter\QueryBuilder;

class SqliteQueryBuilder extends AbstractQueryBuilder
{
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
     * @return string
     */
    public function getMultipleInsertQuery()
    {
        return $this->getQueryHead().$this->getMultipleInsertQueryBody();
    }

    /**
     * @return array
     */
    public function getSingleInsertQueries()
    {
        $sql = [];
        $queries = $this->getSingleInsertQueriesBody();

        foreach ($queries as $query){
            $sql[] = $this->getQueryHead().$query;
        }

        return $sql ;
    }
}
