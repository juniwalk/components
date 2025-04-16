<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Components\Actions\Traits;

use JuniWalk\Components\Actions\LinkProvider;	// ! Used for @phpstan
use Nette\Application\UI\Component;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Link;

/**
 * @phpstan-import-type LinkArgs from LinkProvider
 */
trait Links
{
	/** @var LinkArgs */
	private array $linkArgs = [];

	/**
	 * @param LinkArgs $args
	 */
	public function setLinkArgs(array $args): void
	{
		$this->linkArgs = $args;
	}


	/**
	 * @return LinkArgs
	 */
	public function getLinkArgs(): array
	{
		return $this->linkArgs;
	}


	/**
	 * @param  LinkArgs $args
	 * @throws InvalidLinkException
	 */
	public function createLink(Link|string $dest, array $args = []): Link|string
	{
		if ($dest instanceof Link ||
			str_starts_with($dest, '#') ||
			str_starts_with($dest, 'javascript:')) {
			return $dest;
		}

		$presenter = $this->getPresenter();
		$args = array_merge($args, $this->linkArgs);	// ? Maybe $args should take precedence?

		if (str_contains($dest, ':')) {
			return $presenter->link($dest, $args);
		}

		$invalidLinkMode = $presenter->invalidLinkMode;
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
				$presenter->invalidLinkMode = $invalidLinkMode;
			}

		} while ($component = $component->getParent());

		throw new InvalidLinkException;
	}
}
