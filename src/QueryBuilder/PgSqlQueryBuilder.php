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

        foreach ($this->getQueriesBody($mode) as $query) {
            $sqlString = $this->getQueryHead().$query;

            if (true === $this->debug) {
                $sqlString .= $this->getQueryTail();
            }

            $sql[] = $sqlString;
        }

        return $sql;
    }
}
