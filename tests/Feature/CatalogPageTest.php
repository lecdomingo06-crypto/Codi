<?php

namespace Tests\Feature;

use App\Models\Exercise;
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
            ->assertSee('Core Skills')
            ->assertSee('data-calendar-countdown', false)
            ->assertSee('0/5 to next');
    }

    public function test_seeded_problems_support_all_editor_languages(): void
    {
        $this->seed();

        $expectedLanguages = ['python', 'javascript', 'typescript', 'php', 'cpp'];

        Exercise::with('activeVersion')->get()->each(function (Exercise $exercise) use ($expectedLanguages): void {
            $this->assertNotNull($exercise->activeVersion, $exercise->title.' should have an active version.');

            foreach ($expectedLanguages as $language) {
                $this->assertContains(
                    $language,
                    $exercise->activeVersion->supported_languages,
                    $exercise->title.' should support '.$language.'.',
                );
                $this->assertArrayHasKey(
                    $language,
                    $exercise->activeVersion->starter_code_by_language,
                    $exercise->title.' should have starter code for '.$language.'.',
                );
            }
        });
    }

    public function test_practice_sidebar_is_a_real_navigation_menu(): void
    {
        $this->seed();

        $user = User::where('role', 'USER')->firstOrFail();

        $this->actingAs($user)
            ->get(route('catalog.index', ['menu' => 'databases', 'search' => 'database']))
            ->assertOk()
            ->assertSee('aria-label="Problem categories"', false)
            ->assertSee('href="'.e(route('catalog.index', ['menu' => 'company', 'sort' => 'difficulty'])).'"', false)
            ->assertSee('href="'.e(route('catalog.index', ['menu' => 'quizzes'])).'"', false)
            ->assertDontSee('href="'.e(route('community.index', ['tag' => 'QUESTION'])).'"', false)
            ->assertSee('name="menu" value="databases"', false)
            ->assertSee('Databases')
            ->assertSee('practice-menu__group is-active', false);
    }

    public function test_problem_toolbar_has_tooltips_and_about_popup(): void
    {
        $this->seed();

        $user = User::where('role', 'USER')->firstOrFail();

        $this->actingAs($user)
            ->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('data-random-problem', false)
            ->assertSee('aria-pressed="false"', false)
            ->assertSee('data-random-tooltip', false)
            ->assertSee('Choose random problem')
            ->assertSee('Delete progress')
            ->assertSee('data-practice-about-open', false)
            ->assertSee('id="practice-about-modal"', false)
            ->assertSee('About')
            ->assertSee('I created Coddy to make coding practice easier');
    }
}
