<?php
declare(strict_types=1);

namespace synopsie\plugin;

use pocketmine\Server;
use ReflectionException;
use Symfony\Component\Filesystem\Path;
use synopsie\Engine;

class ServerLoader {

    private PluginManager $pluginManager;
    private Engine $engine;
    private Server $server;

    public function __construct(Engine $engine, Server $server) {
        $this->engine = $engine;
        $this->server = $server;
        $this->pluginManager = new PluginManager($this->engine, $this->server);
    }

    /**
     * @throws ReflectionException
     */
    public function loadEnginePlugins() : void {
        $path = Path::join($this->server->getDataPath(), 'engine-plugins');

        $scanDir = scandir($path);
        if ($scanDir === false) {
            $this->engine->getLogger()->error('Error loading plugin...');
            return;
        }
        foreach (array_diff($scanDir, ['.', '..']) as $name) {
            $loader = new FolderPluginLoader($this->server->getLoader());
            $infos  = $loader->getPluginInfos(
                Path::join($path, $name)
            );
            if ($infos === null) {
                continue;
            }
            $this->pluginManager->loadPlugins($path);
        }
    }

    public function enableEnginePlugins() : bool {
        $allSuccess = true;
        foreach ($this->pluginManager->getPlugins() as $plugin) {
            if(!$plugin->isEnabled()) {
                if($this->pluginManager->enablePlugin($plugin) === false) {
                    $allSuccess = false;
                }
            }
        }
        return $allSuccess;
    }

    public function disableEnginePlugins() : void {
        $this->pluginManager->disablePlugins();
    }

    public function getPluginManager() : PluginManager {
        return $this->pluginManager;
    }

}