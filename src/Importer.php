<?php
/**
 * This file is part of the DbImporter package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DbImporter;

use DbImporter\Exceptions\NotAllowedDriverException;
use DbImporter\Exceptions\NotAllowedModeException;
use DbImporter\Exceptions\NotIterableDataException;
use DbImporter\Normalizer\StdClassNormalizer;
use DbImporter\QueryBuilder\Contracts\QueryBuilderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Statement;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Importer
{
    /**
     * Allowed drivers
     */
    const ALLOWED_DRIVERS = [
        'pdo_mysql',
        'pdo_pgsql',
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
    private $ignoreErrors;

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
     * @var QueryBuilderInterface
     */
    private $qb;

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
        $this->data = $this->normalize($data);
        $this->ignoreErrors = $skipDuplicates;
        $this->mode = $mode;

        $queryBuilder = $this->getQueryBuilder();
        $this->qb = (new $queryBuilder(
            $this->table,
            $this->mapping,
            $this->data,
            $this->ignoreErrors
        ));
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
    private function normalize($data)
    {
        if (false === is_iterable($data)) {
            throw new NotIterableDataException('Data is not iterable');
        }

        $serializer = new Serializer([
            new StdClassNormalizer(),
            new ObjectNormalizer()
        ]);

        return $serializer->normalize($data);
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
     * truncate data in the table
     */
    public function clearData()
    {
        $this->dbal->executeQuery($this->qb->getClearDataQuery());
    }

    /**
     * @param array $tableKeys
     * @param array|null $uniqueKeys
     * @param array|null $indexKeys
     */
    public function createSchema(
        array $tableKeys,
        array $uniqueKeys = null,
        array $indexKeys = null
    ) {
        $schema = new Schema();

        if (false === $this->checkIfTableExists()) {
            $table = $schema->createTable($this->table);

            foreach ($tableKeys as $key => $type) {
                $table->addColumn($key, $type);
            }

            if ($uniqueKeys) {
                $table->setPrimaryKey($uniqueKeys);
            }

            if ($indexKeys) {
                $table->addIndex($indexKeys);
            }

            $platform = $this->dbal->getDatabasePlatform();
            $queries = $schema->toSql($platform);

            foreach ($queries as $query) {
                $this->dbal->executeQuery($query);
            }
        }
    }

    /**
     * @return bool
     */
    private function checkIfTableExists()
    {
        try {
            $this->dbal->executeQuery($this->qb->getTableExistsQuery());
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * destroy database schema
     */
    public function destroySchema()
    {
        $this->dbal->executeQuery($this->qb->getSchemaDestroyQuery());
    }

    /**
     * @return array
     */
    public function getQueries()
    {
        return ($this->qb->getInsertQueries($this->mode));
    }

    /**
     * @return string
     */
    private function getQueryBuilder()
    {
        return 'DbImporter\\QueryBuilder\\'.ucfirst(str_replace('pdo_','', $this->driver)).'QueryBuilder';
    }

    /**
     * @return bool
     */
    public function execute()
    {
        if ($this->mode == 'single') {
            return $this->executeSingleInsertQueries();
        }

        return $this->executeMultipleInsertQueries();
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
    private function executeMultipleInsertQueries()
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
