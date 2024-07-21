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

namespace synopsie\database\base;

use pmmp\thread\ThreadSafe;
use pmmp\thread\ThreadSafeArray;
use function serialize;

class QuerySendQueue extends ThreadSafe {
	private bool $invalidated = false;

	private ThreadSafeArray $queries;

	public function __construct() {
		$this->queries = new ThreadSafeArray();
	}

	public function scheduleQuery(int $queryID, int $modes, string $queries, array $params) : void {
		if($this->invalidated) {
			throw new QueueShutdownException("You cannot schedule a query on an invalidated queue.");
		}
		$this->synchronized(function () use ($queryID, $modes, $queries, $params) : void {
			$this->queries[] = serialize([$queryID, $modes, $queries, $params]);
			$this->notifyOne();
		});
	}

	public function fetchQuery() : ?string {
		return $this->synchronized(function () : ?string {
			while ($this->queries->count() === 0 && !$this->invalidated) {
				$this->wait();
			}
			return $this->queries->shift();
		});
	}

	public function invalidate() : void {
		$this->synchronized(function () : void {
			$this->invalidated = true;
			$this->notify();
		});
	}

	public function count() : int {
		return $this->queries->count();
	}

}
