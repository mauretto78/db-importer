<?php

namespace DbImporter;

use DbImporter\Collections\DataCollection;
use DbImporter\Exceptions\NotAllowedDriverException;
use DbImporter\Exceptions\NotAllowedModeException;
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
     * Allowed modes
     */
    const ALLOWED_MODES = [
        'single',
        'multiple',
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
     * @var string
     */
    private $mode;

    /**
     * Importer constructor.
     * @param Connection $dbal
     * @param $table
     * @param array $mapping
     * @param DataCollection $data
     * @param $skipDuplicates
     * @param string $mode
     */
    private function __construct(
        Connection $dbal,
        $table,
        array $mapping,
        DataCollection $data,
        $skipDuplicates,
        $mode = 'multiple'
    ) {
        $this->checkDriver($driver = $dbal->getDriver()->getName());
        $this->checkMode($mode);
        $this->dbal = $dbal;
        $this->driver = $driver;
        $this->table = $table;
        $this->mapping = $mapping;
        $this->data = $data;
        $this->skipDuplicates = $skipDuplicates;
        $this->mode = $mode;
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
     * @param $mode
     * @throws NotAllowedModeException
     */
    private function checkMode($mode)
    {
        if (false === in_array($mode, self::ALLOWED_MODES)) {
            throw new NotAllowedModeException(
                sprintf(
                    'The mode %s is not allowed. Drivers allowed are: [%s]',
                    $mode,
                    implode(',', self::ALLOWED_MODES)
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
        array $mapping,
        DataCollection $data,
        $skipDuplicates,
        $mode = 'multiple'
    ) {
        return new self(
            $dbal,
            $table,
            $mapping,
            $data,
            $skipDuplicates,
            $mode
        );
    }

    /**
     * @return string
     */
    public function getQueries()
    {
        $queryBuilder = $this->getQueryBuilder();

        return (new $queryBuilder(
            $this->table,
            $this->mapping,
            $this->data,
            $this->skipDuplicates
        ))->getQueries($this->mode);
    }

    /**
     * @return string
     */
    private function getQueryBuilder()
    {
        switch ($this->driver) {
            case 'pdo_mysql':
                return MySqlQueryBuilder::class;

            case 'pdo_sqlite':
                return SqliteQueryBuilder::class;
        }
    }

    /**
     * @return bool
     */
    public function execute()
    {
        switch ($this->mode){
            case 'single':
                return $this->executeSingleInsertQueries();
            case 'multiple':
                return $this->executeMultipleInsertQuery();
        }
    }

    /**
     * @return bool
     */
    private function executeSingleInsertQueries()
    {
        $queries = $this->getQueries();
        $c = 0;

        foreach ($queries as $query){
            $stmt = $this->dbal->prepare($query);
            $this->bindValuesToItem($this->data->getItem($c), $stmt);
            $c++;

            if(false === $stmt->execute()){
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function executeMultipleInsertQuery()
    {
        $queries = $this->getQueries();

        /** @var QueryBuilderInterface $queryBuilder */
        $queryBuilder = $this->getQueryBuilder();
        $limit = $queryBuilder::MULTIPLE_QUERY_IMPORT_LIMIT;
        $start = 0;

        foreach ($queries as $query){
            $stmt = $this->dbal->prepare($query);
            $c = 1;
            $dataSliced = array_slice($this->data->toArray(), ($start*$limit), $limit);

            foreach ($dataSliced as $item) {
                $this->bindValuesToItem($item, $stmt, $c);
                $c++;
            }

            if(false === $stmt->execute()){
                return false;
            }

            $start++;
        }

        return true;
    }

    /**
     * @param $item
     * @param $index
     * @param Statement $stmt
     */
    private function bindValuesToItem($item, Statement $stmt, $index = null)
    {
        foreach ($item as $key => $value) {
            $map = array_values($this->mapping);

            if (in_array($key, $map)) {
                $key = ($index) ? ':'.$key.'_'.$index : $key;
                $stmt->bindValue($key, $value);
            }
        }
    }
}
