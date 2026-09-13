<?php

declare(strict_types=1);

namespace Ki5an\TailwindShades;

use InvalidArgumentException;
use Ki5an\TailwindShades\Support\ColorSpace;

final class Color
{
    private function __construct(
        private readonly int $red,
        private readonly int $green,
        private readonly int $blue,
    ) {}

    public static function from(string $hex): self
    {
        return self::fromHex($hex);
    }

    public static function fromHex(string $hex): self
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = implode('', array_map(
                static fn (string $value): string => $value.$value,
                str_split($hex),
            ));
        }

        if (
            strlen($hex) !== 6
            || ! ctype_xdigit($hex)
        ) {
            throw new InvalidArgumentException(
                "Invalid HEX color: #{$hex}.",
            );
        }

        return new self(
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        );
    }

    public static function fromRgb(
        int $red,
        int $green,
        int $blue,
    ): self {
        foreach ([
            'red' => $red,
            'green' => $green,
            'blue' => $blue,
        ] as $name => $value) {
            if ($value < 0 || $value > 255) {
                throw new InvalidArgumentException(
                    ucfirst($name).' must be between 0 and 255.',
                );
            }
        }

        return new self(
            $red,
            $green,
            $blue,
        );
    }

    public function red(): int
    {
        return $this->red;
    }

    public function green(): int
    {
        return $this->green;
    }

    public function blue(): int
    {
        return $this->blue;
    }

    public function hex(): string
    {
        return sprintf(
            '#%02x%02x%02x',
            $this->red,
            $this->green,
            $this->blue,
        );
    }

    /**
     * @return array{
     *     red: int,
     *     green: int,
     *     blue: int
     * }
     */
    public function rgb(): array
    {
        return [
            'red' => $this->red,
            'green' => $this->green,
            'blue' => $this->blue,
        ];
    }

    public function palette(): Palette
    {
        return Palette::from($this);
    }

    public function textColor(): string
    {
        return Contrast::textColor($this);
    }

    public function contrast(Color $background): float
    {
        return Contrast::ratio(
            $this,
            $background,
        );
    }

    public function luminance(): float
    {
        return ColorSpace::luminance($this);
    }

    /**
     * Return HSL lightness.
     */
    public function lightness(): float
    {
        return ColorSpace::lightness($this);
    }

    /**
     * Return HSL saturation.
     */
    public function saturation(): float
    {
        return ColorSpace::saturation($this);
    }

    /**
     * Return OKLCH chroma.
     */
    public function chroma(): float
    {
        return ColorSpace::chroma($this);
    }

    /**
     * Return OKLCH hue in degrees.
     */
    public function hue(): float
    {
        return ColorSpace::hue($this);
    }

    public function isLight(): bool
    {
        return $this->luminance() > 0.5;
    }

    public function isDark(): bool
    {
        return ! $this->isLight();
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
    public function toOklab(): array
    {
        return ColorSpace::toOklab($this);
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
    public function toOklch(): array
    {
        return ColorSpace::toOklch($this);
    }

    public function equals(Color $color): bool
    {
        return $this->red === $color->red
            && $this->green === $color->green
            && $this->blue === $color->blue;
    }

    public function __toString(): string
    {
        return $this->hex();
    }
}