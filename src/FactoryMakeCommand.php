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

use Illuminate\Database\Console\Factories\FactoryMakeCommand as _FactoryMakeCommand;
use Illuminate\Filesystem\Filesystem;

class FactoryMakeCommand extends _FactoryMakeCommand {

    use MakeCommandTrait {
        buildClass as traitBuildClass;
    }

    /**
     * Create a new controller creator command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem $files
     * @return void
     */
    public function __construct (Filesystem $files) {
        $this->bootMakeCommandTrait();

        parent::__construct($files);
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass ($name) {
        $this->className = $name;

        if ($this->option('model')) {

            $model = $this->qualifyClass($this->option('model'));

            if (!class_exists($model)) {
                if ($this->confirm("A {$model} model does not exist. Do you want to generate it?", true)) {
                    $this->call('make:model', ['name' => $model, '--softdelete' => $this->option('softdelete')]);
                }
            }
        }

        $buildClass = parent::buildClass($name);

        $replace = $this->getReplacements();

        return str_replace(array_keys($replace), array_values($replace), $buildClass);
    }


    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace ($rootNamespace) {
        if (is_null($config = config('laravel-make-softdelete.model.namespace')))
            return $rootNamespace;

        return trim(str_replace('/', '\\', $config), '\\');
    }

    /**
     * Get the arguments to send to the custom replacement callbacks.
     *
     * @return array
     */
    protected function getReplacementArguments () {
        return [
            'options' => $this->options(),
            'className' => $this->className,
            'model' => $this->option('model') ? $this->qualifyClass($this->option('model')) : null,
        ];
    }

}