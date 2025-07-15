<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Components\Actions;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Html;
use Nette\Localization\Translator;

interface Component
{
	public function setTranslator(?Translator $translator): void;
	public function setTitle(?string $title): static;
	public function getTitle(): ?string;
	public function setIcon(string $icon, bool $fixedWidth = true, ?Color $color = null): static;
	public function getIcon(): ?Html;
	public function hasClass(string $name): bool;
	public function addClass(string $class): static;
	public function setClass(string $class): static;

	/**
	 * @return string|array<string, bool>
	 */
	public function getClass(): string|array;

	/**
	 * @param array<string, scalar> $attributes
	 */
	public function addAttributes(array $attributes): static;
}
