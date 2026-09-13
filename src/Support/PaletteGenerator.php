<?php

declare(strict_types=1);

namespace Ki5an\TailwindShades\Support;

use Ki5an\TailwindShades\Color;

final class PaletteGenerator
{
    /**
     * Tailwind-style shade positions.
     *
     * @var array<int>
     */
    private const SHADES = [
        50,
        100,
        200,
        300,
        400,
        500,
        600,
        700,
        800,
        900,
        950,
    ];

    /**
     * Target OKLCH lightness for each shade.
     *
     * @var array<int, float>
     */
    private const LIGHTNESS = [
        50 => 0.98,
        100 => 0.96,
        200 => 0.91,
        300 => 0.84,
        400 => 0.74,
        500 => 0.67,
        600 => 0.57,
        700 => 0.48,
        800 => 0.39,
        900 => 0.32,
        950 => 0.22,
    ];

    /**
     * Generate a complete palette from a source color.
     *
     * The source color is preserved exactly at its detected shade.
     *
     * @return array<int, string>
     */
    public static function generate(Color $color): array
    {
        $defaultShade = self::classify($color);

        return self::generateFrom(
            $color,
            $defaultShade,
        );
    }

    /**
     * Determine the closest shade for a color.
     */
    public static function classify(Color $color): int
    {
        $lightness = $color->toOklch()['l'];

        $closestShade = 500;
        $closestDistance = PHP_FLOAT_MAX;

        foreach (self::LIGHTNESS as $shade => $target) {
            $distance = abs($lightness - $target);

            if ($distance < $closestDistance) {
                $closestDistance = $distance;
                $closestShade = $shade;
            }
        }

        return $closestShade;
    }

    /**
     * Generate shades around the source color.
     *
     * @return array<int, string>
     */
    private static function generateFrom(
        Color $color,
        int $defaultShade,
    ): array {
        $source = $color->toOklch();

        $colors = [];

        foreach (self::SHADES as $shade) {
            if ($shade === $defaultShade) {
                $colors[$shade] = $color->hex();

                continue;
            }

            $colors[$shade] = ColorSpace::fromOklch(
                self::LIGHTNESS[$shade],
                $source['c'],
                $source['h'],
            )->hex();
        }

        return $colors;
    }

}
