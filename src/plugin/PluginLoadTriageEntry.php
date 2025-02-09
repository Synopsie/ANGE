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

namespace synopsie\plugin;

final readonly class PluginLoadTriageEntry {
	public function __construct(
		private string           $file,
		public PluginLoader      $loader,
		public EnginePluginInfos $informations
	) {
	}

	public function getFile() : string {
		return $this->file;
	}

	public function getLoader() : PluginLoader {
		return $this->loader;
	}

	public function getPluginInfos() : EnginePluginInfos {
		return $this->informations;
	}

}
