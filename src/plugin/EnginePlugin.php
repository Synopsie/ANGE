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

use AttachableLogger;
use pocketmine\plugin\ResourceProvider;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use synopsie\Engine;

interface EnginePlugin {
	public function __construct(
		Engine $engine,
		PluginLoader $loader,
		Server $server,
		EnginePluginInfos $infos,
		string $dataFolder,
		string $file,
		ResourceProvider $resourceProvider
	);

	public function isEnabled() : bool;

	public function onEnableStateChange(bool $enabled) : void;

	public function getDataFolder() : string;

	public function getPluginInfos() : EnginePluginInfos;

	public function getName() : string;

	public function getLogger() : AttachableLogger;

	public function getLoader() : PluginLoader;

	public function getEngine() : Engine;

	public function getScheduler() : TaskScheduler;

}
