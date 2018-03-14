<?php
/**
 * This file is part of the DbImporter package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DbImporter\QueryBuilder\Contracts;

interface QueryBuilderInterface
{
    /**
     * Default multiple query import limit
     * Override this value in concrete implementation
     */
    const MULTIPLE_QUERY_IMPORT_LIMIT = 1000;

    /**
     * Returns the array of insert queries
     * @param string $mode
     *
     * @return array
     */
    public function getInsertQueries($mode = 'multiple');

    /**
     * @return string
     */
    public function getClearDataQuery();

    /**
     * @return string
     */
    public function getSchemaDestroyQuery();

    /**
     * @return string
     */
    public function getTableExistsQuery();
}
