<?php

namespace DbImporter\QueryBuilder;

class MySqlQueryBuilder extends AbstractQueryBuilder
{
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
     * Returns the full query
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->getQueryHead().$this->getQueryBody().$this->getQueryTail();
    }
}
