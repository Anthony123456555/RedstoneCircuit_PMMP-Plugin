<?php

namespace tedo0627\redstonecircuit;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\item\Item;
use pocketmine\tile\Tile;

class BlockLoader {

    private array $blocks = [];
    private array $blockEntities = [];

    public function getConfigNames(): array {
        return [...array_keys($this->blocks), ...array_keys($this->blockEntities)];
    }

    public function addBlock(Block $block, string $loadName = ""): void {
        if ($loadName === "") $loadName = str_replace(" ", "-", mb_strtolower($block->getName()));
        $configName = "load-" . $loadName;
        if (!array_key_exists($configName, $this->blocks)) {
            $this->blocks[$configName] = [];
        }

        $this->blocks[$configName][] = $block;
    }

    public function addBlockEntity(string $loadName, string $className, array $saveNames = []) {
        $configName = "load-" . $loadName;
        if (!array_key_exists($configName, $this->blockEntities)) {
            $this->blockEntities[$configName] = [];
        }

        $this->blockEntities[$configName][] = new class($className, $saveNames) {
            public string $className;
            public array $saveNames;

            public function __construct(string $className, array $saveNames) {
                $this->className = $className;
                $this->saveNames = $saveNames;
            }

            public function load() {
                Tile::registerTile($this->className, $this->saveNames);
            }
        };
    }

    public function load(BlockLoadConfig $config): void {
        foreach ($this->blocks as $key => $value) {
            if (!$config->canLoadBlock($key)) continue;

            foreach ($value as $block) BlockFactory::registerBlock($block, true);
        }
        foreach ($this->blockEntities as $key => $value) {
            if ($config->canLoadBlock($key)) $value->load();
        }
        Item::initCreativeItems();
    }
}