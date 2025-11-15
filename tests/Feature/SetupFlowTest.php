<?php

namespace Tests\Feature;

use App\Http\Livewire\Setup\SetupWizard;
use App\Http\Middleware\EnsureSetupIsComplete;
use App\Models\SetupState;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Tests\TestCase;

class SetupFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_routes_redirect_to_setup_until_completed(): void
    {
        EnsureSetupIsComplete::forceForConsole(true);

        $middleware = app(EnsureSetupIsComplete::class);

        $request = Request::create('/es', 'GET');
        $request->attributes->set('locale', 'es');
        $response = $middleware->handle($request, fn () => response('ok', 200));

        $this->assertTrue($response->isRedirection());
        $this->assertStringEndsWith('/es/setup', $response->headers->get('Location'));

        SetupState::markCompleted();

        $second = Request::create('/es', 'GET');
        $second->attributes->set('locale', 'es');
        $responseOk = $middleware->handle($second, fn () => response('ok', 200));

        $this->assertEquals(200, $responseOk->getStatusCode());

        EnsureSetupIsComplete::forceForConsole(false);
    }

    public function test_setup_wizard_creates_admin_and_completes_flow(): void
    {
        EnsureSetupIsComplete::forceForConsole(true);

        Livewire::test(SetupWizard::class)
            ->set('admin.name', 'Admin Principal')
            ->set('admin.email', 'admin@example.com')
            ->set('admin.password', 'password123')
            ->set('admin.password_confirmation', 'password123')
            ->call('next')
            ->assertSet('step', 2)
            ->call('next')
            ->call('finish')
            ->assertRedirect(route('dashboard', ['locale' => $this->testingLocale]));

        $this->assertTrue(SetupState::isCompleted());
        $this->assertEquals(1, User::count());
        $this->assertEquals('admin@example.com', User::first()->email);

        EnsureSetupIsComplete::forceForConsole(false);
    }
}
