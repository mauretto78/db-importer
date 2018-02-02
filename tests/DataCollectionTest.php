<?php

namespace DbImporter\Tests;

use DbImporter\Collections\DataCollection;
use PHPUnit\Framework\TestCase;

class DataCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_adds_and_removes_items_to_collection()
    {
        $data = new DataCollection();
        $data->addItem([
            'id' => 1,
            'name' => 'Mauro',
            'email' => 'm.cassani@bestnetwork.it',
            'username' => 'mauretto78',
        ]);
        $data->addItem([
            'id' => 2,
            'name' => 'Damian',
            'email' => 'damian@bestnetwork.it',
            'username' => 'bigfoot90',
        ]);
        $data->addItem([
            'id' => 3,
            'name' => 'Matteo',
            'email' => 'm.adamo@bestnetwork.it',
            'username' => 'maffeo',
        ]);

        $this->assertCount(3, $data);

        $data->removeItem(0);

        $this->assertCount(2, $data);

        $data->addItems([
            [
                'id' => 4,
                'name' => 'Roberto',
                'email' => 'r.curti@bestnetwork.it',
                'username' => 'rebberto',
            ],
            [
                'id' => 5,
                'name' => 'Nicola',
                'email' => 'n.muzi@bestnetwork.it',
                'username' => 'nicola',
            ]
        ]);

        $this->assertCount(4, $data);
    }
}
