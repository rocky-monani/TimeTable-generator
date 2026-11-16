<?php
$s = app(App\Services\TimetableGeneratorService::class);
$w = $s->postValidate();
echo count($w) . ' warnings' . PHP_EOL;
foreach ($w as $x) {
    echo '[' . $x['section'] . '] ' . $x['message'] . PHP_EOL;
}
