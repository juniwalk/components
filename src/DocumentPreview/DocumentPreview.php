<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Components\DocumentPreview;

use JuniWalk\Components\Actions\LinkProvider;
use JuniWalk\Components\Actions\Traits\Actions;
use JuniWalk\Components\Actions\Traits\Links;
use JuniWalk\Utils\Format;
use JuniWalk\Utils\GoogleChrome;
use JuniWalk\Utils\Interfaces\EventHandler;
use JuniWalk\Utils\Interfaces\Modal;
use JuniWalk\Utils\Strings;
use JuniWalk\Utils\Traits\Events;
use JuniWalk\Utils\Traits\RedirectAjaxHandler;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Control;
use Nette\Application\UI\Link;
use Nette\Bridges\ApplicationLatte\DefaultTemplate;
use Throwable;

/**
 * @phpstan-import-type LinkArgs from LinkProvider
 */
class DocumentPreview extends Control implements EventHandler, Modal, LinkProvider
{
	use Actions, Links, Events, RedirectAjaxHandler;

	private string $title;
	private string $icon;
	private string|Link $frameUrl;
	private bool $isModalOpen = false;

	public function __construct(
		private readonly GoogleChrome $googleChrome,
	) {
		$this->watch('download');
		$this->watch('render');
		$this->watch('print');
	}


	public function setTitle(string $title): void
	{
		$this->title = $title;
	}


	public function setIcon(string $icon): void
	{
		$this->icon = $icon;
	}


	/**
	 * @param LinkArgs $params
	 */
	public function setFrameUrl(string|Link $frameUrl, array $params = []): void
	{
		$this->frameUrl = $frameUrl;
		$this->setLinkArgs($params);
	}


	public function setModalOpen(bool $open): void
	{
		$this->isModalOpen = $open;
	}


	public function handlePrint(): void
	{
		$this->trigger('print', $this);
		$this->redirect('this');
	}


	/**
	 * @throws BadRequestException
	 */
	public function handleDownload(): void
	{
		if (!isset($this->frameUrl)) {
			throw new BadRequestException('FrameUrl is not set.');
		}

		$presenter = $this->getPresenter();
		$fileName = Strings::webalize(
			$presenter->getAction(true)
		);

		try {
			$frameUrl = $this->createFrameUrl();
			$file = new FileResponse(
				$this->googleChrome->covert($frameUrl),
				$this->params['file'] ?? $fileName,
				'application/pdf',
			);

			$this->trigger('download', $this);

		} catch (Throwable $e) {
			throw new BadRequestException('Failed to create PDF file.', previous: $e);
		}

		$presenter->sendResponse($file);
	}


	public function renderModal(bool $keyboard = false, bool|string $backdrop = 'static'): void
	{
		if (!$this->isModalOpen) {
			return;
		}

		$this->when('render', fn($x, $y) => $y->setParameters([
			'modalOptions' => [
				'data-backdrop' => Format::stringify($backdrop),
				'data-keyboard' => Format::stringify($keyboard),
			],
		]));

		$this->render();
	}


	public function render(): void
	{
		if (!$this->isModalOpen || !isset($this->frameUrl)) {
			return;
		}

		/** @var DefaultTemplate */
		$template = $this->getTemplate();
		$template->setFile(__DIR__.'/templates/default.latte');
		$template->add('modalOptions', []);

		$this->trigger('render', $this, $template);

		$template->add('frameUrl', $this->createFrameUrl());
		$template->add('actions', $this->getActions());
		$template->add('title', $this->title);
		$template->add('icon', $this->icon);

		if (defined('IframeResizeLicence')) {
			$template->add('license', IframeResizeLicence);
		}

		$template->render();
	}


	private function createFrameUrl(): string|Link
	{
		return $this->createLink($this->frameUrl, $this->params ?: $this->getLinkArgs());
	}
}
