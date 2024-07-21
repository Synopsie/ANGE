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

namespace synopsie\database;

use Logger;
use synopsie\utils\promise\PromiseInterface;

interface DataConnector {
	public function setLoggingQueries(bool $loggingQueries) : void;

	public function isLoggingQueries() : bool;

	public function setLogger(?Logger $logger) : void;

	public function getLogger() : ?Logger;

	public function executeGeneric(string $query, array $params = []) : PromiseInterface;

	public function executeInsert(string $query, array $params = []) : PromiseInterface;

	public function executeChange(string $query, array $params = []) : PromiseInterface;

	public function executeSelect(string $query, array $params = []) : PromiseInterface;

	public function waitAll() : void;

	public function close() : void;

}
