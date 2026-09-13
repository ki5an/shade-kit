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

            $colors[$shade] = self::fromOklch(
                self::LIGHTNESS[$shade],
                $source['c'],
                $source['h'],
            );
        }

        return $colors;
    }

    /**
     * Create a HEX color from OKLCH values.
     */
    private static function fromOklch(
        float $lightness,
        float $chroma,
        float $hue,
    ): string {
        $chroma = self::fitChroma(
            $lightness,
            $chroma,
            $hue,
        );

        $angle = deg2rad($hue);

        $a = $chroma * cos($angle);
        $b = $chroma * sin($angle);

        $l = $lightness;

        $lmsL = $l
            + (0.3963377774 * $a)
            + (0.2158037573 * $b);

        $lmsM = $l
            - (0.1055613458 * $a)
            - (0.0638541728 * $b);

        $lmsS = $l
            - (0.0894841775 * $a)
            - (1.2914855480 * $b);

        $lmsL **= 3;
        $lmsM **= 3;
        $lmsS **= 3;

        $red = (
            4.0767416621 * $lmsL
            - 3.3077115913 * $lmsM
            + 0.2309699292 * $lmsS
        );

        $green = (
            -1.2684380046 * $lmsL
            + 2.6097574011 * $lmsM
            - 0.3413193965 * $lmsS
        );

        $blue = (
            -0.0041960863 * $lmsL
            - 0.7034186147 * $lmsM
            + 1.7076147010 * $lmsS
        );

        return sprintf(
            '#%02x%02x%02x',
            self::channel(self::delinearize($red)),
            self::channel(self::delinearize($green)),
            self::channel(self::delinearize($blue)),
        );
    }

    /**
     * Reduce chroma until the color fits inside the sRGB gamut.
     */
    private static function fitChroma(
        float $lightness,
        float $chroma,
        float $hue,
    ): float {
        if (self::isInGamut(
            $lightness,
            $chroma,
            $hue,
        )) {
            return $chroma;
        }

        $low = 0.0;
        $high = $chroma;

        for ($i = 0; $i < 20; $i++) {
            $mid = ($low + $high) / 2;

            if (self::isInGamut(
                $lightness,
                $mid,
                $hue,
            )) {
                $low = $mid;
            } else {
                $high = $mid;
            }
        }

        return $low;
    }

    /**
     * Determine whether an OKLCH color fits inside sRGB.
     */
    private static function isInGamut(
        float $lightness,
        float $chroma,
        float $hue,
    ): bool {
        $angle = deg2rad($hue);

        $a = $chroma * cos($angle);
        $b = $chroma * sin($angle);

        $lmsL = $lightness
            + (0.3963377774 * $a)
            + (0.2158037573 * $b);

        $lmsM = $lightness
            - (0.1055613458 * $a)
            - (0.0638541728 * $b);

        $lmsS = $lightness
            - (0.0894841775 * $a)
            - (1.2914855480 * $b);

        $lmsL **= 3;
        $lmsM **= 3;
        $lmsS **= 3;

        $red = (
            4.0767416621 * $lmsL
            - 3.3077115913 * $lmsM
            + 0.2309699292 * $lmsS
        );

        $green = (
            -1.2684380046 * $lmsL
            + 2.6097574011 * $lmsM
            - 0.3413193965 * $lmsS
        );

        $blue = (
            -0.0041960863 * $lmsL
            - 0.7034186147 * $lmsM
            + 1.7076147010 * $lmsS
        );

        return $red >= 0.0 && $red <= 1.0
            && $green >= 0.0 && $green <= 1.0
            && $blue >= 0.0 && $blue <= 1.0;
    }

    /**
     * Convert a linear RGB channel to sRGB.
     */
    private static function delinearize(float $value): float
    {
        return $value <= 0.0031308
            ? 12.92 * $value
            : 1.055 * ($value ** (1 / 2.4)) - 0.055;
    }

    /**
     * Convert a normalized RGB channel to 8-bit.
     */
    private static function channel(float $value): int
    {
        return (int) round(
            max(0.0, min(1.0, $value)) * 255,
        );
    }
}