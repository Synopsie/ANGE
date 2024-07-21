<?php
declare(strict_types=1);

namespace synopsie\plugin;

final readonly class PluginLoadTriageEntry {

    public function __construct(
        private string           $file,
        public PluginLoader      $loader,
        public EnginePluginInfos $informations
    ) {
    }

    public function getFile() : string {
        return $this->file;
    }

    public function getLoader() : PluginLoader {
        return $this->loader;
    }

    public function getPluginInfos() : EnginePluginInfos {
        return $this->informations;
    }

}