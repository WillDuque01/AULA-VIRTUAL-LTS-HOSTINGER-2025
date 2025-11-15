<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_root_redirects_to_spanish(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/es');
    }

    public function test_dashboard_available_in_english(): void
    {
        $response = $this->get('/en/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/en/login');
    }
}

