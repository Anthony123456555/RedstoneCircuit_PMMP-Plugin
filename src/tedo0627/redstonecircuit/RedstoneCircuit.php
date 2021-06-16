<?php

namespace tedo0627\redstonecircuit;

use pocketmine\plugin\PluginBase;
use tedo0627\redstonecircuit\block\BlockRedstone;
use tedo0627\redstonecircuit\block\BlockRedstoneWire;

class RedstoneCircuit extends PluginBase {

    public function onEnable() {
        $loader = new BlockLoader();

        $loader->addBlock(new BlockRedstone());
        $loader->addBlock(new BlockRedstoneWire());

        $config = new BlockLoadConfig($this, $loader);
        $loader->load($config);
    }
}