<?php

namespace tedo0627\redstonecircuit;

use pocketmine\plugin\PluginBase;
use tedo0627\redstonecircuit\block\mechanism\BlockRedstoneLamp;
use tedo0627\redstonecircuit\block\mechanism\BlockRedstoneLampLit;
use tedo0627\redstonecircuit\block\power\BlockRedstone;
use tedo0627\redstonecircuit\block\power\BlockStoneButton;
use tedo0627\redstonecircuit\block\power\BlockWoodenButton;
use tedo0627\redstonecircuit\block\transmission\BlockRedstoneWire;

class RedstoneCircuit extends PluginBase {

    public function onEnable() {
        $loader = new BlockLoader();

        // mechanism
        $loader->addBlock(new BlockRedstoneLamp(), "redstone-lamp");
        $loader->addBlock(new BlockRedstoneLampLit(), "redstone-lamp");

        // power
        $loader->addBlock(new BlockRedstone());
        $loader->addBlock(new BlockStoneButton(), "button");
        $loader->addBlock(new BlockWoodenButton(), "button");

        // transmission
        $loader->addBlock(new BlockRedstoneWire());

        $config = new BlockLoadConfig($this, $loader);
        $loader->load($config);
    }
}