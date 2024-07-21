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

use BadFunctionCallException;
use synopsie\database\base\DataConnectorImpl;
use synopsie\database\base\SqlThreadPool;
use synopsie\database\mysqli\MysqlCredentials;
use synopsie\database\mysqli\MysqliThread;
use synopsie\Engine;
use function extension_loaded;
use function usleep;

final class DataBaseManager {
	private DataConnectorImpl $connector;

	public function __construct(
		private readonly Engine $plugin
	) {
		if(!extension_loaded("mysqli")) {
			throw new BadFunctionCallException("The mysqli extension is not loaded");
		}
		$cred = MysqlCredentials::fromArray(
			$this->plugin->getConfig()->get("database")
		);
		$factory = MysqliThread::createFactory($cred, $this->plugin->getServer()->getLogger());
		$pool    = new SqlThreadPool($factory, 1);
		while(!$pool->connCreated()) {
			usleep(1000);
		}
		if($pool->hasConnError()) {
			throw new SqlError(SqlError::STAGE_CONNECT, $pool->getConnError());
		}
		$this->connector = new DataConnectorImpl(
			$this->plugin,
			$pool
		);
	}

	public function getConnector() : DataConnectorImpl {
		return $this->connector;
	}

}
