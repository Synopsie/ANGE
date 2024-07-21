<?php

declare(strict_types=1);

namespace synopsie\database\base;

use arkania\database\SqlError;
use pmmp\thread\ThreadSafe;
use pmmp\thread\ThreadSafeArray;
use function count;
use function is_string;
use function serialize;
use function unserialize;

class QueryRecvQueue extends ThreadSafe {
	private int $availableThreads = 0;

	private ThreadSafeArray $queue;

	public function __construct() {
		$this->queue = new ThreadSafeArray();
	}

	public function publishResult(int $queryID, array $results) : void {
		$this->synchronized(function () use ($queryID, $results) : void {
			$this->queue[] = serialize([$queryID, $results]);
			$this->notify();
		});
	}

	public function publishError(int $queryID, SqlError $error) : void {
		$this->synchronized(function () use ($error, $queryID) : void {
			$this->queue[] = serialize([$queryID, $error]);
			$this->notify();
		});
	}

	public function fetchResults(&$queryID, &$results) : bool {
		$row = $this->queue->shift();
		if(is_string($row)) {
			[$queryID, $results] = unserialize($row, ["allowed_classes" => true]);
			return true;
		}
		return false;
	}

	public function fetchAllResults() : array {
		$ret = [];
		while($this->fetchResults($queryID, $results)) {
			$ret[] = [$queryID, $results];
		}
		return $ret;
	}

	public function waitForResults(int $expectedResults) : array {
		$this->synchronized(function () use ($expectedResults) : void {
			while(count($this->queue) < $expectedResults) {
				$this->wait();
			}
		});
		return $this->fetchAllResults();
	}

}
