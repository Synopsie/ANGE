<?php
declare(strict_types=1);

namespace synopsie\plugin;

use pocketmine\plugin\ResourceProvider;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use synopsie\Engine;

abstract class EnginePluginBase implements EnginePlugin {

    private TaskScheduler $scheduled;
    private bool $isEnabled = false;

    public function __construct(
        private readonly Engine            $engine,
        private readonly PluginLoader      $loader,
        private readonly Server            $server,
        private readonly EnginePluginInfos $informations,
        private string                     $dataFolder,
        private string                     $file,
        private readonly ResourceProvider  $resourceProvider
    ) {
        $this->dataFolder = rtrim($this->dataFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->file       = rtrim($this->file, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->scheduled  = new TaskScheduler($this->getFullName());
        $this->onLoad();
    }

    protected function onLoad() : void {
    }

    protected function onEnable() : void {
    }

    protected function onDisable() : void {
    }

    final public function getEngine() : Engine {
        return $this->engine;
    }

    final public function getLoader() : PluginLoader {
        return $this->loader;
    }

    final public function getServer() : Server {
        return $this->server;
    }

    final public function getPluginInfos() : EnginePluginInfos {
        return $this->informations;
    }

    final public function getDataFolder() : string {
        return $this->dataFolder;
    }

    final public function getFile() : string {
        return $this->file;
    }

    final public function getLogger() : \AttachableLogger {
        return $this->engine->getLogger();
    }

    final public function getResourceProvider() : ResourceProvider {
        return $this->resourceProvider;
    }

    final public function getScheduler() : TaskScheduler {
        return $this->scheduled;
    }

    final public function isEnabled() : bool {
        return $this->isEnabled;
    }

    final public function getFullName() : string {
        return $this->informations->getFullName();
    }

    final public function getName() : string {
        return $this->informations->getName();
    }

    final public function onEnableStateChange(bool $enabled) : void {
        if($this->isEnabled !== $enabled) {
            $this->isEnabled = $enabled;
            if($this->isEnabled) {
                $this->onEnable();
            } else {
                $this->onDisable();
            }
        }
    }

}