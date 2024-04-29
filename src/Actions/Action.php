<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Components\Actions;

use JuniWalk\Utils\Html;
use Nette\ComponentModel\IComponent;

interface Action extends IComponent
{
	public function create(): Html;
	public function render(): void;

	/**
	 * ComponentModel methods
	 */
	public function lookup(?string $type, bool $throw = true): ?IComponent;
	public function lookupPath(?string $type = null, bool $throw = true): ?string;
	public function monitor(string $type, ?callable $attached = null, ?callable $detached = null): void;
	public function unmonitor(string $type): void;
}
