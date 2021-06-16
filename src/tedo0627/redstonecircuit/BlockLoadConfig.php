<?php

namespace tedo0627\redstonecircuit;

use pocketmine\utils\Config;

class BlockLoadConfig {

    private Config $config;

    public function __construct(RedstoneCircuit $main, BlockLoader $loader) {
        $main->saveDefaultConfig();
        $main->reloadConfig();
        $this->config = new Config($main->getDataFolder() . "config.yml", Config::YAML);

        foreach ($loader->getConfigNames() as $name) {
            if (!$this->config->exists($name)) $this->config->set($name, true);
        }
        $this->config->save();
    }

    public function canLoadBlock(string $name): bool {
        return $this->config->get($name, true);
    }
}