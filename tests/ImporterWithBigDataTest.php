<?php

namespace DbImporter\Tests;

use DbImporter\Collections\DataCollection;
use DbImporter\Importer;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

class ImporterWithBigDataTest extends BaseTestCase
{
    const TABLE_NAME = 'photos_table';

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
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

        $json = file_get_contents('https://jsonplaceholder.typicode.com/photos');

        $this->data = new DataCollection();
        $this->data->addItems(json_decode($json, true));

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
     * 5000 records
     * @test
     */
    public function execute_the_multiple_import_query_with_mysql_driver_and_5000_records()
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

    /**
     * 5000 records
     * @test
     */
    public function execute_the_multiple_import_query_with_sqlite_driver_and_5000_records()
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
}