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

        foreach ($this->getQueriesBody($mode) as $query) {
            $sql[] = $this->getQueryHead().$query;
        }

        return $sql;
    }
}
