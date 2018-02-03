# Db-Importer

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/61444b8259e642f990965fc843283ad7)](https://www.codacy.com/app/mauretto78/db-importer?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=mauretto78/db-importer&amp;utm_campaign=Badge_Grade)
[![license](https://img.shields.io/github/license/mauretto78/db-importer.svg)]()
[![Packagist](https://img.shields.io/packagist/v/mauretto78/db-importer.svg)]()

Db Importer is a PHP library that allows you to import data in your database with very little effort Edit

## Basic Usage

To use Importer simply do this:

```php
use DbImporter\Importer;

// init Importer
$importer = Importer::init(
    $connection, // your DBAL connection
    $table,      // table to import data
    true,        // ignore duplicate
    $mapping,    // mapping array
    $data        // your data, must be an instance of `DataCollection` class.

);

// Execute import query
$importer->executeQuery()

```

### Mapping

The mapping array is a simple key value array in which you specify the column name on your database and the key in inpu data. Look at the example:

```php
$mapping = [
    'id' => 'id_utente',             // 'id' is the column name on your database. 'id_utente' is the key in input data
    'name' => 'name_utente',         // 'name' is the column name on your database. 'name_utente' is the key in input data
    'username' => 'username_utente', // 'username' is the column name on your database. 'username_utente' is the key in input data
    'email' => 'email_utente',       // 'email' is the column name on your database. 'email_utente' is the key in input data
];
```

### Data

The input data must be an instance of `DataCollection` class.

```php
$data = new DataCollection();
$data->addItem([
    'id_utente' => 1,
    'name_utente' => 'Mauro',
    'email_utente' => 'm.cassani@bestnetwork.it',
    'username_utente' => 'mauretto78',
]);
$data->addItem([
    'id_utente' => 2,
    'name_utente' => 'Damian',
    'username_utente' => 'bigfoot90',
    'email_utente' => 'damian@bestnetwork.it',
]);
$data->addItem([
    'id_utente' => 3,
    'username_utente' => 'maffeo',
    'name_utente' => 'Matteo',
    'email_utente' => 'm.adamo@bestnetwork.it',
]);
```

## Built With

* [DBAL](http://www.doctrine-project.org/projects/dbal.html) - Database Abstraction Layer

## Support

If you found an issue or had an idea please refer [to this section](https://github.com/mauretto78/db-importer/issues).

## Authors

* **Mauro Cassani** - [github](https://github.com/mauretto78)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
