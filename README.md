# Tailwind Shades

A PHP color library for creating Tailwind-style color palettes from a single color.

Tailwind Shades analyzes a source color, determines its closest shade, and generates a complete palette while preserving the original color as its anchor.

```php
use Ki5an\TailwindShades\Color;

$color = Color::from('#E11D48');

$palette = $color->palette();

$palette->all();
```

## Installation

Install Tailwind Shades with Composer:

```bash
composer require ki5an/tailwind-shades
```

## Usage

### Create a Color

Create a color from HEX:

```php
$color = Color::from('#E11D48');
```

Short HEX values are supported:

```php
$color = Color::from('#fff');
```

You can also create a color from RGB values:

```php
$color = Color::fromRgb(225, 29, 72);
```

### Generate a Palette

Generate a complete palette from the source color:

```php
$palette = $color->palette();
```

Tailwind Shades generates the following shades:

```text
50
100
200
300
400
500
600
700
800
900
950
```

Get all shades:

```php
$palette->all();
```

Or retrieve an individual shade:

```php
$palette->get(500);
```

### Default Shade

The source color does not have to be `500`.

Tailwind Shades analyzes the color and determines the closest shade automatically:

```php
$palette->defaultShade();

// 600
```

The original color is preserved at that position:

```php
$palette->get(
    $palette->defaultShade(),
);

// #e11d48
```

This allows you to start with any brand color and build a complete palette around it without changing the original color.

## Color Analysis

Tailwind Shades provides access to common color properties.

```php
$color->red();
$color->green();
$color->blue();

$color->rgb();
```

You can also inspect luminance, lightness, saturation, chroma, and hue:

```php
$color->luminance();
$color->lightness();
$color->saturation();
$color->chroma();
$color->hue();
```

Convert a color to OKLab or OKLCH:

```php
$color->toOklab();

$color->toOklch();
```

## Contrast

Calculate the contrast ratio between two colors:

```php
$foreground = Color::from('#ffffff');
$background = Color::from('#000000');

$foreground->contrast($background);

// 21
```

Find a suitable black or white text color for a background:

```php
$background->textColor();

// #ffffff
```

WCAG contrast thresholds are available through `Wcag`:

```php
Wcag::AA_TEXT;
Wcag::AA_LARGE_TEXT;
Wcag::AAA_TEXT;
Wcag::AAA_LARGE_TEXT;
```

## How It Works

Tailwind Shades uses **OKLCH** for palette generation.

The source color provides the hue and chroma, while each shade is generated using a target lightness. The source color is kept unchanged at its closest shade.

When a generated color falls outside the sRGB gamut, its chroma is reduced until a valid sRGB color can be produced.

This creates a smoother and more predictable light-to-dark progression than simply mixing the source color with white or black.

## Features

* HEX and RGB color creation
* Short HEX support
* Tailwind-style `50` to `950` palettes
* Automatic source shade detection
* Original color preservation
* OKLab and OKLCH support
* WCAG contrast calculations
* Luminance and color analysis
* Light and dark color detection
* Color comparison
* sRGB gamut handling
* No framework dependency

## Development

Clone the repository and install dependencies:

```bash
git clone https://github.com/ki5an/tailwind-shades.git

cd tailwind-shades

composer install
```

Run the test suite:

```bash
vendor/bin/phpunit
```

## License

Tailwind Shades is open-sourced software licensed under the [MIT License](LICENSE).

## Support

Made with ❤️ for the PHP community.

If Tailwind Shades is useful to you, consider supporting its development.

[❤️ Sponsor the project](https://github.com/sponsors/ki5an)