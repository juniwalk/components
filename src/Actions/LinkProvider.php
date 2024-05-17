<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Components\Actions;

use Nette\Application\UI\Link;

/**
 * @phpstan-type LinkArgs array<string, mixed>
 */
interface LinkProvider
{
	/**
	 * @param LinkArgs $args
	 */
	public function createLink(Link|string $dest, array $args = []): Link|string;
}
