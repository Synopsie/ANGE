<?php
declare(strict_types=1);

namespace synopsie;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use ReflectionException;
use Symfony\Component\Filesystem\Path;
use synopsie\plugin\ServerLoader;

class Engine extends PluginBase {
    use SingletonTrait{
        setInstance as private;
        reset as private;
    }

    private string $pluginPath;
    private ServerLoader $serverLoader;

    /**
     * @throws ReflectionException
     */
    protected function onLoad() : void {
        self::setInstance($this);

        $this->pluginPath          = Path::join($this->getServer()->getDataPath(), 'engine-plugins');
        $this->serverLoader = new ServerLoader($this, $this->getServer());

        $this->serverLoader->loadEnginePlugins();
    }

    protected function onEnable() : void {
        $this->serverLoader->enableEnginePlugins();
    }

    protected function onDisable() : void {
        $this->serverLoader->disableEnginePlugins();
    }

    public function getPluginPath() : string {
        return $this->pluginPath;
    }

    public function getApiVersion() : string {
        return VersionInfo::BASE_VERSION;
    }

}