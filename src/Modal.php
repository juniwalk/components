<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Components;

use Nette\ComponentModel\IComponent;

interface Modal extends IComponent
{
	public function setModalOpen(bool $open): void;
	public function renderModal(bool $keyboard = false, bool|string $backdrop = 'static'): void;
}
