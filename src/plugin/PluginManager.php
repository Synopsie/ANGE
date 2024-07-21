<?php
declare(strict_types=1);

namespace synopsie\plugin;

use FilesystemIterator;
use pocketmine\event\HandlerListManager;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\DiskResourceProvider;
use pocketmine\plugin\PluginDescriptionParseException;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use ReflectionException;
use RuntimeException;
use Symfony\Component\Filesystem\Path;
use synopsie\Engine;

class PluginManager {

    private Engine $engine;
    private Server $server;

    /** @var EnginePlugin[] */
    private array $plugins = [];
    /** @var EnginePlugin[] */
    private array $enabledPlugins = [];
    /** @var array<string, array<string, true>> */
    private array $pluginDependents = [];

    public function __construct(Engine $engine, Server $server) {
        if(!file_exists($engine->getPluginPath())) {
            mkdir($engine->getPluginPath());
        }
        $this->engine = $engine;
        $this->server = $server;
    }

    /**
     * @return EnginePlugin[]
     */
    public function getPlugins() : array {
        return $this->plugins;
    }

    public function getPlugin(string $name) : ?EnginePlugin {
        return $this->plugins[$name] ?? null;
    }

    private function internalLoadPlugin(string $path, PluginLoader $loader, EnginePluginInfos $infos) : ?EnginePlugin {
        $name = $infos->getName();
        $dataFolder = Path::join($path, $name);
        if (file_exists($dataFolder) && !is_dir($dataFolder)) {
            $this->engine->getLogger()->critical('Plugin ' . $name . ' has a file with the same name as the plugin');
            return null;
        }
        $prefixed = $loader->getAccessProtocol() . $path;
        $loader->loadPlugin(Path::join($this->engine->getPluginPath(), $name));
        $mainClass = $infos->getMain();
        if(!class_exists($mainClass)) {
            $this->engine->getLogger()->critical('Plugin ' . $name . ' has no main class');
            return null;
        }
        if (!is_a($mainClass, EnginePlugin::class, true)) {
            $this->engine->getLogger()->critical('Plugin ' . $name . ' main class must extend EnginePlugin');
            return null;
        }
        $reflect = new \ReflectionClass($mainClass);
        if($reflect->isAbstract()) {
            $this->engine->getLogger()->critical('Plugin ' . $name . ' main class must not be abstract');
            return null;
        }
        /**
         * @see EnginePlugin::__construct()
         */
        $plugin = new $mainClass(
            $this->engine,
            $loader,
            $this->server,
            $infos,
            $dataFolder,
            $prefixed,
            new DiskResourceProvider(Path::join($prefixed, 'resources'))
        );
        $this->plugins[$name] = $plugin;
        return $plugin;
    }

    private function triagePlugins(string $path, PluginLoadTriage $triage, int &$loadErrorCount) : void {
        $loadability = new PluginLoadabilityChecker($this->engine->getApiVersion());
        $loader      = new FolderPluginLoader($this->server->getLoader());
        if(is_dir($path)) {
            $files = iterator_to_array(new FilesystemIterator($path, FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS));
            shuffle($files);
        } elseif(is_file($path)) {
            $realPath = Utils::assumeNotFalse(realpath($path), "realpath() should not return false on an accessible, existing file");
            $files    = [$realPath];
        } else {
            return;
        }

        foreach ($files as $file) {
            if(!is_string($file)) {
                throw new AssumptionFailedError("FilesystemIterator should return strings");
            }
            if(!$loader->canLoadPlugin($file)) {
                continue;
            }
            try {
                $informations = $loader->getPluginInfos($file);
            } catch (PluginDescriptionParseException $e) {
                $this->engine->getLogger()->critical('Error while parsing plugin description in ' . $file . ': ' . $e->getMessage());
                $loadErrorCount++;
                continue;
            } catch (RuntimeException $e) {
                $this->engine->getLogger()->critical('Error while reading plugin description in ' . $file . ': ' . $e->getMessage());
                $this->engine->getLogger()->logException($e);
                $loadErrorCount++;
                continue;
            }
            if($informations === null) {
                continue;
            }
            $name = $informations->getName();
            if(($loadabilityError = $loadability->check($informations)) !== null) {
                $this->engine->getLogger()->critical('Plugin ' . $name . ' is not compatible with this server: ' . Server::getInstance()->getLanguage()->translate($loadabilityError));
                $loadErrorCount++;
                continue;
            }
            if(isset($triage->plugins[$name]) || $this->getPlugin($name) instanceof EnginePlugin) {
                $this->engine->getLogger()->critical('Plugin ' . $name . ' is already loaded, or a plugin with the same name is already loaded');
                $loadErrorCount++;
                continue;
            }
            if(str_contains($name, " ")) {
                $this->engine->getLogger()->warning('Plugin ' . $name . ' has a space in its name, this is not recommended');
            }

            $triage->plugins[$name] = new PluginLoadTriageEntry(
                $file,
                $loader,
                $informations
            );
            $triage->dependencies[$name] = $informations->getDepends();
        }
    }

    /**
     * @param string[][]     $dependencyLists
     * @param EnginePlugin[] $loadedPlugins
     */
    private function checkDepsForTriage(string $pluginName, array &$dependencyLists, array $loadedPlugins) : void {
        if(isset($dependencyLists[$pluginName])) {
            foreach($dependencyLists[$pluginName] as $key => $dependency) {
                if(isset($loadedPlugins[$dependency]) || $this->getPlugin($dependency) instanceof EnginePlugin) {
                    unset($dependencyLists[$pluginName][$key]);
                }
            }

            if(count($dependencyLists[$pluginName]) === 0) {
                unset($dependencyLists[$pluginName]);
            }
        }
    }

    /**
     * @return EnginePlugin[]
     * @throws ReflectionException
     */
    public function loadPlugins(string $path, int &$loadErrorCount = 0) : array {
        $triage = new PluginLoadTriage();
        $this->triagePlugins($path, $triage, $loadErrorCount);
        $loadedPlugins = [];
        while(count($triage->plugins) > 0) {
            $loadedThisLoop = 0;
            foreach($triage->plugins as $name => $entry) {
                $this->checkDepsForTriage($name, $triage->dependencies, $loadedPlugins);

                if(!isset($triage->softDependencies[$name]) && !isset($triage->hardDependencies[$name])) {
                    unset($triage->plugins[$name]);
                    $loadedThisLoop++;
                    if(($plugin = $this->internalLoadPlugin($entry->getFile(), $entry->getLoader(), $entry->getPluginInfos())) !== null) {
                        $loadedPlugins[$plugin->getName()] = $plugin;
                    }
                } else {
                    $loadErrorCount++;
                }
            }
            if($loadedThisLoop === 0) {
                foreach(Utils::stringifyKeys($triage->plugins) as $name => $file) {
                    if(isset($triage->dependencies[$name])) {
                        $unknownDependencies = [];

                        foreach($triage->dependencies[$name] as $k => $dependency) {
                            if($this->getPlugin($dependency) === null && !array_key_exists($dependency, $triage->plugins)) {
                                $unknownDependencies[$dependency] = $dependency;
                            }
                        }

                        if(count($unknownDependencies) > 0) {
                            $this->server->getLogger()->critical('Plugin ' . $name . ' has unknown dependencies: ' . implode(", ", $unknownDependencies));
                            unset($triage->plugins[$name]);
                            $loadErrorCount++;
                        }
                    }
                }
                foreach(Utils::stringifyKeys($triage->plugins) as $name => $file) {
                    $this->engine->getLogger()->critical('Plugin ' . $name . ' has unmet dependencies: ' . implode(", ", $triage->dependencies[$name]));
                    $loadErrorCount++;
                }
            }
        }
        return $loadedPlugins;
    }

    private function isPluginEnabled(EnginePlugin $plugin) : bool {
        return isset($this->plugins[$plugin->getName()]) && $plugin->isEnabled();
    }

    public function enablePlugin(EnginePlugin $plugin) : bool {
        if (!$plugin->isEnabled()) {
            $this->engine->getLogger()->info(
                Server::getInstance()->getLanguage()->translate(
                    KnownTranslationFactory::pocketmine_plugin_enable($plugin->getPluginInfos()->getFullName())
                )
            );
            $plugin->getScheduler()->setEnabled(true);
            try {
                $plugin->onEnableStateChange(true);
            } catch (DisablePluginException) {
                $this->disablePlugin($plugin);
            }

            if($plugin->isEnabled()) {
                $this->enabledPlugins[$plugin->getName()] = $plugin;
                foreach ($plugin->getPluginInfos()->getDepends() as $dependency) {
                    $this->pluginDependents[$dependency][$plugin->getName()] = true;
                }
                return true;
            }
            $this->engine->getLogger()->critical(
                Server::getInstance()->getLanguage()->translate(
                    KnownTranslationFactory::pocketmine_plugin_enableError(
                        $plugin->getPluginInfos()->getName(),
                        KnownTranslationFactory::pocketmine_plugin_suicide()
                    )
                )
            );
            return false;

        }
        return true;
    }

    public function disablePlugins() : void {
        while(count($this->enabledPlugins) > 0) {
            foreach ($this->enabledPlugins as $plugin) {
                if(!$plugin->isEnabled()) {
                    continue;
                }
                $name = $plugin->getName();
                if(isset($this->pluginDependents[$name]) && count($this->pluginDependents[$name]) > 0) {
                    continue;
                }
                $this->disablePlugin($plugin);
            }
        }
    }

    public function disablePlugin(EnginePlugin $plugin) : void {
        if($plugin->isEnabled()) {
            $this->engine->getLogger()->info(
                Server::getInstance()->getLanguage()->translate(
                    KnownTranslationFactory::pocketmine_plugin_disable(
                        $plugin->getPluginInfos()->getFullName()
                    )
                )
            );
            unset($this->enabledPlugins[$plugin->getName()]);
            foreach(Utils::stringifyKeys($this->pluginDependents) as $dependency => $dependentList) {
                if(isset($this->pluginDependents[$dependency][$plugin->getPluginInfos()->getName()])) {
                    if(count($this->pluginDependents[$dependency]) === 1) {
                        unset($this->pluginDependents[$dependency]);
                    } else {
                        unset($this->pluginDependents[$dependency][$plugin->getPluginInfos()->getName()]);
                    }
                }
            }
            $plugin->onEnableStateChange(false);
            $plugin->getScheduler()->setEnabled(false);
            HandlerListManager::global()->unregisterAll($this->engine);
        }
    }

}