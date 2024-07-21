<?php
declare(strict_types=1);

namespace synopsie\plugin;

final class PluginLoadTriage {

    /** @var PluginLoadTriageEntry[] */
    public array $plugins = [];

    /** @var string[][] */
    public array $dependencies = [];

}