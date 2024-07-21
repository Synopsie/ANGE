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

use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Translatable;
use pocketmine\plugin\ApiVersion;
use pocketmine\utils\VersionString;
use function count;
use function implode;
use function stripos;

readonly class PluginLoadabilityChecker {
	public function __construct(private string $apiVersion) {
	}

	public function check(EnginePluginInfos $informations) : null|Translatable {
		$name = $informations->getName();
		if(stripos($name, "pocketmine") !== false || stripos($name, "minecraft") !== false || stripos($name, "mojang") !== false || stripos($name, 'synopsie') !== false) {
			return KnownTranslationFactory::pocketmine_plugin_restrictedName();
		}

		foreach($informations->getApi() as $api) {
			if(!VersionString::isValidBaseVersion($api)) {
				return KnownTranslationFactory::pocketmine_plugin_invalidAPI($api);
			}
		}

		if(!ApiVersion::isCompatible($this->apiVersion, $informations->getApi())) {
			return KnownTranslationFactory::pocketmine_plugin_incompatibleAPI(implode(", ", $informations->getApi()));
		}

		$ambiguousVersions = ApiVersion::checkAmbiguousVersions($informations->getApi());
		if(count($ambiguousVersions) > 0) {
			return KnownTranslationFactory::pocketmine_plugin_ambiguousMinAPI(implode(", ", $ambiguousVersions));
		}
		return null;
	}

}
