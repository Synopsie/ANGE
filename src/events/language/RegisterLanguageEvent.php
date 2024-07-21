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

namespace synopsie\events\language;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use synopsie\language\Language;

class RegisterLanguageEvent extends Event implements Cancellable {
	use CancellableTrait;

	private Language $language;

	public function __construct(Language $language) {
		$this->language = $language;
	}

	public function getLanguage() : Language {
		return $this->language;
	}

	public function setLanguage(Language $language) : void {
		$this->language = $language;
	}

}
