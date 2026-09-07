<?php

namespace App\Services;

use App\Models\ExerciseVersion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Throwable;

class JudgeService
{
    /**
     * Local development runner. It executes learner code in a separate OS
     * process with a timeout, then normalizes results behind the same boundary
     * a Judge0-backed service can replace later.
     *
     * @param  Collection<int, \App\Models\TestCase>  $testCases
     * @return array{verdict:string, execution_time_ms:int, memory_kb:int, output:string, tests:array<int, array<string, mixed>>}
     */
    public function evaluate(ExerciseVersion $version, string $language, string $sourceCode, Collection $testCases): array
    {
        if (trim($sourceCode) === '') {
            return $this->result('WRONG_ANSWER', $testCases, 'A non-empty answer is required.');
        }

        return match ($language) {
            'python' => $this->runPython($version, $sourceCode, $testCases),
            'javascript', 'typescript' => $this->runJavaScript($version, $language, $sourceCode, $testCases),
            default => $this->result('INTERNAL_ERROR', $testCases, 'Unsupported language.'),
        };
    }

    /**
     * @param  Collection<int, \App\Models\TestCase>  $testCases
     * @return array{verdict:string, execution_time_ms:int, memory_kb:int, output:string, tests:array<int, array<string, mixed>>}
     */
    private function runPython(ExerciseVersion $version, string $sourceCode, Collection $testCases): array
    {
        $functionName = $this->functionName($version, 'python');
        $workspace = $this->workspace();

        try {
            $solutionPath = $workspace.DIRECTORY_SEPARATOR.'solution.py';
            $runnerPath = $workspace.DIRECTORY_SEPARATOR.'runner.py';

            File::put($solutionPath, $sourceCode);
            File::put($runnerPath, $this->pythonHarness());

            return $this->runProcess(
                $this->process([
                    config('judge.python_binary'),
                    $runnerPath,
                    $solutionPath,
                    $functionName,
                    json_encode($this->testPayload($testCases), JSON_THROW_ON_ERROR),
                ]),
                $testCases,
            );
        } finally {
            File::deleteDirectory($workspace);
        }
    }

    /**
     * @param  Collection<int, \App\Models\TestCase>  $testCases
     * @return array{verdict:string, execution_time_ms:int, memory_kb:int, output:string, tests:array<int, array<string, mixed>>}
     */
    private function runJavaScript(ExerciseVersion $version, string $language, string $sourceCode, Collection $testCases): array
    {
        $functionName = $this->functionName($version, $language);
        $workspace = $this->workspace();

        try {
            $solutionPath = $workspace.DIRECTORY_SEPARATOR.'solution.js';
            $runnerPath = $workspace.DIRECTORY_SEPARATOR.'runner.cjs';

            File::put($solutionPath, $language === 'typescript' ? $this->stripSimpleTypes($sourceCode) : $sourceCode);
            File::put($runnerPath, $this->javascriptHarness());

            return $this->runProcess(
                $this->process([
                    config('judge.node_binary'),
                    $runnerPath,
                    $solutionPath,
                    $functionName,
                    json_encode($this->testPayload($testCases), JSON_THROW_ON_ERROR),
                ]),
                $testCases,
            );
        } finally {
            File::deleteDirectory($workspace);
        }
    }

    /**
     * @param  array<int, string>  $command
     */
    private function process(array $command): Process
    {
        return new Process($command, null, $this->runtimeEnvironment());
    }

    /**
     * @return array<string, string|false>
     */
    private function runtimeEnvironment(): array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return [];
        }

        $path = getenv('Path') ?: getenv('PATH') ?: implode(';', [
            'C:\\Windows\\system32',
            'C:\\Windows',
            'C:\\Windows\\System32\\Wbem',
            'C:\\Program Files\\nodejs',
            'C:\\msys64\\ucrt64\\bin',
        ]);

        $env = [
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
            'WINDIR' => getenv('WINDIR') ?: 'C:\\Windows',
            'ComSpec' => getenv('ComSpec') ?: 'C:\\Windows\\System32\\cmd.exe',
            'TEMP' => getenv('TEMP') ?: sys_get_temp_dir(),
            'TMP' => getenv('TMP') ?: sys_get_temp_dir(),
            'Path' => $path,
            'PATH' => $path,
            'OPENSSL_CONF' => false,
        ];

        foreach (['APPDATA', 'LOCALAPPDATA', 'USERPROFILE', 'HOMEDRIVE', 'HOMEPATH'] as $key) {
            $value = getenv($key);

            if ($value) {
                $env[$key] = $value;
            }
        }

        return $env;
    }

    /**
     * @param  Collection<int, \App\Models\TestCase>  $testCases
     * @return array{verdict:string, execution_time_ms:int, memory_kb:int, output:string, tests:array<int, array<string, mixed>>}
     */
    private function runProcess(Process $process, Collection $testCases): array
    {
        $start = hrtime(true);
        $process->setTimeout((float) config('judge.timeout_seconds'));

        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            return $this->result('TIME_LIMIT_EXCEEDED', $testCases, 'Execution timed out.');
        } catch (Throwable $exception) {
            return $this->result('INTERNAL_ERROR', $testCases, $exception->getMessage());
        }

        $elapsedMs = max(1, (int) ((hrtime(true) - $start) / 1_000_000));
        $output = $process->getOutput();

        if (strlen($output) > (int) config('judge.max_output_bytes')) {
            return $this->result('MEMORY_LIMIT_EXCEEDED', $testCases, 'Output exceeded the configured limit.');
        }

        $decoded = json_decode($output, true);

        if (! is_array($decoded)) {
            $error = trim($process->getErrorOutput());
            $message = $error
                ? 'Runner failed before returning results: '.Str::limit($error, 700)
                : 'Runner returned malformed output.';

            return $this->result('INTERNAL_ERROR', $testCases, $message);
        }

        $status = $decoded['status'] ?? 'INTERNAL_ERROR';

        if ($status !== 'OK') {
            return $this->result($status, $testCases, $decoded['message'] ?? 'Execution failed.');
        }

        $tests = collect($decoded['tests'] ?? [])->map(function (array $test, int $index) use ($testCases, $elapsedMs): array {
            $testCase = $testCases->values()->get($index);
            $visibility = $testCase?->visibility ?? ($test['visibility'] ?? 'VISIBLE');
            $verdict = $test['verdict'] ?? 'INTERNAL_ERROR';

            return [
                'test_name' => $testCase?->name ?? ($test['test_name'] ?? 'test '.($index + 1)),
                'visibility' => $visibility,
                'verdict' => $verdict,
                'execution_time_ms' => $elapsedMs,
                'memory_kb' => 0,
                'message' => $visibility === 'HIDDEN'
                    ? ($verdict === 'ACCEPTED' ? 'Hidden test passed.' : 'Hidden test failed.')
                    : ($test['message'] ?? $verdict),
            ];
        })->all();

        $verdict = collect($tests)->firstWhere('verdict', 'COMPILE_ERROR')['verdict'] ?? null;
        $verdict ??= collect($tests)->firstWhere('verdict', 'RUNTIME_ERROR')['verdict'] ?? null;
        $verdict ??= collect($tests)->firstWhere('verdict', 'WRONG_ANSWER')['verdict'] ?? 'ACCEPTED';

        return [
            'verdict' => $verdict,
            'execution_time_ms' => $elapsedMs,
            'memory_kb' => 0,
            'output' => $verdict === 'ACCEPTED' ? 'All selected tests passed.' : 'Some selected tests failed.',
            'tests' => $tests,
        ];
    }

    /**
     * @param  Collection<int, \App\Models\TestCase>  $testCases
     * @return array{verdict:string, execution_time_ms:int, memory_kb:int, output:string, tests:array<int, array<string, mixed>>}
     */
    protected function result(string $verdict, Collection $testCases, string $message): array
    {
        $tests = $testCases->values()->map(function ($testCase) use ($verdict, $message): array {
            return [
                'test_name' => $testCase->name,
                'visibility' => $testCase->visibility,
                'verdict' => $verdict === 'ACCEPTED' ? 'ACCEPTED' : $verdict,
                'execution_time_ms' => $verdict === 'ACCEPTED' ? 12 : 4,
                'memory_kb' => 1024,
                'message' => $testCase->visibility === 'VISIBLE'
                    ? $message
                    : ($verdict === 'ACCEPTED' ? 'Hidden test passed.' : 'Hidden test failed.'),
            ];
        })->all();

        return [
            'verdict' => $verdict,
            'execution_time_ms' => $verdict === 'ACCEPTED' ? 12 : 4,
            'memory_kb' => 1024,
            'output' => $message,
            'tests' => $tests,
        ];
    }

    private function functionName(ExerciseVersion $version, string $language): string
    {
        $signature = $version->function_signature_by_language[$language] ?? '';

        if (preg_match('/(?:def|function)\s+([A-Za-z_][A-Za-z0-9_]*)/', $signature, $matches)) {
            return $matches[1];
        }

        if (preg_match('/(?:def|function)\s+([A-Za-z_][A-Za-z0-9_]*)/', $version->starterCodeFor($language), $matches)) {
            return $matches[1];
        }

        return 'solution';
    }

    private function workspace(): string
    {
        $path = storage_path('app/judge/'.Str::uuid());
        File::ensureDirectoryExists($path);

        return $path;
    }

    /**
     * @param  Collection<int, \App\Models\TestCase>  $testCases
     * @return array<int, array<string, string|null>>
     */
    private function testPayload(Collection $testCases): array
    {
        return $testCases->values()->map(fn ($testCase) => [
            'name' => $testCase->name,
            'visibility' => $testCase->visibility,
            'input' => $testCase->input,
            'expected_output' => $testCase->expected_output,
        ])->all();
    }

    private function stripSimpleTypes(string $sourceCode): string
    {
        $sourceCode = preg_replace('/:\s*[A-Za-z_][A-Za-z0-9_<>,\s\[\]\|]*/', '', $sourceCode) ?? $sourceCode;

        return preg_replace('/\)\s*:\s*[A-Za-z_][A-Za-z0-9_<>,\s\[\]\|]*/', ')', $sourceCode) ?? $sourceCode;
    }

    private function pythonHarness(): string
    {
        return <<<'PY'
import importlib.util
import json
import sys
import traceback

source_path = sys.argv[1]
function_name = sys.argv[2]
tests = json.loads(sys.argv[3])

def parse_value(value):
    if value is None or value == "":
        return None
    try:
        return json.loads(value)
    except Exception:
        return value

def normalize(value):
    if isinstance(value, str):
        return value.strip()
    return json.dumps(value, separators=(",", ":"), sort_keys=True)

try:
    spec = importlib.util.spec_from_file_location("candidate", source_path)
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
except SyntaxError as exc:
    print(json.dumps({"status": "COMPILE_ERROR", "message": str(exc)}))
    sys.exit(0)
except Exception as exc:
    print(json.dumps({"status": "RUNTIME_ERROR", "message": str(exc)}))
    sys.exit(0)

candidate = getattr(module, function_name, None)

if not callable(candidate):
    print(json.dumps({"status": "COMPILE_ERROR", "message": f"Function '{function_name}' was not defined."}))
    sys.exit(0)

results = []
for test in tests:
    payload = parse_value(test.get("input"))
    expected = normalize(parse_value(test.get("expected_output")))

    try:
        if isinstance(payload, dict):
            actual_value = candidate(**payload)
        elif isinstance(payload, list):
            actual_value = candidate(*payload)
        elif payload is None:
            actual_value = candidate()
        else:
            actual_value = candidate(payload)

        actual = normalize(actual_value)
        accepted = actual == expected
        results.append({
            "test_name": test.get("name"),
            "visibility": test.get("visibility"),
            "verdict": "ACCEPTED" if accepted else "WRONG_ANSWER",
            "message": "passed" if accepted else f"expected {expected}, got {actual}",
        })
    except Exception as exc:
        results.append({
            "test_name": test.get("name"),
            "visibility": test.get("visibility"),
            "verdict": "RUNTIME_ERROR",
            "message": str(exc),
        })

print(json.dumps({"status": "OK", "tests": results}))
PY;
    }

    private function javascriptHarness(): string
    {
        return <<<'JS'
const fs = require('node:fs');
const vm = require('node:vm');

const sourcePath = process.argv[2];
const functionName = process.argv[3];
const tests = JSON.parse(process.argv[4]);
const source = fs.readFileSync(sourcePath, 'utf8');
const context = vm.createContext({
  console: { log() {} },
});

function parseValue(value) {
  if (value === null || value === undefined || value === '') {
    return null;
  }

  try {
    return JSON.parse(value);
  } catch {
    return value;
  }
}

function normalize(value) {
  if (typeof value === 'string') {
    return value.trim();
  }

  return JSON.stringify(value);
}

try {
  const script = new vm.Script(`${source}\nglobalThis.__candidate = typeof ${functionName} !== 'undefined' ? ${functionName} : undefined;`);
  script.runInContext(context, { timeout: 1000 });
} catch (error) {
  const status = error instanceof SyntaxError ? 'COMPILE_ERROR' : 'RUNTIME_ERROR';
  process.stdout.write(JSON.stringify({ status, message: error.message }));
  process.exit(0);
}

if (typeof context.__candidate !== 'function') {
  process.stdout.write(JSON.stringify({ status: 'COMPILE_ERROR', message: `Function '${functionName}' was not defined.` }));
  process.exit(0);
}

const results = [];

for (const test of tests) {
  const payload = parseValue(test.input);
  const expected = normalize(parseValue(test.expected_output));
  context.__payload = payload;

  let callSource = `
    if (Array.isArray(globalThis.__payload)) {
      globalThis.__actual = globalThis.__candidate(...globalThis.__payload);
    } else if (globalThis.__payload && typeof globalThis.__payload === 'object') {
      globalThis.__actual = globalThis.__candidate(...Object.values(globalThis.__payload));
    } else if (globalThis.__payload === null) {
      globalThis.__actual = globalThis.__candidate();
    } else {
      globalThis.__actual = globalThis.__candidate(globalThis.__payload);
    }
  `;

  try {
    new vm.Script(callSource).runInContext(context, { timeout: 1000 });
    const actual = normalize(context.__actual);
    const accepted = actual === expected;
    results.push({
      test_name: test.name,
      visibility: test.visibility,
      verdict: accepted ? 'ACCEPTED' : 'WRONG_ANSWER',
      message: accepted ? 'passed' : `expected ${expected}, got ${actual}`,
    });
  } catch (error) {
    results.push({
      test_name: test.name,
      visibility: test.visibility,
      verdict: 'RUNTIME_ERROR',
      message: error.message,
    });
  }
}

process.stdout.write(JSON.stringify({ status: 'OK', tests: results }));
JS;
    }
}
