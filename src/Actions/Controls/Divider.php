<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Components\Actions\Controls;

use JuniWalk\Components\Actions\Action;
use JuniWalk\Utils\Html;
use Nette\Application\UI\Control as UIControl;

class Divider extends UIControl implements Action
{
	private Html $control;

	public function __construct(
		private string $name,
	) {
		$this->control = Html::el('div class="dropdown-divider"');
		$this->setParent(null, $this->name);
	}


	public function create(): Html
	{
		return clone $this->control;
	}


	public function render(): void
	{
		echo $this->create()->render();
	}
}
