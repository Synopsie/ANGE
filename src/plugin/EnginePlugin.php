<?php
declare(strict_types=1);

namespace synopsie\plugin;

use AttachableLogger;
use pocketmine\plugin\ResourceProvider;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use synopsie\Engine;

interface EnginePlugin {

    public function __construct(
        Engine $engine,
        PluginLoader $loader,
        Server $server,
        EnginePluginInfos $infos,
        string $dataFolder,
        string $file,
        ResourceProvider $resourceProvider
    );

    public function isEnabled() : bool;

    public function onEnableStateChange(bool $enabled) : void;

    public function getDataFolder() : string;

    public function getInfos() : EnginePluginInfos;

    public function getName() : string;

    public function getLogger() : AttachableLogger;

    public function getLoader() : PluginLoader;

    public function getEngine() : Engine;

    public function getScheduler() : TaskScheduler;

}