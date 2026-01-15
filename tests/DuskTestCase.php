<?php

namespace Tests;

use Laravel\Dusk\TestCase as BaseTestCase;

/**
 * Class DuskTestCase
 *
 * Basic Dusk test case scaffold used by browser tests.
 * - Uses the application's CreatesApplication trait to bootstrap Laravel.
 * - Prepares the ChromeDriver before tests run.
 *
 * If you need custom RemoteWebDriver options (headless arguments, custom URL),
 * override the driver() method from the parent class.
 */
abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     *
     * Laravel Dusk expects a static prepare method to start the ChromeDriver.
     *
     * @return void
     */
    public static function prepare(): void
    {
        static::startChromeDriver();
    }
}
