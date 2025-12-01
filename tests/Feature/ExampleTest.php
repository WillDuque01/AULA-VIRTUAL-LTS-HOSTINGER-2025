<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// [AGENTE: OPUS 4.5] - Fix: RefreshDatabase requerido para migrar tabla settings antes de acceder a la app
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get($this->localized());

        $response->assertStatus(200);
    }
}
