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

use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Console\ControllerMakeCommand as _ControllerMakeCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException as SymfonyFileNotFoundException;

class ControllerMakeCommand extends _ControllerMakeCommand {

    /**
     * Create a new controller creator command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem $files
     * @return void
     */
    public function __construct (Filesystem $files) {
        $this->description = $this->description . ' [replaced by peterdekok]';

        parent::__construct($files);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    public function getStub () {
        $stub = parent::getStub();

        if ($this->option('softdelete'))
            $stub = str_replace('.stub', '.softdelete.stub', $stub);

        return $this->getStubPath($stub);
    }

    /**
     * Change stub path to relevant base.
     *
     * @param string $stub
     * @return string
     */
    public function getStubPath (string $stub) {
        $laravelStub = $stub;

        $stubPathParts = explode(DIRECTORY_SEPARATOR, $stub);
        $stub = array_pop($stubPathParts);

        $userStub = resource_path('peterdekok/laravel-make-softdelete/stubs/controller/' . $stub);

        $vendorStub = __DIR__ . '/../stubs/controller/' . $stub;

        if (file_exists($userStub))
            return $userStub;

        if (file_exists($vendorStub))
            return $vendorStub;

        if (file_exists($laravelStub))
            return $laravelStub;

        throw new SymfonyFileNotFoundException($stub);
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string $name
     * @return string
     */
    protected function buildClass ($name) {
        $replace = $this->mergeCustomReplacements($this->getReplacements($name));

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * @param string $name
     * @return array
     */
    public function getReplacements (string $name) {
        $defaultNamespace = $this->getDefaultNamespace(trim($this->rootNamespace(), '\\'));

        $relativeNamespace = str_replace($defaultNamespace, '', $this->getNamespace($name));

        $replace = [
            "DummyControllerBaseClass" => "Controller",
        ];

        if (trim($relativeNamespace, '\\') === '') {
            $replace["use DummyFQCNControllerBaseClass;\n"] = '';
        } else {
            $replace["DummyFQCNControllerBaseClass"] = $defaultNamespace . "\Controller";
        }

        return $replace;
    }

    /**
     * @param array $replace
     * @return array
     */
    public function mergeCustomReplacements ($replace = []) {
        $customReplacementFile = resource_path('peterdekok/laravel-make-softdelete/replacements.php');

        if (!file_exists($customReplacementFile)) {
            return $replace;
        }

        $customReplace = require $customReplacementFile;

        if (!is_array($customReplace)) {
            $this->error('Custom replacements do not have correct format, ignoring!');

            return $replace;
        }

        foreach ($customReplace as $dummy => $with) {
            if (is_callable($with))
                $with = call_user_func_array($with, []);

            $replace[$dummy] = (string) $with;
        }

        return $replace;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions () {
        $options = parent::getOptions();

        $options[] = ['softdelete', null, InputOption::VALUE_NONE, 'Include the restore method and change destroy to delete in the controller. If a Model is created, this model will include the SoftDelete trait (TODO)'];

        return $options;
    }
}
