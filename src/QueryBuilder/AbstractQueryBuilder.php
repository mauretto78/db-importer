<?php

namespace DbImporter\QueryBuilder;

use DbImporter\Collections\DataCollection;
use DbImporter\QueryBuilder\Contracts\QueryBuilderInterface;
use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractQueryBuilder implements QueryBuilderInterface
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var ArrayCollection
     */
    protected $data;

    /**
     * MySqlQueryBuilderTest constructor.
     * @param $table
     * @param $debug
     * @param array $mapping
     * @param array $data
     */
    public function __construct(
        $table,
        array $mapping,
        array $data,
        $debug
    ) {
        $this->table = $table;
        $this->mapping = $mapping;
        $this->data = $data;
        $this->debug = $debug;
    }

    /**
     * @param $index
     * @param $array
     * @return string
     */
    protected function appendComma($index, $array)
    {
        if ($index < (count($array))) {
            return  ', ';
        }
    }

    /**
     * @return array
     */
    protected function getMultipleInsertQueriesBody($limit)
    {
        $sql = [];
        $data = array_chunk($this->data, $limit, true);

        foreach ($data as $array) {
            $count = count($array);
            $string = '';

            for ($c = 1; $c <= $count; $c++) {
                $string .= '('.$this->getItemPlaceholders($c).')';
                $string .= $this->appendComma($c, $array);
            }

            $sql[] = $string;
        }

        return $sql;
    }

    /**
     * @return array
     */
    protected function getSingleInsertQueriesBody()
    {
        $sql = [];
        $count = count($this->data);

        for ($c = 1; $c <= $count; $c++) {
            $sql[] = '('.$this->getItemPlaceholders().')';
        }

        return $sql;
    }

    /**
     * @param $index
     * @return string
     */
    private function getItemPlaceholders($index = null)
    {
        $sql = '';
        $c = 1;
        $values = array_values($this->mapping);

        foreach ($values as $map) {
            $sql .= ($index) ? ':'.$map.'_'.$index : ':'.$map;
            $sql .= $this->appendComma($c, $values);
            $c++;
        }

        return $sql;
    }
}
