<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use Ki5an\TailwindShades\Color;
use Ki5an\TailwindShades\Support\ColorPreview;

$colors = [
    'primary' => Color::from('#eee'),
    'secondary' => Color::from('#3B82F6'),
    'success' => Color::from('#22C55E'),
    'danger' => Color::from('#EF4444'),
    'warning' => Color::from('#EAB308'),
];

foreach ($colors as $name => $color) {
    $palette = $color->palette();

    $defaultShade = $palette->defaultShade();
    $defaultColor = $palette->get($defaultShade);

    echo "{$name}: {$defaultColor} ({$defaultShade})".PHP_EOL;
}

echo ColorPreview::make($colors);