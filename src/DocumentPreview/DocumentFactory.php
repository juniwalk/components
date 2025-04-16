<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Components\DocumentPreview;

interface DocumentFactory
{
	public function create(): DocumentPreview;
}
