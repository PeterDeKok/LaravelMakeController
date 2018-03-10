<?php

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
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot () {
        $this->publishes([
            __DIR__.'/../stubs' => resource_path('peterdekok/laravel-make-softdelete/stubs'),
        ], 'stubs');

        $this->publishes([
            __DIR__.'/../replacements.php' => resource_path('peterdekok/laravel-make-softdelete/replacements.php'),
        ], 'replacements');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register () {
        foreach ($this->devCommands as $concrete => $abstract) {
            if (class_exists($concrete) && is_subclass_of($concrete, GeneratorCommand::class)) {
                $this->registerGeneratorCommand($concrete, $abstract);

                return null;
            }

            $method = "register" . class_basename($concrete);

            if (method_exists($this, $method)) {
                call_user_func_array([$this, $method], [$abstract]);

                return null;
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
