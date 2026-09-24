<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Runs before RefreshDatabase / DatabaseTransactions are applied.
     * Refuses to touch any database whose name doesn't end in "_test", so a
     * misconfigured phpunit.xml / .env can never migrate:fresh the dev data.
     */
    protected function setUpTraits()
    {
        $connection = config('database.default');
        $database   = (string) config("database.connections.{$connection}.database");

        if (! str_ends_with($database, '_test')) {
            throw new \RuntimeException(
                "Refusing to run tests against database \"{$database}\". "
                . 'Tests must use a dedicated database whose name ends in "_test" (see phpunit.xml).'
            );
        }

        return parent::setUpTraits();
    }
}
