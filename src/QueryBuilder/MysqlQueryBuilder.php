<?php
/**
 * This file is part of the DbImporter package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DbImporter\QueryBuilder;

class MysqlQueryBuilder extends AbstractQueryBuilder
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
    public function getInsertQueries($mode = 'multiple')
    {
        $sql = [];

        foreach ($this->getQueriesBody($mode, self::MULTIPLE_QUERY_IMPORT_LIMIT) as $query) {
            $sql[] = $this->getQueryHead().$query.$this->getQueryTail();
        }

        return $sql;
    }

    /**
     * @return string
     */
    public function getClearDataQuery()
    {
        return 'TRUNCATE TABLE `'.$this->table.'`';
    }

    /**
     * @return string
     */
    public function getSchemaDestroyQuery()
    {
        return 'DROP TABLE `'.$this->table.'`';
    }

    /**
     * @return string
     */
    public function getTableExistsQuery()
    {
        return 'SELECT count(*) as c FROM `'.$this->table.'`';
    }
}
