<?php
declare(strict_types=1);

namespace synopsie\pack;

use pocketmine\resourcepacks\ZippedResourcePack;
use Symfony\Component\Filesystem\Path;
use synopsie\Engine;
use synopsie\events\pack\ResourcePackLoadEvent;

class ResourcePackManager {

    private Engine $engine;

    public function registerResourcePack(string $packName, ResourcesPackFile $packFile) : void {
        $this->resourcePackPath[$packName] = $packFile->getResourcePackPath();
        $packFile->savePackInData($packFile->getResourcePackPath());
        $packFile->zipPack(
            $packFile->getResourcePackPath(),
            Path::join($this->engine->getEngineFile(), 'packs'),
            $packName
        );
    }

    /** @var string[] */
    protected array $resourcePackPath = [];

    public function __construct(Engine $engine) {
        $this->engine = $engine;

    }

    public function loadResourcePack() : void {
        $resourcePackManager = $this->engine->getServer()->getResourcePackManager();
        $resourcePacks       = [];
        foreach ($this->resourcePackPath as $packName => $packPath) {
            $resourcePacks[] = new ZippedResourcePack($packPath . '.zip');
        }
        $ev = new ResourcePackLoadEvent();
        $ev->call();
        if (!$ev->isCancelled()) {
            if ($ev->getResourcePackPath() !== null) {
                foreach ($ev->getResourcePackPath() as $packName => $resource) {
                    $resourcePacks[] = new ZippedResourcePack($resource . '.zip');
                }
            }
            $resourcePackManager->setResourcePacksRequired(true);
            $resourcePackManager->setResourceStack($resourcePacks);
        } else {
            $this->engine->getLogger()->warning('Resources pack system is cancelled !');
        }
    }
}