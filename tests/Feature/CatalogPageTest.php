<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_renders_with_selected_calendar_month(): void
    {
        $this->seed();

        $user = User::where('role', 'USER')->firstOrFail();

        $this->actingAs($user)
            ->get(route('catalog.index', ['month' => '2026-08']))
            ->assertOk()
            ->assertSee('August 2026')
            ->assertSee('Core Skills');
    }
}
