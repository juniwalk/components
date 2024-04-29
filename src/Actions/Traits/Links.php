<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Components\Actions\Traits;

use Nette\Application\UI\Component;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Link;

trait Links
{
	private array $linkArgs = [];

	public function setLinkArgs(array $args): void
	{
		$this->linkArgs = $args;
	}


	public function getLinkArgs(): array
	{
		return $this->linkArgs;
	}


	/**
	 * @throws InvalidLinkException
	 */
	public function createLink(Link|string $dest, array $args = []): Link|string
	{
		$args = array_merge($args, $this->linkArgs);

		if (str_starts_with($dest, 'javascript:') ||
			str_starts_with($dest, '#') ||
			$dest instanceof Link) {
			return $dest;
		}

		$presenter = $this->getPresenter();
		$linkMode = $presenter->invalidLinkMode;

		if (str_contains($dest, ':')) {
			return $presenter->link($dest, $args);
		}

		$presenter->invalidLinkMode = $presenter::InvalidLinkException;
		$component = $this;

		do {
			try {
				if (!$component instanceof Component) {
					break;
				}

				return $component->link($dest, $args);

			} catch (InvalidLinkException) {
				continue;

			} finally {
				$presenter->invalidLinkMode = $linkMode;
			}

		} while ($component = $component->getParent());

		throw new InvalidLinkException;
	}
}
