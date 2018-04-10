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

use Illuminate\Database\Console\Migrations\MigrateMakeCommand as _MigrateMakeCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrateMakeCommand extends _MigrateMakeCommand {

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $name = 'make:migration';
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = null;
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'migration';
    /**
     * The name of the class being generated.
     *
     * @var string
     */
    protected $className;
    /**
     * The migration creator instance.
     *
     * @var \PeterDeKok\LaravelMakeSoftDelete\MigrationCreator $creator
     */
    protected $creator;
    /**
     * The model the migration is for.
     *
     * @var \Illuminate\Database\Eloquent\Model $creator
     */
    protected $model;

    /**
     * Create a new migration install command instance.
     *
     * @param \PeterDeKok\LaravelMakeSoftDelete\MigrationCreator $creator
     * @param  \Illuminate\Support\Composer                      $composer
     */
    public function __construct(MigrationCreator $creator, Composer $composer) {
        $this->description = $this->description . ' [replaced by peterdekok]';

        parent::__construct($creator, $composer);
    }

    /**
     * Write the migration file to disk.
     *
     * @param string $name
     * @param string $table
     * @param bool   $create
     *
     * @return void
     * @throws \Exception
     */
    protected function writeMigration($name, $table, $create) {
        if (is_null($table) && !is_null($model = $this->option('model'))) {
            $model = $this->parseModel($model);

            if (class_exists($model) && is_subclass_of($model, Model::class)) {
                /** @var \Illuminate\Database\Eloquent\Model $model */
                $this->model = new $model();

                $table = $this->model->getTable();
            }
        }

        if (!is_null($this->model) && is_null($this->option('create')) && !is_null($table))
            $create = $table;

        $this->className = Str::studly($name);

        $this->creator->setCommand($this);

        $migration = $this->creator->create($name, $this->getMigrationPath(), $table, $create);

        $file = pathinfo($migration, PATHINFO_FILENAME);

        $this->line("<info>Created Migration:</info> {$file}");
    }

    /**
     * Get the arguments to send to the custom replacement callbacks.
     *
     * @return array
     */
    protected function getReplacementArguments() {
        return [
            'options'   => $this->options(),
            'className' => $this->className,
            'model'     => $this->option('model') ? $this->parseModel($this->option('model')) : null,
        ];
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param  string $model
     *
     * @return string
     */
    protected function parseModel($model) {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        $model = trim(str_replace('/', '\\', $model), '\\');

        if (Str::startsWith($model, $rootNamespace = $this->laravel->getNamespace()))
            return $model;

        if (is_null($configNamespace = config('laravel-make-softdelete.model.namespace')))
            return $rootNamespace . $model;

        $configNamespace = trim(str_replace('/', '\\', $configNamespace), '\\') . '\\';

        if (Str::startsWith($model, $configNamespace))
            return $model;

        return $configNamespace . $model;
    }

    /**
     * Get the (custom) replacements for the migration stub.
     *
     * @return array
     */
    public function getReplacements() {
        if (!is_null($this->model)) {
            $model = $this->model;

            $maxFieldLength = strlen($model->getKeyName());
            $fields = array_diff($model->getFillable(), $model->getDates(), [$model->getKeyName()]);
            $reservedDates = array_intersect(['created_at', 'updated_at', 'deleted_at'], $model->getDates());
            $dates = array_diff($model->getDates(), $reservedDates);

            if ($model->getIncrementing()) {
                $attributesUp[] = '            $table->increments(\'' . $model->getKeyName() . '\');' . PHP_EOL;
            } else {
                $attributesUp[] = '            $table->uuid(\'' . $model->getKeyName() . '\');' . PHP_EOL;
            }

            $attributesUp[] = PHP_EOL;

            foreach ($fields as $field) {
                $attributesUp[] = '            $table->string(\'' . $field . '\');' . PHP_EOL;

                $maxFieldLength = max($maxFieldLength, strlen($field));
            }

            if (count($fields))
                $attributesUp[] = PHP_EOL;

            foreach ($dates as $field) {
                $attributesUp[] = '            $table->timestamp(\'' . $field . '\');' . PHP_EOL;

                $maxFieldLength = max($maxFieldLength, strlen($field));
            }

            if (count($dates))
                $attributesUp[] = PHP_EOL;

            foreach ($reservedDates as $field) {
                $attributesUp[] = '            $table->timestamp(\'' . $field . '\');' . PHP_EOL;

                $maxFieldLength = max($maxFieldLength, strlen($field));
            }
        } else if ($this->option('create')) {
            $attributesUp[] = '            $table->increments(\'id\');' . PHP_EOL;
            $attributesUp[] = PHP_EOL;
            $attributesUp[] = '            // TODO';
            $attributesUp[] = PHP_EOL;
            $attributesUp[] = '            $table->timestamps();\n' . PHP_EOL;

            if ($this->option('softdelete'))
                $attributesUp[] = '            $table->softDeletes();\n' . PHP_EOL;
        }

        $replace = [];

        if (empty($attributesUp ?? []))
            $replace["//DummyMigrationContentUp"] = "// TODO";
        else
            $replace["            //DummyMigrationContentUp"] = trim(implode("", $attributesUp ?? []), PHP_EOL);

        if (empty($attributesDown ?? []))
            $replace["        //DummyMigrationContentDown\n"] = "";
        else
            $replace["        //DummyMigrationContentDown"] = trim(implode("", $attributesDown ?? []), PHP_EOL);

        return $this->mergeCustomReplacements($replace);
    }

    /**
     * Merge the custom replacements into the default replacements for the migration stub.
     *
     * @param array $replace
     *
     * @return array
     */
    protected function mergeCustomReplacements(array $replace = []) {
        $customReplacementFile = resource_path('peterdekok/laravel-make-softdelete/replacements.php');

        if (!file_exists($customReplacementFile)) {
            return $replace;
        }

        $customReplace = require $customReplacementFile;

        if (!is_array($customReplace)) {
            $this->error('Custom replacements do not have correct format. Ignoring!');

            return $replace;
        }

        if (!array_key_exists(strtolower($this->type), $customReplace)) {
            $this->warn('Custom replacements do not have ' . strtolower($this->type) . ' entries. Ignoring!');

            return $replace;
        }

        foreach ($customReplace[strtolower($this->type)] as $dummy => $with) {
            if (is_callable($with))
                $with = call_user_func_array($with, [$this->getReplacementArguments()]);

            $replace[$dummy] = (string) $with;
        }

        return $replace;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments() {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the migration.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a migration for the given model.'],

            ['create', 'c', InputOption::VALUE_OPTIONAL, 'The table to be created'],

            ['table', 't', InputOption::VALUE_OPTIONAL, 'The table to migrate.'],

            ['path', 'p', InputOption::VALUE_OPTIONAL, 'The location where the migration file should be created.'],

            ['realpath', 'r', InputOption::VALUE_NONE, 'Indicate any provided migration paths are pre-resolved absolute paths.'],

            ['softdelete', 's', InputOption::VALUE_NONE, 'Indicate that a softdelete field should be included in the migration.'],
        ];
    }
}