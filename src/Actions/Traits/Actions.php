<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Components\Actions\Traits;

use JuniWalk\Components\Actions\Action;
use JuniWalk\Components\Actions\Component;
use JuniWalk\Components\Actions\LinkProvider;
use JuniWalk\Components\Actions\Controls\Button;
use JuniWalk\Components\Actions\Controls\Divider;
use JuniWalk\Components\Actions\Controls\Dropdown;
use JuniWalk\Components\Actions\Controls\Group;
use JuniWalk\Utils\Strings;
use Nette\Application\UI\Link;
use Nette\Application\UI\Presenter;
use Nette\Utils\Random;
use Stringable;

/**
 * @phpstan-import-type LinkArgs from LinkProvider
 */
trait Actions
{
	public function addGroup(?string $name = null): Group
	{
		$action = new Group($name ?? Random::generate(6));
		return $this->addAction($action);
	}


	/**
	 * @param LinkArgs $args
	 */
	public function addButton(string $name, Stringable|string|null $label = null, Link|string|null $dest = null, array $args = []): Button
	{
		$action = new Button($name, $label);
		$action->monitor(Presenter::class, function() use ($action, $name, $dest, $args) {
			/** @var LinkProvider */
			$linkProvider = $action->lookup(LinkProvider::class);
			$action->setLink($linkProvider->createLink($dest ?? $name, $args));
		});

		return $this->addAction($action);
	}


	public function addDropdown(string $name, Stringable|string|null $label = null): Dropdown
	{
		$action = new Dropdown($name, $label);
		return $this->addAction($action);
	}


	public function addDivider(?string $name = null): Divider
	{
		$action = new Divider($name ?? Random::generate(6));
		return $this->addAction($action);
	}


	/**
	 * @template T of Action
	 * @param  T $action
	 * @return T
	 */
	public function addAction(Action $action): Action
	{
		if ($action instanceof Component) {
			$action->monitor(Presenter::class, fn($parent) => $action->setTranslator($parent->getTranslator()));
		}

		$this->addComponent($action, null);
		return $action;
	}


	public function findAction(string $name): ?Action
	{
		return $this->getComponent(Strings::webalize($name), false);
	}


	public function getAction(string $name): Action
	{
		return $this->getComponent(Strings::webalize($name));
	}


	/**
	 * @return array<int|string, Action>
	 */
	public function getActions(): iterable
	{
		/** @var array<int|string, Action> */
		return $this->getComponents(false, Action::class);
	}


	public function removeAction(string $name): ?Action
	{
		if ($action = $this->findAction($name)) {
			$this->removeComponent($action);
		}

		return $action;
	}
}
