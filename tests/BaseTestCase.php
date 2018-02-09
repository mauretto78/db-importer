<?php

namespace DbImporter\Tests;

use DbImporter\Importer;
use DbImporter\Tests\Entity\Photo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    /**
     * @param Importer $importer
     * @param Connection $connection
     * @param $tableName
     * @param array $keys
     * @param array|null $uniqueKeys
     */
    protected function executeImportQueryAndPerformTests(
        Importer $importer,
        Connection $connection,
        $tableName,
        array $keys,
        array $uniqueKeys = null
    ) {
        $this->createSchema($connection, $tableName, $keys, $uniqueKeys);
        $this->assertInstanceOf(Importer::class, $importer);
        $this->assertTrue($importer->execute());
    }

    /**
     * @param Connection $connection
     */
    protected function createSchema(
        Connection $connection,
        $tableName,
        array $keys,
        array $uniqueKeys = null
    ) {
        $schema = new Schema();

        if (false === $this->checkIfTableExists($connection, $tableName)) {
            $table = $schema->createTable($tableName);

            foreach ($keys as $key => $type) {
                $table->addColumn($key, $type);
            }

            if ($uniqueKeys) {
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

    /**
     * @param $limit
     * @return array
     */
    protected function createPhotosArray($limit)
    {
        $array = [];
        $faker = Factory::create();

        for ($i = 1; $i <= $limit; $i++) {
            $array[] = [
                'id' => $i,
                'albumId' => ($i+1),
                'title' => $faker->name,
                'url' => $faker->url,
                'thumbnailUrl' => $faker->imageUrl()
            ];
        }

        return $array;
    }

    /**
     * @param $limit
     * @return ArrayCollection
     */
    protected function createPhotosCollection($limit)
    {
        $array = [];
        $faker = Factory::create();

        for ($i = 1; $i <= $limit; $i++) {
            $array[] = new Photo(
                $i,
                ($i+1),
                $faker->name,
                $faker->url,
                $faker->imageUrl()
            );
        }

        return new ArrayCollection($array);
    }

    /**
     * @param $url
     * @return \Doctrine\DBAL\Connection
     */
    protected function getConnection($url)
    {
        return DriverManager::getConnection(
            [
                'url' => $url,
            ],
            new Configuration()
        );
    }
}
