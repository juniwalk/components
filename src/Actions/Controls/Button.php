<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Components\Actions\Controls;

use JuniWalk\Components\Actions\Action;
use JuniWalk\Components\Actions\Component;
use JuniWalk\Components\Actions\Traits\Control;
use JuniWalk\Utils\Html;
use JuniWalk\Utils\Strings;
use Nette\Application\UI\Control as UIControl;
use Nette\Application\UI\Link;
use Stringable;

class Button extends UIControl implements Action, Component
{
	use Control;

	private Link|string $link;

	public function __construct(
		private string $name,
		private Stringable|string|null $label = null,
	) {
		$this->name = Strings::webalize($name);
		$this->control = Html::el('a');

		$this->setParent(null, $this->name);
	}


	public function setLink(Link|string $link): static
	{
		$this->link = $link;
		return $this;
	}


	public function create(): Html
	{
		$label = (string) ($this->translator?->translate($this->label ?? '') ?? $this->label);
		/** @var Html */
		return $this->getControl()->addHtml($label)->setHref($this->link ?? '#');
	}


	public function render(): void
	{
		echo $this->create()->render();
	}
}
