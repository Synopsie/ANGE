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

namespace synopsie;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use ReflectionException;
use Symfony\Component\Filesystem\Path;
use synopsie\events\ListenerManager;
use synopsie\language\Language;
use synopsie\language\LanguageManager;
use synopsie\plugin\ServerLoader;

require_once __DIR__ . '/utils/promise/functions.php';

class Engine extends PluginBase {
	use SingletonTrait{
		setInstance as private;
		reset as private;
	}

	private string $pluginPath;
	private ServerLoader $serverLoader;
	private ListenerManager $listenerManager;
	private LanguageManager $languageManager;

	/**
	 * @throws ReflectionException
	 */
	protected function onLoad() : void {
		self::setInstance($this);

		$this->saveResource('config.yml');
		$this->pluginPath      = Path::join($this->getServer()->getDataPath(), 'engine-plugins');
		$this->serverLoader    = new ServerLoader($this, $this->getServer());
		$this->listenerManager = new ListenerManager();
		$this->languageManager = new LanguageManager($this);

		$this->serverLoader->loadEnginePlugins();
	}

	protected function onEnable() : void {
		$this->serverLoader->enableEnginePlugins();
	}

	protected function onDisable() : void {
		$this->serverLoader->disableEnginePlugins();
	}

	public function getPluginPath() : string {
		return $this->pluginPath;
	}

	public function getApiVersion() : string {
		return VersionInfo::BASE_VERSION;
	}

	public function getListenerManager() : ListenerManager {
		return $this->listenerManager;
	}

	public function getLanguageManager() : LanguageManager {
		return $this->languageManager;
	}

	public function getConsoleLanguage() : Language {
		return $this->languageManager->getConsoleLanguage();
	}

}
