<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-10-14
 * Time: 8:25 PM
 */

namespace CST21;


use ILazi\Coders\Console\CST21GeneratorCommand;

class ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/Templates/cst21_config.php' => config_path('cst21.php'),
            ], 'cst21_confg');

            $this->commands([
                CST21GeneratorCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerModelFactory();
    }

    /**
     * Register Model Factory.
     *
     * @return void
     */
    protected function registerModelFactory()
    {
        $this->app->singleton(Customize::class, function ($app) {
            return new Customize(
                $app->make('db')
            );
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [Customize::class];
    }
}