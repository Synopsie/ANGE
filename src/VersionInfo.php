<?php
declare(strict_types=1);

namespace synopsie;

use Phar;
use pocketmine\utils\Git;
use pocketmine\utils\VersionString;

final class VersionInfo {

    public const NAME                 = "ANGE";
    public const BASE_VERSION         = "0.0.1-alpha";
    public const IS_DEVELOPMENT_BUILD = true;
    public const BUILD_CHANNEL        = "stable";

    private function __construct() {
    }

    private static ?string $gitHash = null;

    public static function GIT_HASH() : string {
        if(self::$gitHash === null) {
            $gitHash = str_repeat("00", 20);

            if(Phar::running() === "") {
                $gitHash = Git::getRepositoryStatePretty(dirname(__DIR__) . '/');
            } else {
                $phar = new Phar(Phar::running(false));
                $meta = $phar->getMetadata();
                if(isset($meta["git"])) {
                    $gitHash = $meta["git"];
                }
            }

            self::$gitHash = $gitHash;
        }

        return self::$gitHash;
    }

    private static ?int $buildNumber = null;

    public static function BUILD_NUMBER() : int {
        if(self::$buildNumber === null) {
            self::$buildNumber = 0;
            if(Phar::running() !== "") {
                $phar = new Phar(Phar::running(false));
                $meta = $phar->getMetadata();
                if(is_array($meta) && isset($meta["build"]) && is_int($meta["build"])) {
                    self::$buildNumber = $meta["build"];
                }
            }
        }

        return self::$buildNumber;
    }

    private static ?VersionString $fullVersion = null;

    public static function VERSION() : VersionString {
        if(self::$fullVersion === null) {
            self::$fullVersion = new VersionString(self::BASE_VERSION, self::IS_DEVELOPMENT_BUILD, self::BUILD_NUMBER());
        }
        return self::$fullVersion;
    }
}