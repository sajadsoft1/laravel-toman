<?php

namespace Evryn\LaravelToman;

use Evryn\LaravelToman\Clients\GuzzleClient;
use Evryn\LaravelToman\Contracts\PaymentRequester;
use Evryn\LaravelToman\Managers\PaymentRequestManager;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class LaravelTomanServiceProvider extends ServiceProvider
{
    const CONFIG_FILE = __DIR__.'/../config/toman.php';
    const TRANSLATION_FILES = __DIR__ . '/../resources/lang/';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $this->loadTranslationsFrom(self::TRANSLATION_FILES, 'toman');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(self::CONFIG_FILE, 'toman');

        // Register the PaymentManager used to separate drivers
        $this->app->singleton(PaymentRequester::class, function ($app) {
            return new PaymentRequestManager($app);
        });

        // Register the Guzzle HTTP client used by drivers to send requests
        $this->app->singleton(GuzzleClient::class, function () {
            return new Client;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function provides()
    {
        return [
            'laravel-toman.payment',
            GuzzleClient::class,
        ];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            self::CONFIG_FILE => config_path('toman.php'),
        ], 'config');

        $this->publishes([
            self::TRANSLATION_FILES => resource_path('lang/vendor/toman'),
        ], 'lang');
    }
}
