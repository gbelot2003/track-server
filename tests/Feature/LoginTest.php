<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();
        \Artisa::call('passport:install', ['-vvv' => true]);
    }
}
