<?php

namespace Codificar\MarketplaceIntegration\Test;

use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;
    use CreatesApplication;
    use WithFaker;
}
