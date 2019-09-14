# Laravel Components
This package is a re-published, re-organized and maintained version of [hsameerc/laravel-modular-structure](https://github.com/hsameerc/LaravelModularStructure).

## Documentation

* [Installation](#installation)
* [Getting started](#getting-started)
* [Usage](#usage)


<a name="installation"></a>
## Installation

The best way to install this package is through your terminal via Composer.

To install through Composer, by run the following command:

```
composer require kadivar/laravel-components:dev-master
```
<a name="getting-started"></a>
## Getting started

The built in Artisan command `php artisan make:component name [--migration] [--translation]` generates a ready to use component in the `app/Components` folder and a migration if necessary.

You can generate components named with more than one word, like `foo-bar`.

This is how the generated component would look like:
```
laravel-project/
    app/
    |-- Components/
        |-- FooBar/
            |-- Controllers/
                |-- FooBarController.php
            |-- Models/
                |-- FooBar.php
            |-- Views/
                |-- index.blade.php
            |-- Translations/
                |-- en/
                    |-- example.php
            |-- Requests/
                |-- FooBarRequest.php
            |-- routes.php
            |-- helper.php
                
```

<a name="usage"></a>
## Usage

The generated `RESTful Resource Controller` and the corresponding `routes.php` make it easy to dive in. In my example you would see the output from the `Components/FooBar/Views/index.blade.php` when you open `laravel-project:8000/foo-bar` in your browser.


#### Disable components
In case you want to disable one ore more components, you can add a `components.php` into your projects `app/config` folder. This file should return an array with the component names that should be **loaded**.
e.g:

```
return [
    'enable' => array(
        "customer",
        "jobs",
        "reporting",
    ),
];
```
In this case `LaravelComponents` would only load this three components `customer` `jobs` `reporting`. Every other components in the `app/Components` folder would not be loaded.

`LaravelComponents‍‍` will load all components if there is no `components.php` file in the `config` folder.

You have to follow the `upper camel case` name convention for the component folder. If you had a `Components/foo` folder you have to rename it to `Components/Foo`. 

Also there are changes in the `app/config/components.php` file. Now you have to return an array with the key `enable` instead of `list`.



## License

**Laravel Components** is licensed under the terms of the [MIT License](http://opensource.org/licenses/MIT)
(See LICENSE file for details).