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
     * Returns the full query
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->getQueryHead().$this->getQueryBody();
    }
}
