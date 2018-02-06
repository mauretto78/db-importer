<?php

namespace DbImporter\Collections;

class DataCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var \ArrayObject
     */
    private $items = [];

    /**
     * @param $item
     */
    public function addItem($item)
    {
        $this->items[] = $item;
    }

    /**
     * @param array $items
     */
    public function addItems(array $items)
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    /**
     * @param $index
     */
    public function removeItem($index)
    {
        unset($this->items[$index]);
    }

    /**
     * @param $index
     * @return mixed
     */
    public function getItem($index)
    {
        return $this->items[$index];
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return (array)$this->items;
    }
}
