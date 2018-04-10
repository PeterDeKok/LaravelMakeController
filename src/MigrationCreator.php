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

use Illuminate\Database\Migrations\MigrationCreator as _MigrationCreator;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class MigrationCreator extends _MigrationCreator {

    /**
     * The console command calling the creator, if applicable.
     *
     * @var \PeterDeKok\LaravelMakeSoftDelete\MigrateMakeCommand $command
     */
    protected $command;

    /**
     * Set the console command.
     *
     * @param \PeterDeKok\LaravelMakeSoftDelete\MigrateMakeCommand $command
     *
     * @return void
     */
    public function setCommand(MigrateMakeCommand $command) {
        $this->command = $command;
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string $name
     * @param  string $stub
     * @param  string $table
     *
     * @return string
     */
    protected function populateStub($name, $stub, $table) {
        $stub = parent::populateStub($name, $stub, $table);

        if (!is_null($this->command)) {
            $replace = $this->command->getReplacements();

            $stub = str_replace(array_keys($replace), array_values($replace), $stub);
        }

        return $stub;
    }

    /**
     * Get the migration stub file.
     *
     * @param  string $table
     * @param  bool $create
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getStub($table, $create) {
        if (is_null($table))
            $stub = 'blank.stub';
        else if ($create)
            $stub = 'create.stub';
        else
            $stub = 'update.stub';

        return $this->files->get($this->getStubPath($stub));
    }

    /**
     * Change stub path to relevant base.
     *
     * @param string $stub
     *
     * @return string
     */
    protected function getStubPath(string $stub) {
        $laravelStub = $stub;

        $stubPathParts = explode(DIRECTORY_SEPARATOR, $stub);
        $stub = array_pop($stubPathParts);

        $userStub = resource_path('peterdekok/laravel-make-softdelete/stubs/migration/' . $stub);

        $vendorStub = __DIR__ . '/../stubs/migration/' . $stub;

        if (file_exists($userStub))
            return $userStub;

        if (file_exists($vendorStub))
            return $vendorStub;

        if (file_exists($laravelStub))
            return $laravelStub;

        throw new FileNotFoundException($stub);
    }
}