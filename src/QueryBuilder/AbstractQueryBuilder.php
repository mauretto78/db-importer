<?php

namespace DbImporter\QueryBuilder;

use DbImporter\Collections\DataCollection;
use DbImporter\QueryBuilder\Contracts\QueryBuilderInterface;

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
     * @var DataCollection
     */
    protected $data;

    /**
     * MySqlQueryBuilderTest constructor.
     * @param $table
     * @param $debug
     * @param array $mapping
     * @param DataCollection $data
     */
    public function __construct(
        $table,
        $debug,
        array $mapping,
        DataCollection $data
    )
    {
        $this->table = $table;
        $this->debug = $debug;
        $this->mapping = $mapping;
        $this->data = $data;
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
     * @return string
     */
    protected function getQueryBody()
    {
        $sql = '';
        $count = $this->data->count();

        for ($c = 1; $c <= $count; $c++){
            $sql .= '('.$this->getItemPlaceholders($c).')';
            $sql .= $this->appendComma($c, $this->data);
        }

        return $sql;
    }

    /**
     * @param $index
     * @return string
     */
    private function getItemPlaceholders($index)
    {
        $sql = '';
        $c = 1;
        $values = array_values($this->mapping);

        foreach ($values as $map){
            $sql .= ':'.$map.'_'.$index;
            $sql .= $this->appendComma($c, $values);
            $c++;
        }

        return $sql;
    }
}