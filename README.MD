# Laravel Make SoftDelete

> `php artisan make:controller SomeResourceController --resource --softdelete`

> `php artisan make:model SomeModel --all --softdelete`

Description

## Installation

- Add `PeterDeKok\LaravelMakeSoftDelete\MakeSoftDeleteServiceProvider::class` to `config/app.php`
Make sure it is added AFTER the Laravel Framework Service Providers. If not: command(s) not overwritten.