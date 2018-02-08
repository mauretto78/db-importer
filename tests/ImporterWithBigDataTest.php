<?php

namespace DbImporter\Tests;

use DbImporter\Collections\DataCollection;
use DbImporter\Importer;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Faker\Factory;

class ImporterWithBigDataTest extends BaseTestCase
{
    const TABLE_NAME = 'photos_table';

    /**
     * @var array
     */
    private $config;

    /**
     * @var DataCollection
     */
    private $data;

    /**
     * @var array
     */
    private $mapping;

    /**
     * @var array
     */
    private $keys;

    /**
     * @var array
     */
    private $uniqueKeys;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->config = require __DIR__.'/../app/bootstrap.php';

        $array = [];
        $faker = Factory::create();

        for ($i = 1; $i <= 50000; $i++) {
            $array[] = [
                'id' => $i,
                'albumId' => ($i+1),
                'title' => $faker->name,
                'url' => $faker->url,
                'thumbnailUrl' => $faker->imageUrl()
            ];
        }

        $this->data = new DataCollection();
        $this->data->addItems($array);

        $this->mapping = [
            'id' => 'id',
            'album_id' => 'albumId',
            'titolo' => 'title',
            'url' => 'url',
            'thumbnail_url' => 'thumbnailUrl',
        ];

        $this->keys = [
            'id' => 'integer',
            'album_id' => 'integer',
            'titolo' => 'string',
            'url' => 'string',
            'thumbnail_url' => 'string',
        ];

        $this->uniqueKeys = ['id'];
    }

    /**
     * @test
     */
    public function execute_the_multiple_import_query_with_sqlite_driver_and_50000_records()
    {
        $connection = DriverManager::getConnection(
            [
                'url' => $this->config['sqlite_url'],
            ],
            new Configuration()
        );

        $importer = Importer::init(
            $connection,
            self::TABLE_NAME,
            $this->mapping,
            $this->data,
            true
        );

        $this->executeQueryAndPerformTests(
            $importer,
            $connection,
            self::TABLE_NAME,
            $this->keys,
            $this->uniqueKeys
        );
    }

    /**
     * @test
     */
    public function execute_the_multiple_import_query_with_mysql_driver_and_50000_records()
    {
        $connection = DriverManager::getConnection(
            [
                'url' => $this->config['mysql_url'],
            ],
            new Configuration()
        );

        $importer = Importer::init(
            $connection,
            self::TABLE_NAME,
            $this->mapping,
            $this->data,
            true
        );

        $this->executeQueryAndPerformTests(
            $importer,
            $connection,
            self::TABLE_NAME,
            $this->keys,
            $this->uniqueKeys
        );
    }
}
