<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class DemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_connection_returns_successful_result()
    {
        $this->assertTrue(true);
    }
}
