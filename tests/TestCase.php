<?php

namespace MateusLecchi\FilamentRichEditorExtender\Tests;

use MateusLecchi\FilamentRichEditorExtender\FilamentRichEditorExtenderServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            FilamentRichEditorExtenderServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app) {}
}
