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

namespace synopsie\language;

use InvalidArgumentException;
use Symfony\Component\Filesystem\Path;
use synopsie\Engine;
use synopsie\events\language\RegisterLanguageEvent;
use function dirname;
use function file_exists;
use function mkdir;

class LanguageManager {
	/** @var Language[] */
	private array $languages = [];
	private Engine $engine;

	public function register(Language $language) : void {
		if (isset($this->languages[$language->getName()])) {
			throw new InvalidArgumentException("Language with code {$language->getName()} is already registered");
		}
		$ev = new RegisterLanguageEvent($language);
		$ev->call();
		if($ev->isCancelled()) {
			return;
		}
		$this->languages[$ev->getLanguage()->getName()] = $ev->getLanguage();
	}

	public function __construct(Engine $engine) {

		if(!file_exists(Path::join($engine->getDataFolder(), 'data'))) {
			mkdir(Path::join($engine->getDataFolder(), 'data'));
		}

		$engine->saveResource(Path::join('data', 'fr_FR.lang'), true);

		$this->register(new Language(
			'Français',
			'fr_FR.lang',
			Path::join($engine->getDataFolder(), 'data'),
			Path::join(dirname(__DIR__, 2), 'vendor', 'pocketmine', 'locale-data'),
			'fra.ini'
		));
		$this->engine = $engine;
	}

	public function getLanguage(string $lang) : ?Language {
		return $this->languages[$this->parseLanguageName($lang)] ?? null;
	}

	public function getConsoleLanguage() : Language {
		return $this->getLanguage($this->engine->getConfig()->get('console-language'));
	}

	public function getDefaultLanguage() : Language {
		return $this->getLanguage($this->engine->getConfig()->get('default-language'));
	}

	private function parseLanguageName(string $lang) : string {
		return match($lang) {
			default => 'Français'
		};
	}

}
