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

    /**
     * Convert an OKLCH color to the closest displayable sRGB color.
     */
    public static function fromOklch(
        float $lightness,
        float $chroma,
        float $hue,
    ): Color {
        $fittedChroma = self::fitChroma(
            $lightness,
            $chroma,
            $hue,
        );

        $linearRgb = self::oklchToLinearRgb(
            $lightness,
            $fittedChroma,
            $hue,
        );

        return Color::fromRgb(
            self::toEightBitChannel($linearRgb['red']),
            self::toEightBitChannel($linearRgb['green']),
            self::toEightBitChannel($linearRgb['blue']),
        );
    }

    private static function fitChroma(
        float $lightness,
        float $chroma,
        float $hue,
    ): float {
        if (self::isInSrgbGamut($lightness, $chroma, $hue)) {
            return $chroma;
        }

        $minimumChroma = 0.0;
        $maximumChroma = $chroma;

        for ($iteration = 0; $iteration < 20; $iteration++) {
            $candidateChroma = ($minimumChroma + $maximumChroma) / 2;

            if (self::isInSrgbGamut($lightness, $candidateChroma, $hue)) {
                $minimumChroma = $candidateChroma;

                continue;
            }

            $maximumChroma = $candidateChroma;
        }

        return $minimumChroma;
    }

    private static function isInSrgbGamut(
        float $lightness,
        float $chroma,
        float $hue,
    ): bool {
        $linearRgb = self::oklchToLinearRgb($lightness, $chroma, $hue);

        return self::isNormalized($linearRgb['red'])
            && self::isNormalized($linearRgb['green'])
            && self::isNormalized($linearRgb['blue']);
    }

    /**
     * @return array{red: float, green: float, blue: float}
     */
    private static function oklchToLinearRgb(
        float $lightness,
        float $chroma,
        float $hue,
    ): array {
        $hueInRadians = deg2rad($hue);
        $opponentA = $chroma * cos($hueInRadians);
        $opponentB = $chroma * sin($hueInRadians);

        $long = $lightness
            + (0.3963377774 * $opponentA)
            + (0.2158037573 * $opponentB);

        $medium = $lightness
            - (0.1055613458 * $opponentA)
            - (0.0638541728 * $opponentB);

        $short = $lightness
            - (0.0894841775 * $opponentA)
            - (1.2914855480 * $opponentB);

        $long **= 3;
        $medium **= 3;
        $short **= 3;

        return [
            'red' => (
                4.0767416621 * $long
                - 3.3077115913 * $medium
                + 0.2309699292 * $short
            ),
            'green' => (
                -1.2684380046 * $long
                + 2.6097574011 * $medium
                - 0.3413193965 * $short
            ),
            'blue' => (
                -0.0041960863 * $long
                - 0.7034186147 * $medium
                + 1.7076147010 * $short
            ),
        ];
    }

    private static function isNormalized(float $value): bool
    {
        return $value >= 0.0 && $value <= 1.0;
    }

    private static function toEightBitChannel(float $linearValue): int
    {
        return (int) round(
            self::clamp(self::delinearize($linearValue)) * 255,
        );
    }

    private static function delinearize(float $value): float
    {
        return $value <= 0.0031308
            ? 12.92 * $value
            : 1.055 * ($value ** (1 / 2.4)) - 0.055;
    }

    private static function clamp(float $value): float
    {
        return max(0.0, min(1.0, $value));
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
