<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Components\Enums;

use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Enums\Traits\Labeled;

enum Theme: string implements LabeledEnum
{
	use Labeled;

	case Auto = 'auto';
	case Dark = 'dark';
	case Light = 'light';


	public function label(): string
	{
		return match ($this) {
			self::Auto => 'enum.theme.auto',
			self::Dark => 'enum.theme.dark',
			self::Light => 'enum.theme.light',
		};
	}


	public function icon(): string
	{
		return match ($this) {
			self::Auto => 'fa-circle-half-stroke',
			self::Dark => 'fa-moon',
			self::Light => 'fa-sun',
		};
	}
}
