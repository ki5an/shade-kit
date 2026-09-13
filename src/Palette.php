<?php

declare(strict_types=1);

namespace Ki5an\TailwindShades;

use InvalidArgumentException;
use Ki5an\TailwindShades\Support\PaletteGenerator;

final class Palette
{
    /**
     * @param array<int, string> $colors
     */
    private function __construct(
        private readonly Color $source,
        private readonly array $colors,
    ) {}

    public static function from(Color $color): self
    {
        return new self(
            $color,
            PaletteGenerator::generate($color),
        );
    }

    public function source(): Color
    {
        return $this->source;
    }

    public function defaultShade(): int
    {
        return PaletteGenerator::classify($this->source);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(int $shade): string
    {
        if (! $this->has($shade)) {
            throw new InvalidArgumentException(
                "Invalid color shade: {$shade}.",
            );
        }

        return $this->colors[$shade];
    }

    public function has(int $shade): bool
    {
        return array_key_exists(
            $shade,
            $this->colors,
        );
    }

    /**
     * @return array<int, string>
     */
    public function all(): array
    {
        return $this->colors;
    }
}