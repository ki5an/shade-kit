<?php

declare(strict_types=1);

namespace Ki5an\TailwindShades;

final class Contrast
{
    /**
     * Calculate the WCAG contrast ratio between two colors.
     *
     * The ratio ranges from 1:1 to 21:1.
     */
    public static function ratio(
        Color $foreground,
        Color $background,
    ): float {
        $foregroundLuminance = $foreground->luminance();
        $backgroundLuminance = $background->luminance();

        $lighter = max(
            $foregroundLuminance,
            $backgroundLuminance,
        );

        $darker = min(
            $foregroundLuminance,
            $backgroundLuminance,
        );

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Determine whether two colors meet a contrast ratio.
     */
    public static function passes(
        Color $foreground,
        Color $background,
        float $ratio = Wcag::AA_TEXT,
    ): bool {
        return self::ratio(
            $foreground,
            $background,
        ) >= $ratio;
    }

    /**
     * Return the best black or white text color
     * for the given background.
     */
    public static function textColor(Color $background): string
    {
        $black = Color::from('#000000');
        $white = Color::from('#ffffff');

        return self::ratio($black, $background)
            >= self::ratio($white, $background)
            ? $black->hex()
            : $white->hex();
    }
}