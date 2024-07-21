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

use InvalidArgumentException;
use pocketmine\Server;
use pocketmine\snooze\SleeperHandlerEntry;
use synopsie\database\SqlThread;
use function assert;
use function count;

class SqlThreadPool implements SqlThread {
	private SleeperHandlerEntry $sleeperEntry;
	/** @var callable */
	private $workerFactory;

	/** @var SqlSlaveThread[] */
	private array $workers = [];

	private int $workerLimit;

	private QuerySendQueue $bufferSend;

	private QueryRecvQueue $bufferRecv;

	private ?DataConnectorImpl $dataConnector = null;

	public function setDataConnector(DataConnectorImpl $dataConnector) : void {
		$this->dataConnector = $dataConnector;
	}

	public function __construct(
		callable $workerFactory,
		int $workerLimit
	) {
		$this->sleeperEntry = Server::getInstance()->getTickSleeper()->addNotifier(function () : void {
			assert($this->dataConnector instanceof DataConnectorImpl);
			$this->dataConnector->checkResults();
		});
		$this->workerFactory = $workerFactory;
		$this->workerLimit   = $workerLimit;
		$this->bufferSend    = new QuerySendQueue();
		$this->bufferRecv    = new QueryRecvQueue();
		$this->addWorker();
	}

	private function addWorker() : void {
		$this->workers[] = ($this->workerFactory)($this->sleeperEntry, $this->bufferSend, $this->bufferRecv);
	}

	public function join() : void {
		foreach ($this->workers as $worker) {
			$worker->join();
		}
	}

	public function stopRunning() : void {
		foreach ($this->workers as $worker) {
			$worker->stopRunning();
		}
	}

	public function addQuery(int $queryId, int $modes, string $queries, array $params) : void {
		$this->bufferSend->scheduleQuery($queryId, $modes, $queries, $params);
		foreach ($this->workers as $worker) {
			if(!$worker->isBusy()) {
				return;
			}
		}
		if(count($this->workers) < $this->workerLimit) {
			$this->addWorker();
		}
	}

	public function readResults(array &$callbacks, ?int $expectedResults) : void {
		if($expectedResults === null) {
			$resultsList = $this->bufferRecv->fetchAllResults();
		} else {
			$resultsList = $this->bufferRecv->waitForResults($expectedResults);
		}
		foreach($resultsList as [$queryId, $results]) {
			if(!isset($callbacks[$queryId])) {
				throw new InvalidArgumentException("Missing handler for query #$queryId");
			}
			$callbacks[$queryId]($results);
			unset($callbacks[$queryId]);
		}
	}

	public function connCreated() : bool {
		return $this->workers[0]->connCreated();
	}

	public function hasConnError() : bool {
		return $this->workers[0]->hasConnError();
	}

	public function getConnError() : ?string {
		return $this->workers[0]->getConnError();
	}

	public function getLoad() : float {
		return $this->bufferSend->count() / (float) $this->workerLimit;
	}

}
