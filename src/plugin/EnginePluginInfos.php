<?php
declare(strict_types=1);

namespace synopsie\plugin;

use Exception;
use pocketmine\plugin\PluginDescriptionParseException;

final class EnginePluginInfos {

    private string $name;

    private string $version;

    private string $main;

    /** @var string[] */
    private array $api;

    private string $description;

    private string $website;

    private string $author;

    /** @var string[] */
    private array $authors;

    /** @var array<string, int|bool|array|string> */
    private array $map;

    /** @var string[] */
    private array $depends = [];

    /** @var mixed|string */
    private mixed $srcNamespacePrefix;

    /**
     * @throws Exception
     * @param (string|int|float)[]|string $file
     */
    public function __construct(array|string $file) {
        if (is_string($file)) {
            $map = yaml_parse($file);
            if($map === false) {
                throw new PluginDescriptionParseException("Invalid yaml file: $file");
            }
            if(!is_array($map)) {
                throw new PluginDescriptionParseException("Root must be an array");
            }
        } else {
            $map = $file;
        }
        $this->loadMap($map);
    }

    private function loadMap(array $map) : void {
        $this->name    = $map["name"] ?? throw new PluginDescriptionParseException("Missing name");
        $this->version = $map["version"] ?? throw new PluginDescriptionParseException("Missing version");
        if(isset($map["main"])) {
            if (str_contains($map['main'], 'arkania')) {
                throw new PluginDescriptionParseException("Invalid main: $map[main] (cannot be inside the arkania namespace)");
            }
            $this->main = $map["main"];
        } else {
            throw new PluginDescriptionParseException("Missing main");
        }
        $this->api                = array_map("\strval", (array) ($map['api'] ?? []));
        $this->srcNamespacePrefix = $map["src-namespace-prefix"] ?? '';
        $this->description        = $map["description"] ?? "";
        $this->website            = $map["website"] ?? "";
        $this->author             = $map["author"] ?? "";
        $this->authors            = $map["authors"] ?? [];
        if(isset($map['depend'])) {
            $this->depends = (array) $map['depend'];
        }
        $this->map = $map;}

    /**
     * @return string[]
     */
    public function getMap() : array {
        return $this->map;
    }

    public function getName() : string {
        return $this->name;
    }

    public function getVersion() : string {
        return $this->version;
    }

    public function getMain() : string {
        return $this->main;
    }

    /**
     * @return string[]
     */
    public function getApi() : array {
        return $this->api;
    }

    public function getAuthor() : string {
        return $this->author;
    }

    /**
     * @return string[]
     */
    public function getAuthors() : array {
        return $this->authors;
    }

    public function getWebsite() : string {
        return $this->website;
    }

    public function getDescription() : string {
        return $this->description;
    }

    public function getSrcNamespacePrefix() : string {
        return $this->srcNamespacePrefix;
    }

    /**
     * @return string[]
     */
    public function getDepends() : array {
        return $this->depends;
    }

    public function getFullName() : string {
        return $this->name . ' v' . $this->version;
    }

}