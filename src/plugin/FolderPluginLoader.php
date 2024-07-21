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

use pocketmine\plugin\PluginDescriptionParseException;
use pocketmine\thread\ThreadSafeClassLoader;
use Symfony\Component\Filesystem\Path;
use synopsie\Engine;
use function file_exists;
use function file_get_contents;
use function is_dir;

class FolderPluginLoader implements PluginLoader {
	private ThreadSafeClassLoader $loader;

	public function __construct(ThreadSafeClassLoader $loader) {
		$this->loader = $loader;
	}

	public function canLoadPlugin(string $path) : bool {
		return is_dir($path) && file_exists(Path::join($path, "/plugin.yml"));
	}

	public function loadPlugin(string $file) : void {
		$description = $this->getPluginInfos($file);
		if($description !== null) {
			$this->loader->addPath($description->getSrcNamespacePrefix(), "$file/src");
		}
	}

	public function getPluginInfos(string $file) : ?EnginePluginInfos {
		if(is_dir($file) && file_exists($file . "/plugin.yml")) {
			$yaml = @file_get_contents($file . "/plugin.yml");
			if($yaml !== '') {
				try {
					return new EnginePluginInfos($yaml);
				} catch (PluginDescriptionParseException) {
					Engine::getInstance()->getLogger()->error('Invalid plugin file: ' . $file . "/plugin.yml");
					return null;
				}
			}
		}
		return null;
	}

	public function getAccessProtocol() : string {
		return "";
	}

}
