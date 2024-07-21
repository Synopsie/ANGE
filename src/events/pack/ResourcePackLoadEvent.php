<?php
declare(strict_types=1);

namespace synopsie\events\pack;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use Symfony\Component\Filesystem\Path;
use synopsie\Engine;
use synopsie\pack\ResourcesPackFile;

class ResourcePackLoadEvent extends Event implements Cancellable {
    use CancellableTrait;

    /** @var string[]|null */
    private ?array $resourcePackPath = null;

    public function addResourcesPackFile(string $packName, ResourcesPackFile $packFile) : void {
        $this->resourcePackPath[$packName] = $packFile->getResourcePackPath();
        $packFile->savePackInData($packFile->getResourcePackPath());
        $packFile->zipPack(
            $packFile->getResourcePackPath(),
            Path::join(Engine::getInstance()->getEngineFile(), 'packs'),
            $packName
        );
    }

    public function getResourcePackPath() : ?array {
        return $this->resourcePackPath;
    }

}