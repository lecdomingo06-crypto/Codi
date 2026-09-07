<?php

$windowsPython = 'C:\\msys64\\ucrt64\\bin\\python.exe';
$windowsNode = 'C:\\Program Files\\nodejs\\node.exe';

return [
    'python_binary' => env('JUDGE_PYTHON_BINARY', PHP_OS_FAMILY === 'Windows' && file_exists($windowsPython) ? $windowsPython : 'python'),
    'node_binary' => env('JUDGE_NODE_BINARY', PHP_OS_FAMILY === 'Windows' && file_exists($windowsNode) ? $windowsNode : 'node'),
    'timeout_seconds' => (float) env('JUDGE_TIMEOUT_SECONDS', 2.0),
    'max_output_bytes' => (int) env('JUDGE_MAX_OUTPUT_BYTES', 100000),
];
