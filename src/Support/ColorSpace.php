<?php

declare(strict_types=1);

namespace Ki5an\TailwindShades\Support;

use Ki5an\TailwindShades\Color;

final class ColorSpace
{
    public static function luminance(Color $color): float
    {
        $red = self::linearize($color->red() / 255);
        $green = self::linearize($color->green() / 255);
        $blue = self::linearize($color->blue() / 255);

        return
            (0.2126 * $red)
            + (0.7152 * $green)
            + (0.0722 * $blue);
    }

    /**
     * Return HSL lightness.
     */
    public static function lightness(Color $color): float
    {
        $red = $color->red() / 255;
        $green = $color->green() / 255;
        $blue = $color->blue() / 255;

        $maximum = max($red, $green, $blue);
        $minimum = min($red, $green, $blue);

        return ($maximum + $minimum) / 2;
    }

    /**
     * Return HSL saturation.
     */
    public static function saturation(Color $color): float
    {
        $red = $color->red() / 255;
        $green = $color->green() / 255;
        $blue = $color->blue() / 255;

        $maximum = max($red, $green, $blue);
        $minimum = min($red, $green, $blue);
        $delta = $maximum - $minimum;

        if ($delta === 0.0) {
            return 0.0;
        }

        $lightness = ($maximum + $minimum) / 2;

        return $delta / (
            1 - abs((2 * $lightness) - 1)
        );
    }

    /**
     * Return OKLCH hue in degrees.
     */
    public static function hue(Color $color): float
    {
        return self::toOklch($color)['h'];
    }

    /**
     * Return OKLCH chroma.
     */
    public static function chroma(Color $color): float
    {
        return self::toOklch($color)['c'];
    }

    /**
     * Convert RGB to OKLab.
     *
     * @return array{
     *     l: float,
     *     a: float,
     *     b: float
     * }
     */
    public static function toOklab(Color $color): array
    {
        $red = self::linearize($color->red() / 255);
        $green = self::linearize($color->green() / 255);
        $blue = self::linearize($color->blue() / 255);

        $l = (
            0.4122214708 * $red
            + 0.5363325363 * $green
            + 0.0514459929 * $blue
        );

        $m = (
            0.2119034982 * $red
            + 0.6806995451 * $green
            + 0.1073969566 * $blue
        );

        $s = (
            0.0883024619 * $red
            + 0.2817188376 * $green
            + 0.6299787005 * $blue
        );

        $l = self::cubeRoot($l);
        $m = self::cubeRoot($m);
        $s = self::cubeRoot($s);

        return [
            'l' => (
                0.2104542553 * $l
                + 0.7936177850 * $m
                - 0.0040720468 * $s
            ),
            'a' => (
                1.9779984951 * $l
                - 2.4285922050 * $m
                + 0.4505937099 * $s
            ),
            'b' => (
                0.0259040371 * $l
                + 0.7827717662 * $m
                - 0.8086757660 * $s
            ),
        ];
    }

    /**
     * Convert RGB to OKLCH.
     *
     * @return array{
     *     l: float,
     *     c: float,
     *     h: float
     * }
     */
    public static function toOklch(Color $color): array
    {
        $oklab = self::toOklab($color);

        $chroma = sqrt(
            ($oklab['a'] ** 2)
            + ($oklab['b'] ** 2)
        );

        $hue = rad2deg(
            atan2(
                $oklab['b'],
                $oklab['a'],
            )
        );

        if ($hue < 0) {
            $hue += 360;
        }

        return [
            'l' => $oklab['l'],
            'c' => $chroma,
            'h' => $hue,
        ];
    }

    private static function linearize(float $value): float
    {
        return $value <= 0.04045
            ? $value / 12.92
            : (($value + 0.055) / 1.055) ** 2.4;
    }

    private static function cubeRoot(float $value): float
    {
        return $value < 0
            ? -((- $value) ** (1 / 3))
            : $value ** (1 / 3);
    }
}