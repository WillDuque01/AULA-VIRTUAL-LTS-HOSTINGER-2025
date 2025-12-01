<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

// [AGENTE: OPUS 4.5] - Fix: RefreshDatabase requerido para migrar tablas antes de acceder a la app
class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get($this->localized());

        $response->assertStatus(200);
    }
}
