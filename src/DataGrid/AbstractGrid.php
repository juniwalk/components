<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Components\DataGrid;

use Contributte\Translation\Wrappers\Message;
use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Html;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator as Translator;
use Nette\Utils\Html as NetteHtml;
// use Nette\Localization\Translator;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\Column\ColumnDateTime;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\Row;
use UnexpectedValueException;
use Stringable;

abstract class AbstractGrid extends Control
{
	protected const TemplatesDir = __DIR__.'/templates';

	protected DataGrid $grid;
	protected Translator $translator;
	protected bool $hasFiltersAlwaysShown = true;
	protected bool $hasColumnsFixedWidth = false;
	protected bool $isDisabled = false;
	protected Stringable|string|null $title = null;
	protected ?string $help = null;


	public function setDisabled(bool $disabled = true): void
	{
		$this->isDisabled = $disabled;
	}


	public function isDisabled(): bool
	{
		return $this->isDisabled;
	}


	public function setFiltersAlwaysShown(bool $filtersAlwaysShown = true): void
	{
		$this->hasFiltersAlwaysShown = $filtersAlwaysShown;
	}


	public function hasFiltersAlwaysShown(): bool
	{
		return $this->hasFiltersAlwaysShown;
	}


	public function setColumnsFixedWidth(bool $columnsFixedWidth = true): void
	{
		$this->hasColumnsFixedWidth = $columnsFixedWidth;
	}


	public function hasColumnsFixedWidth(): bool
	{
		return $this->hasColumnsFixedWidth;
	}


	/**
	 * @param mixed[] $params
	 */
	public function setTitle(Stringable|string|null $title, ?array $params = null): void
	{
		if ($title && !is_null($params) && class_exists(Message::class)) {
			$title = new Message((string) $title, $params);
		}

		$this->title = $title;
	}


	public function setHelp(?string $help): void
	{
		$this->help = $help;
	}


	public function getHelp(): ?string
	{
		return $this->help;
	}


	public function setTranslator(Translator $translator): void
	{
		$this->translator = $translator;
	}


	public function getTranslator(): ?Translator
	{
		return $this->translator ?? null;
	}


	public function getRow(mixed $item): Row
	{
		return new Row($this->grid, $item, $this->grid->getPrimaryKey());
	}


	/**
	 * @param  class-string<LabeledEnum> $enum
	 * @throws UnexpectedValueException
	 */
	public function addColumnEnum(string $name, string $title, string $enum, bool $blockButtons = false): Column
	{
		if (!enum_exists($enum) || !is_a($enum, LabeledEnum::class, true)) {
			throw new UnexpectedValueException('$enum has to implement '.LabeledEnum::class);
		}

		$signalMethod = $this->formatSignalMethod($name);
		$class = $blockButtons ? 'btn-block text-right' : null;

		if (!method_exists($this, $signalMethod)) {
			return $this->grid->addColumnText($name, $title)->setAlign('right')
				->setRenderer(function($item) use ($name, $enum, $class): ?Html {
					if (!$value = $this->getRow($item)->getValue($name)) {
						return null;
					}

					/** @var int|string|LabeledEnum|null $value */
					return Html::badgeEnum($enum::make($value))		// @phpstan-ignore return.type
						->addClass($class);
				});
		}

		$column = $this->grid->addColumnStatus($name, $title)->setAlign('right');
		$column->setTemplate(static::TemplatesDir.'/datagrid_column_status.latte');
		$column->onChange[] = fn($id, $value) => $this->$signalMethod((int) $id, $enum::make($value));

		foreach ($enum::cases() as $item) {
			$color = ($item->color() ?? Color::Secondary)->for('btn');	// @phpstan-ignore-line v3.0 utils had color() method return null.
			$option = $column->addOption($item->value, $item->label())
				->setClass($class.' '.$color);

			if ($icon = $item->icon()) {
				$option->setIcon($icon)->setIconSecondary($icon);
			}

			$option->endOption();
		}

		return $column;
	}


	/**
	 * @param array<string, scalar> $filter
	 */
	final public function setFilter(array $filter): void
	{
		$this->getComponent('grid')->setFilter($filter);
	}


	final public function redrawGrid(): void
	{
		$this->getComponent('grid')->redrawControl();
	}


	final public function redrawItem(int|string $id): void
	{
		$this->getComponent('grid')->redrawItem($id);
	}


	final public function render(): void
	{
		$grid = $this->getComponent('grid');

		foreach ($grid->getColumns() as $column) {
			if (!$this->hasColumnsFixedWidth && !$column instanceof ColumnDateTime) {
				continue;
			}

			$column->addCellAttributes(['class' => 'text-nowrap']);
		}

		$gridTemplate = $grid->getTemplate();
		$gridTemplate->controlName = $this->getName();
		$gridTemplate->hasFiltersAlwaysShown = $this->hasFiltersAlwaysShown;
		$gridTemplate->isDisabled = $this->isDisabled;
		$gridTemplate->title = $this->title;
		$gridTemplate->help = $this->help;

		$template = $this->getTemplate();
		$template->setFile(static::TemplatesDir.'/datagrid-wrapper.latte');
		$template->render();
	}


	protected function createModel(): mixed
	{
		return [];
	}


	abstract protected function createComponentGrid(): DataGrid;


	/**
	 * @param mixed[] $items
	 */
	protected function onDataLoaded(array $items): void {}
	protected function onRowRender(mixed $item, NetteHtml $html): void {}


	final protected function createDataGrid(bool $rememberState = true, ?string $primaryKey = null): DataGrid
	{
		$grid = $this->grid = new DataGrid;
		$grid->setRememberState($rememberState);
		$grid->setRefreshUrl(!$rememberState);
		$grid->setCustomPaginatorTemplate(static::TemplatesDir.'/datagrid_paginator.latte');
		$grid->setTemplateFile(static::TemplatesDir.'/datagrid.latte');
		$grid->setItemsPerPageList([10, 20, 50], false);
		$grid->setDefaultPerPage(20);

		if (isset($primaryKey)) {
			$grid->setPrimaryKey($primaryKey);
		}

		$grid->setDataSource($this->createModel() ?? []);
		$grid->setStrictSessionFilterValues(false);
		$grid->setOuterFilterRendering(true);
		$grid->setOuterFilterColumnsCount(3);

		if ($this->translator instanceof Translator) {
			$grid->setTranslator($this->translator);
		}

		DataGrid::$iconPrefix = 'fas fa-fw fa-';

		if (($dataSource = $grid->getDataSource()) instanceof DoctrineDataSource) {
			$dataSource->onDataLoaded[] = $this->onDataLoaded(...);
		}

		$grid->setRowCallback($this->onRowRender(...));

		return $grid;
	}


	/**
	 * @param array<string, mixed> $params
	 */
	protected function translate(Stringable|string|null $message, array $params = []): Stringable|string
	{
		if (!$message || !isset($this->translator)) {
			return $message ?? '';
		}

		return $this->translator->translate($message, $params);
	}
}
