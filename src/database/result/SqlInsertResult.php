<?php

declare(strict_types=1);

namespace synopsie\database\result;

class SqlInsertResult extends SqlChangeResult {
	private int $insertId;

	public function __construct(int $affectedRows, int $insertId) {
		parent::__construct($affectedRows);
		$this->insertId = $insertId;
	}

	public function getInsertId() : int {
		return $this->insertId;
	}

}
