<?php
/**
 * This file is part of the DbImporter package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DbImporter\Tests;

use DbImporter\Importer;

class ImporterTest extends BaseTestCase
{
    const TABLE_NAME = 'photos_table';

    /**
     * @var array
     */
    private $config;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->config = require __DIR__.'/../app/bootstrap.php';
    }

    /**
     * @test
     * @expectedException \DbImporter\Exceptions\NotAllowedDriverException
     * @expectedExceptionMessage The driver sqlsrv is not allowed. Drivers allowed are: [pdo_mysql,pdo_pgsql,pdo_sqlite]
     */
    public function it_throws_NotAllowedDriverException_if_a_not_yet_supported_driver_is_provided()
    {
        $this->makeTest(
            $this->config['sqlsrv_url'],
            'single',
            $this->createPhotosArray(3)
        );
    }

    /**
     * @test
     * @expectedException \DbImporter\Exceptions\NotAllowedModeException
     * @expectedExceptionMessage The mode not-allowed-insert-mode is not allowed. Drivers allowed are: [single,multiple]
     */
    public function it_throws_NotAllowedModeException_if_a_not_yet_supported_driver_is_provided()
    {
        $this->makeTest(
            $this->config['mysql_url'],
            'not-allowed-insert-mode',
            $this->createPhotosArray(3)
        );
    }

    /**
     * @test
     * @expectedException \DbImporter\Exceptions\NotIterableDataException
     * @expectedExceptionMessage Data is not iterable
     */
    public function it_throws_NotIterableDataException_if_data_provided_is_not_iterable()
    {
        $this->makeTest(
            $this->config['mysql_url'],
            'single',
            'string'
        );
    }

    /**
     * @test
     */
    public function execute_the_multiple_import_query_with_mysql_driver()
    {
        $this->makeTest(
            $this->config['mysql_url'],
            'multiple',
            $this->createPhotosStdClassArray(200)
        );

        $this->makeTest(
            $this->config['mysql_url'],
            'multiple',
            $this->createPhotosArray(200)
        );

        $this->makeTest(
            $this->config['mysql_url'],
            'multiple',
            $this->createPhotosCollection(200)
        );
    }

    /**
     * @test
     */
    public function execute_the_multiple_import_query_with_pgsql_driver()
    {
        $this->makeTest(
            $this->config['pgsql_url'],
            'multiple',
            $this->createPhotosStdClassArray(200)
        );

        $this->makeTest(
            $this->config['pgsql_url'],
            'multiple',
            $this->createPhotosArray(200)
        );

        $this->makeTest(
            $this->config['pgsql_url'],
            'multiple',
            $this->createPhotosCollection(200)
        );
    }

    /**
     * @test
     */
    public function execute_the_multiple_import_query_with_sqlite_driver()
    {
        $this->makeTest(
            $this->config['sqlite_url'],
            'multiple',
            $this->createPhotosStdClassArray(100)
        );

        $this->makeTest(
            $this->config['sqlite_url'],
            'multiple',
            $this->createPhotosArray(100)
        );

        $this->makeTest(
            $this->config['sqlite_url'],
            'multiple',
            $this->createPhotosCollection(100)
        );
    }

    /**
     * @test
     */
    public function execute_the_single_import_query_with_mysql_driver()
    {
        $this->makeTest(
            $this->config['mysql_url'],
            'single',
            $this->createPhotosStdClassArray(200)
        );

        $this->makeTest(
            $this->config['mysql_url'],
            'single',
            $this->createPhotosArray(200)
        );

        $this->makeTest(
            $this->config['mysql_url'],
            'single',
            $this->createPhotosCollection(200)
        );
    }

    /**
     * @test
     */
    public function execute_the_single_import_query_with_pgsql_driver()
    {
        $this->makeTest(
            $this->config['pgsql_url'],
            'single',
            $this->createPhotosStdClassArray(200)
        );

        $this->makeTest(
            $this->config['pgsql_url'],
            'single',
            $this->createPhotosArray(200)
        );

        $this->makeTest(
            $this->config['pgsql_url'],
            'single',
            $this->createPhotosCollection(200)
        );
    }

    /**
     * @test
     */
    public function execute_the_single_import_query_with_sqlite_driver()
    {
        $this->makeTest(
            $this->config['sqlite_url'],
            'single',
            $this->createPhotosStdClassArray(100)
        );

        $this->makeTest(
            $this->config['sqlite_url'],
            'single',
            $this->createPhotosArray(100)
        );

        $this->makeTest(
            $this->config['sqlite_url'],
            'single',
            $this->createPhotosCollection(100)
        );
    }

    /**
     * @param $url
     * @param $mode
     * @param $data
     */
    private function makeTest($url, $mode, $data)
    {
        $connection = $this->getConnection($url);

        $mapping = [
            'id' => 'id',
            'album_id' => 'albumId',
            'titolo' => 'title',
            'url' => 'url',
            'thumbnail_url' => 'thumbnailUrl',
        ];

        $keys = [
            'id' => 'integer',
            'album_id' => 'integer',
            'titolo' => 'string',
            'url' => 'string',
            'thumbnail_url' => 'string',
        ];

        $uniqueKeys = ['id'];

        $importer = Importer::init(
            $connection,
            self::TABLE_NAME,
            $mapping,
            $data,
            true,
            $mode
        );

        $this->executeImportQueryAndPerformTests(
            $importer,
            $keys,
            $uniqueKeys
        );
    }
}
