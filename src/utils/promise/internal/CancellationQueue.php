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

namespace synopsie\utils\promise\internal;

use Throwable;

use function array_push;
use function is_object;
use function key;
use function method_exists;

class CancellationQueue {
	private bool $started = false;
	private array $queue  = [];

	/**
	 * @throws Throwable
	 */
	public function __invoke() : void {
		if ($this->started) {
			return;
		}

		$this->started = true;
		$this->drain();
	}

	/**
	 * @throws Throwable
	 */
	public function enqueue($cancellable) : void {
		if (!is_object($cancellable) || !method_exists($cancellable, 'then') || !method_exists($cancellable, 'cancel')) {
			return;
		}

		$length = array_push($this->queue, $cancellable);

		if ($this->started && 1 === $length) {
			$this->drain();
		}
	}

	/**
	 * @throws Throwable
	 */
	private function drain() : void {
		for ($i = key($this->queue); isset($this->queue[$i]); $i++) {
			$cancellable = $this->queue[$i];

			$exception = null;

			try {
				$cancellable->cancel();
			} catch (Throwable $exception) {
			}

			unset($this->queue[$i]);

			if ($exception) {
				throw $exception;
			}
		}

		$this->queue = [];
	}

}
