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
use JuniWalk\Utils\Interfaces\TokenProvider;
use JuniWalk\Utils\Strings;
use JuniWalk\Utils\Traits\Events;
use JuniWalk\Utils\Traits\RedirectAjaxHandler;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Link;
use Nette\Bridges\ApplicationLatte\DefaultTemplate;
use Nette\Security\IIdentity as Identity;
use Tracy\Debugger;
use Throwable;

/**
 * @phpstan-import-type LinkArgs from LinkProvider
 */
class DocumentPreview extends Control implements EventHandler, Modal, LinkProvider
{
	use Actions, Links, Events, RedirectAjaxHandler;

	private string $title;
	private string|Link $frameUrl;
	private bool $isModalOpen = false;

	public function __construct(
		private readonly GoogleChrome $googleChrome,
		private readonly ?Identity $user = null,
	) {
		$this->watch('download');
		$this->watch('render');
		$this->watch('print');
	}


	public function setTitle(string $title): void
	{
		$this->title = $title;
	}


	/**
	 * @param LinkArgs $args
	 */
	public function setFrameUrl(string|Link $frameUrl, array $args = []): void
	{
		$this->frameUrl = $frameUrl;
		$this->setLinkArgs($args);
	}


	public function setModalOpen(bool $open): void
	{
		$this->isModalOpen = $open;
	}


	public function handlePrint(): void
	{
		$this->trigger('print', $this, $this->getParameters());
		$this->redirect('this');
	}


	/**
	 * @throws BadRequestException
	 */
	public function handleDownload(?string $fileName): void
	{
		$params = $this->getParameters();
		$parent = $this->getPresenter();

		if (!$parent instanceof TokenProvider) {
			throw new BadRequestException('Presenter needs to implement '.TokenProvider::class);
		}

		try {
			$token = $parent->createToken($this->frameUrl, $params, $this->user);
			$frameUrl = $this->createLink($this->frameUrl, $params, true);
			$frameUrl->setParameter('token', $token);

			$fileName ??= Strings::webalize($frameUrl->getDestination());
			$file = $this->googleChrome->downloadPdf($frameUrl, $fileName);

			$this->trigger('download', $this, $params);

			$parent->sendResponse($file);

		} catch (AbortException $e) {
			throw $e;

		} catch (Throwable $e) {
			$parent->flashMessage('web.message.something-went-wrong', 'danger');
			Debugger::log($e);

		} finally {
			$parent->clearToken($token ?? null);
		}

		$parent->redrawControl('controls');
		$parent->redrawControl('modals');
		$parent->redirect('this');
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

		$template->add('frameUrl', $this->createLink($this->frameUrl));
		$template->add('printUrl', $this->createLink('print!'));
		$template->add('actions', $this->getActions());
		$template->add('title', $this->title);

		if (defined('IframeResizeLicence')) {
			$template->add('license', IframeResizeLicence);
		}

		$template->render();
	}
}
