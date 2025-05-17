<?php

include(__DIR__ . '/vendor/autoload.php');

function vd($var, ?bool $exit = null): void {
    $callOrigins = debug_backtrace();
    $callOrigin = $callOrigins[0];
    if ($callOrigin['file'] === __FILE__) $callOrigin = $callOrigins[1];

    if (!isCLI()) echo '<pre title="' . $callOrigin['file'] . ':' . $callOrigin['line'] . '">';
    vd($var);
    if (!isCLI()) echo '</pre>';

    if ($exit) exit();
}

function isCLI(): bool {
    $isCLI = function_exists('cli_set_process_title');

    return $isCLI;
}
