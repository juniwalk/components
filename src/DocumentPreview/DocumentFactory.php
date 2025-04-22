<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Components\DocumentPreview;

use Nette\Security\IIdentity as Identity;

interface DocumentFactory
{
	public function create(?Identity $user = null): DocumentPreview;
}
