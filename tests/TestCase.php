<?php

namespace Codificar\MarketplaceIntegration\Test;

use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
// use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Orchestra\Testbench\TestCase as BaseTestCase;


abstract class TestCase extends BaseTestCase
{
    // use LazilyRefreshDatabase;
    // use CreatesApplication;
    // use WithFaker;

     /**
     * Setup the test case.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

    }

    /**
     * Get the service providers for the package.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Codificar\MarketplaceIntegration\MarketplaceServiceProvider'];
    }

    /**
     * Configure the environment.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        //$app['config']->set('queue.default', 'redis');
    }

}
