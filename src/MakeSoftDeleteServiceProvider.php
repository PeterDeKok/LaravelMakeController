<?php

/*
 * PeterDeKok/LaravelMakeSoftDelete
 *
 * Copyright (C) 2018 peterdekok.nl
 *
 * Peter De Kok <info@peterdekok.nl>
 * <https://package.peterdekok.nl/laravel-make-softdelete/>
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace PeterDeKok\LaravelMakeSoftDelete;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\ServiceProvider;

class MakeSoftDeleteServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;
    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $devCommands = [
        ControllerMakeCommand::class => 'command.controller.make',
        ModelMakeCommand::class      => 'command.model.make',
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot () {
        $this->publishes([
            __DIR__ . '/../config/laravel-make-softdelete.php' => config_path('laravel-make-softdelete.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../stubs' => resource_path('peterdekok/laravel-make-softdelete/stubs'),
        ], 'stubs');

        $this->publishes([
            __DIR__ . '/../replacements.php' => resource_path('peterdekok/laravel-make-softdelete/replacements.php'),
        ], 'replacements');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register () {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-make-softdelete.php', 'laravel-make-softdelete');

        dump(config('laravel-make-softdelete.model.namespace'));

        foreach ($this->devCommands as $concrete => $abstract) {
            dump($abstract);
            if (class_exists($concrete) && is_subclass_of($concrete, GeneratorCommand::class)) {
                $this->registerGeneratorCommand($concrete, $abstract);

                continue;
            }

            $method = "register" . class_basename($concrete);

            if (method_exists($this, $method)) {
                call_user_func_array([$this, $method], [$abstract]);

                continue;
            }

            throw new \BadMethodCallException('Call to undefined method ' . $concrete . '::' . $method . '()');
        }
    }

    /**
     * Replaces the original Generator command.
     *
     * @param string $concrete
     * @param string $abstract
     * @return void
     */
    protected function registerGeneratorCommand ($concrete, $abstract) {
        $this->app->extend($abstract, function ($original, $app) use ($concrete) {
            return new $concrete($app['files']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides () {
        return array_values($this->devCommands);
    }
}
