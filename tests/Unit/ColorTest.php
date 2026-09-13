<?php

declare(strict_types=1);

namespace Ki5an\TailwindShades\Tests\Unit;

use InvalidArgumentException;
use Ki5an\TailwindShades\Color;
use PHPUnit\Framework\TestCase;

final class ColorTest extends TestCase
{
    public function test_it_creates_color_from_hex(): void
    {
        $color = Color::fromHex('#E11D48');

        $this->assertSame(
            '#e11d48',
            $color->hex(),
        );
    }

    public function test_it_creates_color_using_from(): void
    {
        $color = Color::from('#E11D48');

        $this->assertSame(
            '#e11d48',
            $color->hex(),
        );
    }

    public function test_it_supports_short_hex(): void
    {
        $color = Color::fromHex('#fff');

        $this->assertSame(
            '#ffffff',
            $color->hex(),
        );
    }

    public function test_it_creates_color_from_rgb(): void
    {
        $color = Color::fromRgb(225, 29, 72);

        $this->assertSame(
            '#e11d48',
            $color->hex(),
        );
    }

    public function test_it_rejects_invalid_hex(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        Color::fromHex('#invalid');
    }

    public function test_it_rejects_invalid_rgb(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        Color::fromRgb(256, 0, 0);
    }

    public function test_it_converts_to_oklab(): void
    {
        $oklab = Color::fromHex('#E11D48')->toOklab();

        $this->assertArrayHasKey('l', $oklab);
        $this->assertArrayHasKey('a', $oklab);
        $this->assertArrayHasKey('b', $oklab);
    }

    public function test_it_converts_to_oklch(): void
    {
        $oklch = Color::fromHex('#E11D48')->toOklch();

        $this->assertArrayHasKey('l', $oklch);
        $this->assertArrayHasKey('c', $oklch);
        $this->assertArrayHasKey('h', $oklch);
    }

    public function test_it_returns_chroma(): void
    {
        $color = Color::fromHex('#E11D48');

        $this->assertGreaterThanOrEqual(
            0.0,
            $color->chroma(),
        );
    }

    public function test_it_returns_hue(): void
    {
        $color = Color::fromHex('#E11D48');

        $this->assertGreaterThanOrEqual(
            0.0,
            $color->hue(),
        );

        $this->assertLessThan(
            360.0,
            $color->hue(),
        );
    }

    public function test_black_has_zero_luminance(): void
    {
        $this->assertSame(
            0.0,
            Color::fromHex('#000000')->luminance(),
        );
    }

    public function test_white_has_one_luminance(): void
    {
        $this->assertSame(
            1.0,
            Color::fromHex('#ffffff')->luminance(),
        );
    }

    public function test_it_identifies_light_colors(): void
    {
        $this->assertTrue(
            Color::fromHex('#ffffff')->isLight(),
        );
    }

    public function test_it_identifies_dark_colors(): void
    {
        $this->assertTrue(
            Color::fromHex('#000000')->isDark(),
        );
    }

    public function test_it_returns_contrasting_text_color(): void
    {
        $this->assertSame(
            '#ffffff',
            Color::fromHex('#000000')->textColor(),
        );

        $this->assertSame(
            '#000000',
            Color::fromHex('#ffffff')->textColor(),
        );
    }

    public function test_it_returns_rgb_values(): void
    {
        $this->assertSame(
            [
                'red' => 225,
                'green' => 29,
                'blue' => 72,
            ],
            Color::fromHex('#E11D48')->rgb(),
        );
    }

    public function test_it_returns_individual_rgb_values(): void
    {
        $color = Color::fromHex('#E11D48');

        $this->assertSame(225, $color->red());
        $this->assertSame(29, $color->green());
        $this->assertSame(72, $color->blue());
    }

    public function test_it_compares_colors(): void
    {
        $first = Color::fromHex('#E11D48');
        $same = Color::fromRgb(225, 29, 72);
        $different = Color::fromHex('#F97316');

        $this->assertTrue(
            $first->equals($same),
        );

        $this->assertFalse(
            $first->equals($different),
        );
    }

    public function test_it_converts_to_string_as_hex(): void
    {
        $color = Color::fromHex('#E11D48');

        $this->assertSame(
            '#e11d48',
            (string) $color,
        );
    }

    public function test_it_returns_a_palette(): void
    {
        $color = Color::from('#E11D48');

        $this->assertSame(
            $color,
            $color->palette()->source(),
        );
    }

    public function test_it_returns_default_shade(): void
    {
        $palette = Color::from('#999999')->palette();

        $this->assertSame(
            500,
            $palette->defaultShade(),
        );
    }

    public function test_it_generates_all_shades(): void
    {
        $colors = Color::from('#999999')
            ->palette()
            ->all();

        $this->assertCount(11, $colors);

        foreach ([
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
        ] as $shade) {
            $this->assertArrayHasKey(
                $shade,
                $colors,
            );
        }
    }

    public function test_it_preserves_original_color_at_default_shade(): void
    {
        $color = Color::from('#999999');
        $palette = $color->palette();

        $this->assertSame(
            '#999999',
            $palette->get(
                $palette->defaultShade(),
            ),
        );
    }

    public function test_it_generates_consistent_shades_from_oklch_values(): void
    {
        $palette = Color::from('#3B82F6')->palette();

        $this->assertSame(
            '#77abff',
            $palette->get(400),
        );

        $this->assertSame(
            '#003d97',
            $palette->get(800),
        );
    }

    public function test_it_checks_if_shade_exists(): void
    {
        $palette = Color::from('#999999')->palette();

        $this->assertTrue(
            $palette->has(500),
        );

        $this->assertTrue(
            $palette->has(50),
        );

        $this->assertTrue(
            $palette->has(950),
        );

        $this->assertFalse(
            $palette->has(550),
        );
    }

    public function test_it_rejects_invalid_shade(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        Color::from('#999999')
            ->palette()
            ->get(550);
    }
}
