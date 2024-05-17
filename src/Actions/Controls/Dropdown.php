<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Components\Actions\Controls;

use JuniWalk\Components\Actions\Action;
use JuniWalk\Components\Actions\Component;
use JuniWalk\Components\Actions\Traits\Actions;
use JuniWalk\Components\Actions\Traits\Control;
use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Html;
use JuniWalk\Utils\Strings;
use Nette\Application\UI\Control as UIControl;
use Nette\ComponentModel\IComponent;
use Nette\InvalidStateException;
use Stringable;

class Dropdown extends UIControl implements Action, Component
{
	use Actions, Control;

	private Button $btn;

	public function __construct(
		private string $name,
		Stringable|string|null $label = null,
	) {
		$this->name = Strings::webalize($name);
		$this->control = Html::el('div');
		$this->btn = new Button('_btn', $label);

		$this->setParent(null, $this->name);
		$this->addAction($this->btn);
	}


	public function setTitle(?string $title): static
	{
		$this->btn->setTitle($title);
		return $this;
	}


	public function getTitle(): ?string
	{
		return $this->btn->getTitle();
	}


	public function setIcon(?string $icon, bool $fixedWidth = true, ?Color $color = null): static
	{
		$this->btn->setIcon($icon, $fixedWidth, $color);
		return $this;
	}


	public function getIcon(): ?Html
	{
		return $this->btn->getIcon();
	}


	public function hasClass(string $name): bool
	{
		$class = $this->btn->getClass();

		if (is_array($class)) {
			$class = array_keys($class);
			$class = implode(' ', $class);
		}

		return str_contains($class, $name);
	}


	public function addClass(string $class): static
	{
		$this->btn->addClass($class);
		return $this;
	}


	public function setClass(string $class): static
	{
		$this->btn->setClass($class);
		return $this;
	}


	/**
	 * @return string|array<string, bool>
	 */
	public function getClass(): string|array
	{
		return $this->btn->getClass();
	}


	/**
	 * @param array<string, scalar> $attributes
	 */
	public function addAttributes(array $attributes): static
	{
		$this->btn->addAttributes($attributes);
		return $this;
	}


	public function getControl(): Html
	{
		return $this->btn->getControl();
	}


	public function addMenuClass(string $class): static
	{
		$this->control->addClass($class);
		return $this;
	}


	public function create(): Html
	{
		$button = $this->getControl()
			->addClass('dropdown-toggle')
			->data('toggle', 'dropdown');

		$this->control->addClass('dropdown-menu');

		foreach ($this->getActions() as $action) {
			if ($action === $this->btn) {
				continue;
			}

			$element = clone $action->create();

			if ($action instanceof Button) {
				$element->setClass('dropdown-item');
			}

			if ($action instanceof Component) {
				if ($action->hasClass('active')) {
					$element->addClass('active');
				}
	
				if ($action->hasClass('ajax')) {
					$element->addClass('ajax');
				}
			}

			$this->control->addHtml($element);
		}

		return Html::el('div class="btn-group" role="group"')
			->addHtml($button)->addHtml($this->control);
	}


	public function render(): void
	{
		echo $this->create()->render();
	}


	/**
	 * @throws InvalidStateException
	 */
	protected function validateChildComponent(IComponent $child): void
	{
		parent::validateChildComponent($child);

		if (!$child instanceof Group) {
			return;
		}

		throw new InvalidStateException($child::class.' is not allowed inside '.$this::class);
	}
}
