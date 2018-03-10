# Laravel Make SoftDelete

## Introduction

PeterDeKok's LaravelMakeSoftDelete brings the SoftDelete paradigm and Generators together.

> The build-in Laravel generators create boilerplate for common files. \
> The build-in SoftDelete Trait adds soft-delete functionality to Models

However, the two don't work together without a lot of manual coding/duplication.

This package tries to resolve this.

> `php artisan make:model NewModel --all --softdelete` \
> `php artisan make:controller NewApiResourceController --resource --api --softdelete`
 
A cherry on top: It also implements a way to easily add replacements to the generator.

## Installation

The extension is easy to install. In the root of your Laravel project, run \ 
> `composer require --dev peterdekok/laravel-make-softdelete`

This is the only thing necessary for the most basic version of the extension to be successfully incorporated into your project.

---

This has only been tested with [`Laravel 5.6`](https://laravel.com/docs/5.6 "Laravel Documentation v5.6"), any other version should be thoroughly tested on an empty project before use.

But registering the Service Provider **should** be the only step needed for `Laravel 4.x`.

Add \
`PeterDeKok\LaravelMakeSoftDelete\MakeSoftDeleteServiceProvider::class` \
to \
`config/app.php`

Make sure it is added **after** the Laravel Framework Service Providers. If this is not done, the command(s) are not overwritten registered. And therefore the old commands are used.

---

For the full functionality of this package, some files need to be published.

The stubs can be changed by running: \
`php artisan vendor:publish --provider=PeterDeKok\\LaravelMakeSoftDelete\\MakeSoftDeleteServiceProvider --tag=stubs`

Custom replacements can be added by running: \
`php artisan vendor:publish --provider=PeterDeKok\\LaravelMakeSoftDelete\\MakeSoftDeleteServiceProvider --tag=replacements`

Custom (default) Model location: \
`php artisan vendor:publish --provider=PeterDeKok\\LaravelMakeSoftDelete\\MakeSoftDeleteServiceProvider --tag=config`

A combination of both can be very powerful.

## Conclusion

At this stage in development, contributions are welcome. There are quite some things left to do.

I will try to keep the develop branch as current as possible, so redundant work on the same feature can be kept to a minimum.

- Testing
- Other generators (Only Controller is done at this point in time.)
- Other features to make a programmers life easier.

---

Pull requests are welcome! \
If they change functionality please start an issue so it can be discussed before investing time though!

Happy Programming!

## License

Copyright (C) 2018 peterdekok.nl

This program is free software: you can redistribute it and/or modify it under the terms of 
the GNU General Public License as published by the Free Software Foundation, 
either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. 
If not, see <https://www.gnu.org/licenses/>.
