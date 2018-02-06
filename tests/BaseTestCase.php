<?php

namespace DbImporter\Tests;

use DbImporter\Collections\DataCollection;
use DbImporter\Importer;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    /**
     * @param Connection $connection
     */
    protected function createSchema(
        Connection $connection,
        $tableName,
        array $keys,
        array $uniqueKeys = null
    )
    {
        $schema = new Schema();

        if (false === $this->checkIfTableExists($connection, $tableName)) {
            $table = $schema->createTable($tableName);

            foreach ($keys as $key => $type) {
                $table->addColumn($key, $type);
            }

            if($uniqueKeys) {
                $table->setPrimaryKey($uniqueKeys);
            }

            $platform = $connection->getDatabasePlatform();
            $queries = $schema->toSql($platform);

            foreach ($queries as $query) {
                $connection->executeQuery($query);
            }
        }
    }

    /**
     * @param Importer $importer
     * @param Connection $connection
     * @param $tableName
     * @param array $keys
     * @param array|null $uniqueKeys
     */
    protected function executeQueryAndPerformTests(
        Importer $importer,
        Connection $connection,
        $tableName,
        array $keys,
        array $uniqueKeys = null
    )
    {
        $this->createSchema($connection, $tableName, $keys, $uniqueKeys);
        $this->assertInstanceOf(Importer::class, $importer);
        $this->assertTrue($importer->executeQuery());
    }

    /**
     * @param $table
     * @return bool
     */
    protected function checkIfTableExists(Connection $connection, $table)
    {
        try {
            $query = 'SELECT count(*) as c FROM ' . $table;
            $connection->executeQuery($query);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}