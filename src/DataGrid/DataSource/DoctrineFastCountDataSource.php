<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2021
 * @license   MIT License
 */

namespace JuniWalk\Components\DataGrid\DataSource;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\DataSource\DoctrineDataSource;
use Doctrine\DBAL\Exception as DBALException;

/**
 * @inheritDoc
 */
final class DoctrineFastCountDataSource extends DoctrineDataSource
{
	// private DataGrid $datagrid;

	public function setDataGrid(DataGrid $datagrid): void
	{
		// $this->datagrid = $datagrid;
	}


	public function getCount(): int
	{
		$entityManager = $this->dataSource->getEntityManager();
		$entityNames = $this->dataSource->getRootEntities();
		$connection = $entityManager->getConnection();
		$platform = $connection->getDatabasePlatform();

		if ($this->isFiltered() || empty($entityNames)) {
			// return $this->calculateSlidingCount();
			return parent::getCount();
		}

		$metaData = $entityManager->getClassMetadata($entityNames[0]);
		$tableName = $metaData->getTableName();

		if ($schemaName = $metaData->getSchemaName()) {
			$tableName = $schemaName.'.'.$tableName;
		}

		try {
			$count = $connection->fetchOne("SELECT reltuples::bigint AS count FROM pg_class WHERE oid = '{$tableName}'::regclass;");

			/** @var int */
			return $count;

		} catch (DBALException) {
			// return $this->calculateSlidingCount();
			return parent::getCount();
		}
	}


	private function isFiltered(): bool
	{
		return (bool) $this->dataSource->getDQLPart('where');
	}


	// private function calculateSlidingCount(): int
	// {
	// 	if (!$this->datagrid || !$component = $this->datagrid->getPaginator()) {
	// 		// return parent::getCount(); // ? could be?
	// 		return 0;
	// 	}

	// 	$paginator = $component->getPaginator();
	// 	return ($paginator->getPage() + 1) * $paginator->getItemsPerPage();
	// }
}
