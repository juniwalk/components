<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Components;

use JuniWalk\Utils\Enums\Color;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\ImageType;

class Avatar
{
	private Image $image;

	/**
	 * @param int $size
	 */
	public function __construct(
		private int $size,
		private ?ImageColor $background = null,
		private ?ImageColor $foreground = null,
		private ?string $font = null,
	) {
		if ($size < 1) {
			throw new \Exception;
		}

		$this->image = Image::fromBlank($size, $size, $background);
	}


	public function setBackground(Color|string $background): void
	{
		if ($background instanceof Color) {
			$background = $background->hex();
		}

		$this->background = ImageColor::hex($background);
	}


	public function setForeground(Color|string $foreground): void
	{
		if ($foreground instanceof Color) {
			$foreground = $foreground->hex();
		}

		$this->foreground = ImageColor::hex($foreground);
	}


	public function create(string $text, float $size = .35): void
	{
		$this->background ??= self::colorFromSeed($text);
		$this->foreground ??= self::colorForText($this->background);

		$text = preg_replace('/[^A-ZĚŠČŘŽÝÁÍÉÚŮ]+/u', '', $text);
		$text = mb_substr($text ?? '', 0, 2);

		$this->image->filledRectangleWH(0, 0, $this->size, $this->size, $this->background);

		if (!$text || !isset($this->font)) {
			return;
		}

		$box = Image::calculateTextBox($text, $this->font, $size * $this->size);
		$y = ($this->size - $box['height']) / 2 + $box['height'];
		$x = ($this->size - $box['width']) / 2;

		$this->image->ttfText($size * $this->size, 0, (int) $x, (int) $y, $this->foreground, $this->font, $text);
	}


	/**
	 * @param ImageType::*  $type
	 */
	public function send(int $type): void
	{
		$this->image->send($type);
	}


	/**
	 * @param ImageType::*  $type
	 */
	public function toString(int $type): string
	{
		return $this->image->toString($type);
	}


	private static function colorFromSeed(string $seed): ImageColor
	{
		return ImageColor::hex(substr(sha1($seed), 6, 6));
	}


	private static function colorForText(ImageColor $background, string $light = 'f8f9fa', string $dark = '212529'): ImageColor
	{
		return ImageColor::hex(
			self::luminosity($background) > 0.5
				? $dark
				: $light
		);
	}


	private static function luminosity(ImageColor $color): float
	{
		static $mod = [0.2126, 0.7152, 0.0722, 0];
		$sum = 0;

		foreach ($color->toRGBA() as $i => $channel) {
			$sum += $channel / 255 * $mod[$i];
		}

		return $sum;
	}
}
