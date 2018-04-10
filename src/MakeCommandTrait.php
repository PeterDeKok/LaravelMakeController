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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

trait MakeCommandTrait {

    /**
     * The name of the class being generated.
     *
     * @var string
     */
    protected $className;

    /**
     * @return void
     */
    protected function bootMakeCommandTrait () {
        $this->description = $this->description . ' [replaced by peterdekok]';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub () {
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
    protected function getStubPath (string $stub) {
        $laravelStub = $stub;

        $stubPathParts = explode(DIRECTORY_SEPARATOR, $stub);
        $stub = array_pop($stubPathParts);

        $userStub = resource_path('peterdekok/laravel-make-softdelete/stubs/' . strtolower($this->type) . '/' . $stub);

        $vendorStub = __DIR__ . '/../stubs/' . strtolower($this->type) . '/' . $stub;

        if (file_exists($userStub))
            return $userStub;

        if (file_exists($vendorStub))
            return $vendorStub;

        if (file_exists($laravelStub))
            return $laravelStub;

        throw new FileNotFoundException($stub);
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
        $this->className = $name;

        $buildClass = parent::buildClass($name);

        $replace = $this->getReplacements();

        return str_replace(array_keys($replace), array_values($replace), $buildClass);
    }

    /**
     * @return array
     */
    protected function getReplacements () {
        $defaultNamespace = $this->getDefaultNamespace(trim($this->rootNamespace(), '\\'));
        $relativeNamespace = str_replace($defaultNamespace, '', $this->getNamespace($this->className));

        $replace = [];

        if (trim($relativeNamespace, '\\') === '') {
            $replace["use DummyFQCNTypeBaseClass\\_" . $this->type . ";\n"] = '';
            $replace["use DummyFQCNTypeBaseClass\\" . $this->type . ";\n"] = '';
            $replace["use DummyFQCNTypeBaseClass\DummyTypeBaseClass;\n"] = '';
        } else {
            $replace["DummyFQCNTypeBaseClass"] = $defaultNamespace;
        }

        $replace["DummyTypeBaseClass"] = $this->type;
        $replace["\n\n\n"] = "\n\n";

        return $this->mergeCustomReplacements($replace);
    }

    /**
     * @param array $replace
     * @return array
     */
    protected function mergeCustomReplacements (array $replace = []) {
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
     * Get the arguments to send to the custom replacement callbacks.
     *
     * @return array
     */
    protected function getReplacementArguments () {
        return [
            'options' => $this->options(),
            'className' => $this->className,
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions () {
        $options = parent::getOptions();

        $options[] = ['softdelete', null, InputOption::VALUE_NONE, 'Include soft delete paradigm in the ' . strtolower($this->type) . '.'];

        return $options;
    }
}
