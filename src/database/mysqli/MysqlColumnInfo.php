<?php

/*
 *  ____   __   __  _   _    ___    ____    ____    ___   _____
 * / ___|  \ \ / / | \ | |  / _ \  |  _ \  / ___|  |_ _| | ____|
 * \___ \   \ V /  |  \| | | | | | | |_) | \___ \   | |  |  _|
 *  ___) |   | |   | |\  | | |_| | |  __/   ___) |  | |  | |___
 * |____/    |_|   |_| \_|  \___/  |_|     |____/  |___| |_____|
 *
 * @author Julien
 * @link https://arkaniastudios.com
 * @version 0.0.1-alpha
 *
 */

declare(strict_types=1);

namespace synopsie\database\mysqli;

use arkania\database\result\SqlColumnInfo;

class MysqlColumnInfo extends SqlColumnInfo {
	private int $flags;
	private int $mysqlType;

	public function __construct(string $name, string $type, int $flags, int $mysqlType) {
		parent::__construct($name, $type);
		$this->flags     = $flags;
		$this->mysqlType = $mysqlType;
	}

	public function getFlags() : int {
		return $this->flags;
	}

	public function hasFlag(int $flag) : bool {
		return ($this->flags & $flag) > 0;
	}

	public function getMysqlType() : int {
		return $this->mysqlType;
	}
}
