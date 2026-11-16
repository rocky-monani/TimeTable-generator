<?php
$v = app(App\Services\TimetableGeneratorService::class)->validate();
echo count($v).' issues found'.PHP_EOL;
foreach ($v as $e) {
    echo '[' . $e['type'] . '] ' . $e['message'] . PHP_EOL;
}
