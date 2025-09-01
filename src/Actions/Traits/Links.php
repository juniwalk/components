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
	 * @return ($lazy is true ? Link : Link|string)
	 * @throws InvalidLinkException
	 */
	public function createLink(Link|string $dest, array $args = [], bool $lazy = false): Link|string
	{
		if ($dest instanceof Link ||
			str_starts_with($dest, '#') ||
			str_starts_with($dest, 'javascript:')) {
			// ! Will return string for javascript links in lazy mode
			return $dest;
		}

		$presenter = $this->getPresenter();
		$method = $lazy ? 'lazyLink' : 'link';
		$args = array_merge($args, $this->linkArgs);	// ? Maybe $args should take precedence?

		if (str_contains($dest, ':')) {
			return $presenter->$method($dest, $args);
		}

		$invalidLinkMode = $presenter->invalidLinkMode;
		$component = $this;

		do {
			$presenter->invalidLinkMode = $presenter::InvalidLinkException;

			if (!method_exists($component, $method)) {
				continue;
			}

			try {
				return $component->$method($dest, $args);

			} catch (InvalidLinkException) {
				continue;

			} finally {
				$presenter->invalidLinkMode = $invalidLinkMode;
			}

		} while ($component = $component->getParent());

		throw new InvalidLinkException;
	}
}
