<?php

namespace Tests;

use App\Support\Content;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Content::flush(); // the content cache lives for the whole test run otherwise
    }
}
