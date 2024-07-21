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

namespace synopsie\utils\promise\exception;

use Exception;
use Throwable;

class CompositeException extends Exception {
	private array $throwables;
	public function __construct(array $throwables, $message = '', $code = 0, $previous = null) {
		parent::__construct($message, $code, $previous);

		$this->throwables = $throwables;
	}

	/**
	 * @return Throwable[]
	 */
	public function getThrowables() : array {
		return $this->throwables;
	}

}
