<?php

namespace DbImporter;

use DbImporter\Collections\DataCollection;
use DbImporter\Exceptions\NotAllowedDriverException;
use DbImporter\QueryBuilder\Contracts\QueryBuilderInterface;
use DbImporter\QueryBuilder\MySqlQueryBuilder;
use DbImporter\QueryBuilder\SqliteQueryBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;

class Importer
{
    /**
     * Allowed drivers
     */
    const ALLOWED_DRIVERS = [
        'pdo_mysql',
        'pdo_sqlite',
    ];

    /**
     * @var Connection $dbal
     */
    private $dbal;

    /**
     * @var string
     */
    private $driver;

    /**
     * @var string
     */
    private $table;

    /**
     * @var bool
     */
    private $skipDuplicates;

    /**
     * @var array
     */
    private $mapping;

    /**
     * @var DataCollection
     */
    private $data;

    /**
     * Importer constructor.
     * @param Connection $dbal
     * @param $table
     * @param $skipDuplicates
     * @param array $mapping
     * @param DataCollection $data
     */
    private function __construct(
        Connection $dbal,
        $table,
        $skipDuplicates,
        array $mapping,
        DataCollection $data
    ) {
        $this->checkDriver($driver = $dbal->getDriver()->getName());
        $this->dbal = $dbal;
        $this->driver = $driver;
        $this->table = $table;
        $this->skipDuplicates = $skipDuplicates;
        $this->mapping = $mapping;
        $this->data = $data;
    }

    /**
     * @param $driver
     * @throws NotAllowedDriverException
     */
    private function checkDriver($driver)
    {
        if (false === in_array($driver, self::ALLOWED_DRIVERS)) {
            throw new NotAllowedDriverException(
                sprintf(
                    'The driver %s is not allowed. Drivers allowed are: [%s]',
                    $driver,
                    implode(',', self::ALLOWED_DRIVERS)
                )
            );
        }
    }

    /**
     * @param Connection $dbal
     * @param $table
     * @param $skipDuplicates
     * @param array $mapping
     * @param DataCollection $data
     * @return Importer
     */
    public static function init(
        Connection $dbal,
        $table,
        $skipDuplicates,
        array $mapping,
        DataCollection $data
    ) {
        return new self(
            $dbal,
            $table,
            $skipDuplicates,
            $mapping,
            $data
        );
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        switch ($this->driver) {
            case 'pdo_mysql':
                $class = MySqlQueryBuilder::class;
                break;

            case 'pdo_sqlite':
                $class = SqliteQueryBuilder::class;
                break;
        }

        /** @var $class QueryBuilderInterface */
        return (new $class(
            $this->table,
            $this->skipDuplicates,
            $this->mapping,
            $this->data
        ))->getQuery();
    }

    /**
     * @return bool
     */
    public function executeQuery()
    {
        $stmt = $this->dbal->prepare($this->getQuery());
        $c = 1;

        foreach ($this->data as $item) {
            $this->bindValuesToItem($item, $c, $stmt);
            $c++;
        }

        return $stmt->execute();
    }

    /**
     * @param $item
     * @param $index
     * @param Statement $stmt
     */
    private function bindValuesToItem($item, $index, Statement $stmt)
    {
        foreach ($item as $key => $value) {
            $map = array_values($this->mapping);

            if (in_array($key, $map)) {
                $key = ':'.$key.'_'.$index;
                $stmt->bindValue($key, $value);
            }
        }
    }
}
