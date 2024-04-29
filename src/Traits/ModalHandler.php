<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Components\Traits;

use JuniWalk\Components\Modal;
use JuniWalk\Utils\Interfaces\EventHandler;
use Nette\InvalidArgumentException;

trait ModalHandler
{
	/**
	 * @throws InvalidArgumentException
	 */
	public function openModal(Modal|string $modal, array $params = []): void
	{
		if (is_string($modal) && !str_starts_with($modal, '#')) {
			$modal = $this->getComponent($modal, true);
		}

		if ($modal instanceof Modal) {
			if ($modal instanceof EventHandler && $modal->isWatched('render')) {
				$modal->when('render', fn($m, $t) => $t->setParameters($params));
				$params = [];
			}

			$modal->setModalOpen(true);
			$modal = '#'.$modal->getName();
		}

		$template = $this->getTemplate();
		$template->add('openModal', $modal);
		$template->setParameters($params);

		$this->redrawControl('modals');
		$this->redirect('this');
	}
}
