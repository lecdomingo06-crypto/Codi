<?php

namespace Tests\Unit;

use App\Models\Exercise;
use App\Models\User;
use App\Services\JudgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JudgeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_python_solution_is_executed_and_accepted(): void
    {
        $this->seed();
        $version = Exercise::where('slug', 'sum-two-numbers')->firstOrFail()->activeVersion;
        $tests = $version->testBundle->visibleTestCases()->get();

        $result = app(JudgeService::class)->evaluate(
            $version,
            'python',
            "def add(a, b):\n    return a + b",
            $tests,
        );

        $this->assertSame('ACCEPTED', $result['verdict']);
    }

    public function test_javascript_solution_is_executed_and_accepted(): void
    {
        $this->seed();
        $version = Exercise::where('slug', 'sum-two-numbers')->firstOrFail()->activeVersion;
        $tests = $version->testBundle->visibleTestCases()->get();

        $result = app(JudgeService::class)->evaluate(
            $version,
            'javascript',
            "function add(a, b) {\n  return a + b;\n}\n\nconsole.log(add(5, 3));",
            $tests,
        );

        $this->assertSame('ACCEPTED', $result['verdict']);
    }

    public function test_wrong_answer_is_reported_from_real_output_comparison(): void
    {
        $this->seed();
        $version = Exercise::where('slug', 'sum-two-numbers')->firstOrFail()->activeVersion;
        $tests = $version->testBundle->visibleTestCases()->get();

        $result = app(JudgeService::class)->evaluate(
            $version,
            'python',
            "def add(a, b):\n    return a - b",
            $tests,
        );

        $this->assertSame('WRONG_ANSWER', $result['verdict']);
        $this->assertSame('WRONG_ANSWER', $result['tests'][0]['verdict']);
    }

    public function test_compile_error_is_reported(): void
    {
        $this->seed();
        $version = Exercise::where('slug', 'sum-two-numbers')->firstOrFail()->activeVersion;
        $tests = $version->testBundle->visibleTestCases()->get();

        $result = app(JudgeService::class)->evaluate(
            $version,
            'python',
            "def add(a, b)\n    return a + b",
            $tests,
        );

        $this->assertSame('COMPILE_ERROR', $result['verdict']);
    }

    public function test_timeout_is_reported(): void
    {
        config(['judge.timeout_seconds' => 0.5]);
        $this->seed();
        $version = Exercise::where('slug', 'sum-two-numbers')->firstOrFail()->activeVersion;
        $tests = $version->testBundle->visibleTestCases()->get();

        $result = app(JudgeService::class)->evaluate(
            $version,
            'python',
            "def add(a, b):\n    while True:\n        pass",
            $tests,
        );

        $this->assertSame('TIME_LIMIT_EXCEEDED', $result['verdict']);
    }
}
