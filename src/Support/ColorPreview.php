<?php

declare(strict_types=1);

namespace Ki5an\TailwindShades\Support;

use Ki5an\TailwindShades\Color;

final class ColorPreview
{
    /**
     * @param array<string, Color> $colors
     */
    public static function make(array $colors): string
    {
        $output = '';

        foreach ($colors as $name => $color) {
            $output .= self::render(
                $name,
                $color,
            );
        }

        return $output;
    }

    private static function render(
        string $name,
        Color $color,
    ): string {
        $output = PHP_EOL;

        $output .= $name;
        $output .= ' ';
        $output .= str_repeat(
            '=',
            max(1, 56 - strlen($name)),
        );
        $output .= PHP_EOL;

        $palette = $color->palette();

        $defaultShade = $palette->defaultShade();

        foreach ($palette->all() as $shade => $hex) {
            $marker = $shade === $defaultShade
                ? ' *'
                : '  ';

            $output .= sprintf(
                '%s %-4d %-8s',
                $marker,
                $shade,
                $hex,
            );

            $output .= self::block($hex);

            if ($shade === $defaultShade) {
                $output .= '  default';
            }

            $output .= PHP_EOL;
        }

        return $output;
    }

    private static function block(string $hex): string
    {
        $channels = Color::fromHex($hex)->rgb();

        return "\033[48;2;"
            .$channels['red']
            .';'
            .$channels['green']
            .';'
            .$channels['blue']
            .'m'
            .'        '
            ."\033[0m";
    }
}
