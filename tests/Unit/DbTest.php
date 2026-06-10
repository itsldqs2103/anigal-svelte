<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    use RefreshDatabase;

    public function dbTest()
    {
        $this->assertTrue(true);
    }
}
