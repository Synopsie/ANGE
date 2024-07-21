<?php
declare(strict_types=1);

namespace arkania;

use Generator;
use pocketmine\lang\Translatable;
use ReflectionClass;
use Symfony\Component\Filesystem\Path;

require dirname(__DIR__) . '/vendor/autoload.php';

function constantify(string $permissionName) : string {
    return strtoupper(str_replace([".", "-"], "_", $permissionName));
}

function functionify(string $permissionName) : string {
    return strtoupper(str_replace([".", "-"], "_", $permissionName));
}

const SHARED_HEADER = <<<'HEADER'
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

HEADER;

function stringifyKey(array $array) : Generator {
    foreach ($array as $key => $value) {
        yield (string) $key => $value;
    }
}

/**
 * @param string[] $languageDefinitions
 * @phpstan-param array<string, string> $languageDefinitions
 */
function generate_known_translation_keys(array $languageDefinitions) : void {
    ob_start();
    echo SHARED_HEADER;
    echo <<<'HEADER'
final class KnownTranslationsKeys{

HEADER;
    ksort($languageDefinitions, SORT_STRING);
    foreach (stringifyKey($languageDefinitions) as $k => $_) {
        echo "\tpublic const ";
        echo constantify($k);
        echo " = \"" . $k . "\";\n";
    }
    echo "}\n";
    file_put_contents(dirname(__DIR__) . '/src/language/KnownTranslationsKeys.php', ob_get_clean());
    echo "Génération de KnownTranslationsKeys.\n";
}

/**
 * @param string[] $languageDefinitions
 * @phpstan-param array<string, string> $languageDefinitions
 */
function generate_known_translation_factory(array $languageDefinitions) : void {
    ob_start();
    echo SHARED_HEADER;
    echo <<<'HEADER'


use pocketmine\lang\Translatable;

final class KnownTranslationsFactory{

HEADER;
    ksort($languageDefinitions, SORT_STRING);
    $parameterRegex = '/{%(.+?)}/';
    $translationContainerClass = (new ReflectionClass(Translatable::class))->getShortName();
    foreach (stringifyKey($languageDefinitions) as $key => $value) {
        $parameters = [];
        $allParametersPositional = true;
        if (preg_match_all($parameterRegex, $value, $matches) > 0) {
            foreach ($matches[1] as $parameterName) {
                if (is_numeric($parameterName)) {
                    $parameters[$parameterName] = "param$parameterName";
                } else {
                    $parameters[$parameterName] = $parameterName;
                    $allParametersPositional = false;
                }
            }
        }
        if ($allParametersPositional) {
            ksort($parameters, SORT_NUMERIC);
        }
        echo "\tpublic static function " .
            strtolower(functionify($key)) .
            "(" . implode(", ", array_map(fn (string $paramName) => "$translationContainerClass|string \$$paramName", $parameters)) . ") : $translationContainerClass{\n";
        echo "\t\treturn new $translationContainerClass(KnownTranslationsKeys::" . constantify($key) . ", [";
        foreach ($parameters as $parameterKey => $parameterName) {
            echo "\n\t\t\t";
            if (!is_numeric($parameterKey)) {
                echo "\"$parameterKey\"";
            } else {
                echo $parameterKey;
            }
            echo " => \$$parameterName,";
        }
        if (count($parameters) !== 0) {
            echo "\n\t\t";
        }
        echo "]);\n\t}\n\n";
    }
    echo "}\n";
    file_put_contents(dirname(__DIR__) . '/src/language/KnownTranslationsFactory.php', ob_get_clean());
    echo "Génération de KnownTranslationsFactory.\n";
}
$lang = parse_ini_file(Path::join(dirname(__DIR__), 'resources', 'data', 'fr_FR.lang'), false, INI_SCANNER_RAW);
if ($lang === false) {
    fwrite(STDERR, "Missing language files!\n");
    exit(1);
}
generate_known_translation_keys($lang);
generate_known_translation_factory($lang);