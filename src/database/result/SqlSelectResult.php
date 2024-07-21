<?php

declare(strict_types=1);

namespace synopsie\database\result;

use arkania\database\SqlResult;

class SqlSelectResult extends SqlResult {
	private array $columnInfo;
	private array $rows;

	/**
	 * @param SqlColumnInfo[] $columnInfo
	 * @param array[]         $rows
	 */
	public function __construct(
		array $columnInfo,
		array $rows
	) {
		$this->columnInfo = $columnInfo;
		$this->rows       = $rows;
	}

	/**
	 * @return SqlColumnInfo[]
	 */
	public function getColumnInfo() : array {
		return $this->columnInfo;
	}

	/**
	 * @return array[]
	 */
	public function getRows() : array {
		return $this->rows;
	}
}
