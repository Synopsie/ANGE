<?php
declare(strict_types=1);

namespace synopsie\plugin;

interface PluginLoader {

    public function canLoadPlugin(string $path) : bool;

    public function loadPlugin(string $file) : void;

    public function getPluginInfos(string $file) : ?EnginePluginInfos;

    public function getAccessProtocol() : string;

}