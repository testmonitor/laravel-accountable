# Accountable Eloquent models

[![Latest Stable Version](https://poser.pugx.org/bytestgear/laravel-accountable/v/stable)](https://packagist.org/packages/bytestgear/laravel-accountable)
[![CircleCI](https://img.shields.io/circleci/project/github/byTestGear/laravel-accountable.svg)](https://circleci.com/gh/byTestGear/laravel-accountable)
[![Travis Build](https://travis-ci.org/byTestGear/laravel-accountable.svg?branch=master)](https://travis-ci.org/byTestGear/laravel-accountable)
[![Code Quality](https://scrutinizer-ci.com/g/byTestGear/laravel-accountable/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/byTestGear/laravel-accountable/?branch=master)
[![StyleCI](https://styleci.io/repos/89096388/shield)](https://styleci.io/repos/89096388)
[![License](https://poser.pugx.org/bytestgear/laravel-accountable/license)](https://packagist.org/packages/laravel-accountable)

This package provides a trait that tracks the user responsible for creating, modifying, or 
deleting an Eloquent model. 

Accountable will observe any activity on your models and it sets the **created_by_user_id**, **updated_by_user_id**, and **deleted_by_user_id** 
accordingly using the currently authenticated user. 

It also provides you with some useful scope query functions, allowing you to fetch models that were either created, modified, or deleted 
by a specific user.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
    * [Using the Migration helper](#using-the-migration-helper)
    * [Using the Trait](#using-the-trait)
- [Examples](#examples)
- [Tests](#tests)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)
  
## Installation

This package can be installed through Composer:

```sh
$ composer require bytestgear/laravel-accountable
```

The package will automatically register itself. 

Older Laravel versions (5.4, 5.3, 5.2) require you to add the Accountable service provider in your 
Laravel config file `config/app.php` manually:

```php
ByTestGear\Accountable\AccountableServiceProvider::class
```

Optionally, publish the configuration file:

```sh
$ php artisan vendor:publish --provider="ByTestGear\Accountable\AccountableServiceProvider" --tag="config"
```

The configuration file allows you to set the preferred authentication driver and the database
column names. 

When left untouched, Accountable will use the default authentication driver and
the default column names (*created_by_user_id*, *updated_by_user_id*, and *deleted_by_user_id*).

## Usage

In order to add Accountable to your Laravel application, you'll need to:<br />

1. Add the required columns to your migration file(s).
2. Use the trait ```ByTestGear\Accountable\Traits\Accountable``` on your model(s).

*Please note that due to the nature of Laravel event system, mass updates 
will not be handled by Accountable.*
 
### Using the Migration helper

The migration helper simplifies the process of adding columns to your migration:

```php
use ByTestGear\Accountable\Accountable;

class CreateProjectsTable extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            
            $table->timestamps();
            $table->softDeletes();
            
            // This will add the required columns
            Accountable::columns($table);
        });
    }
}
```

Tip: if you do not use soft-deletes on your model, use `Accountable::columns($table, false)` to prevent
the helper from adding a *deleted_by_user_id* column.

### Using the Trait

Add the Accountable trait on the models you want to track:

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use ByTestGear\Accountable\Traits\Accountable;

class Project extends Model
{
    use Accountable, SoftDeletes;
    
    protected $table = 'projects';
}
```

## Examples

Setup your model and make sure you are authenticated.

Create a project and show the name of the user that created it:

```php
$project = new Project(['name' => 'Awesome project']);
$project->save();

// Show the name of user that created the project
echo $project->createdBy->name; 
```

Get all projects created by a specific user:

```php
$user = User::findOrFail(42);

// Get all projects created by user with id 42
Project::onlyCreatedBy($user)->get();
```

You can use the following properties and methods to reveal the user responsible:

```php
// Get the user that created the model
$model->created_by_user_id;
$model->createdBy->name;

// Get the user that last updated the model
$model->updated_by_user_id;
$model->updatedBy->name;

// Get the user that last deleted the model
$model->deleted_by_user_id;
$model->deletedBy->name;
```

The following scope queries are at your disposal:

```php
// Retrieve the models either created, updated, 
// or deleted by $user.
Model::onlyCreatedBy($user)->get();
Model::onlyUpdatedBy($user)->get();
Model::onlyDeletedBy($user)->get();

// And one extra: get all models that were created 
// by the currently authenticated user.
Model::mine()->get(); 
```

## Tests

The package contains integration tests. You can run them using PHPUnit.

```
$ vendor/bin/phpunit
```

## Changelog

Refer to [CHANGELOG](CHANGELOG.md) for more information.

## Contributing

Refer to [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

## Credits

- [Thijs Kok](https://www.testmonitor.com/)
- [Stephan Grootveld](https://www.testmonitor.com/)
- [Frank Keulen](https://www.testmonitor.com/)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Refer to the [License](LICENSE.md) for more information.
