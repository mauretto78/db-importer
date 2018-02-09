<?php

namespace DbImporter;

use DbImporter\Exceptions\NotAllowedDriverException;
use DbImporter\Exceptions\NotAllowedModeException;
use DbImporter\Exceptions\NotIterableDataException;
use DbImporter\QueryBuilder\Contracts\QueryBuilderInterface;
use DbImporter\QueryBuilder\MySqlQueryBuilder;
use DbImporter\QueryBuilder\SqliteQueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Yaml;

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
     * Allowed insert modes
     */
    const ALLOWED_INSERT_MODES = [
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
     * @var ArrayCollection
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
     * @param array $data
     * @param $skipDuplicates
     * @param string $mode
     */
    private function __construct(
        Connection $dbal,
        $table,
        $mapping,
        $data,
        $skipDuplicates,
        $mode = 'multiple'
    ) {
        $this->checkDriver($driver = $dbal->getDriver()->getName());
        $this->checkMode($mode);
        $this->dbal = $dbal;
        $this->driver = $driver;
        $this->table = $table;
        $this->mapping = $mapping;
        $this->data = $this->serialize($data);
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
                    'The driver '.$driver.' is not allowed. Drivers allowed are: [%s]',
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
        if (false === in_array($mode, self::ALLOWED_INSERT_MODES)) {
            throw new NotAllowedModeException(
                sprintf(
                    'The mode '.$mode.' is not allowed. Drivers allowed are: [%s]',
                    implode(',', self::ALLOWED_INSERT_MODES)
                )
            );
        }
    }

    /**
     * @param $data
     * @return array
     * @throws NotIterableDataException
     */
    private function serialize($data)
    {
        if (false === is_iterable($data)) {
            throw new NotIterableDataException('Data is not iterable');
        }

        $serializer = new Serializer(
            [new ObjectNormalizer()],
            [new YamlEncoder()]
        );

        return Yaml::parse($serializer->serialize($data, 'yaml'));
    }

    /**
     * @param Connection $dbal
     * @param $table
     * @param $mapping
     * @param $data
     * @param $skipDuplicates
     * @param string $mode
     * @return Importer
     */
    public static function init(
        Connection $dbal,
        $table,
        $mapping,
        $data,
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
     * @return array
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
        if ($this->mode == 'single') {
            return $this->executeSingleInsertQueries();
        }

        return $this->executeMultipleInsertQuery();
    }

    /**
     * @return bool
     */
    private function executeSingleInsertQueries()
    {
        $queries = $this->getQueries();
        $c = 0;

        foreach ($queries as $query) {
            $stmt = $this->dbal->prepare($query);
            $this->bindValuesToItem($this->data[$c], $stmt);
            $c++;

            if (false === $stmt->execute()) {
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

        foreach ($queries as $query) {
            $stmt = $this->dbal->prepare($query);
            $c = 1;
            $dataSliced = array_slice($this->data, ($start*$limit), $limit);

            foreach ($dataSliced as $item) {
                $this->bindValuesToItem($item, $stmt, $c);
                $c++;
            }

            if (false === $stmt->execute()) {
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
