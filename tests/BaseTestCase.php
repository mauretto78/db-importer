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
use DbImporter\Tests\Entity\Photo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    /**
     * @param Importer $importer
     * @param array $keys
     * @param array|null $uniqueKeys
     */
    protected function executeImportQueryAndPerformTests(
        Importer $importer,
        array $keys,
        array $uniqueKeys = null
    ) {
        $importer->clearData();
        $importer->destroySchema();
        $importer->createSchema($keys, $uniqueKeys);
        $this->assertInstanceOf(Importer::class, $importer);
        $this->assertTrue($importer->execute());
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
     * @param $limit
     * @return array
     */
    public function createPhotosStdClassArray($limit)
    {
        $url = 'https://jsonplaceholder.typicode.com/photos';

        if($array = json_decode(file_get_contents($url))){
            return array_slice($array, 0, $limit);
        }

        return [];
    }

    /**
     * @param $url
     * @return Connection
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
