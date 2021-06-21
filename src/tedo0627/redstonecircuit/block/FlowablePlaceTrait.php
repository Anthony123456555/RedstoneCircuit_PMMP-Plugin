<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\Block;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use tedo0627\redstonecircuit\Facing;

trait FlowablePlaceTrait {

    /**
     * Returns the Block on the side $side, works like Vector3::getSide()
     *
     * @return Block
     */
    public abstract function getSide(int $side, int $step = 1);

    public function canPlaceFlowable(): bool {
        $block = $this->getSide(Facing::DOWN);
        if ($block instanceof Stair && $block->getDamage() < 4) return false;
        if ($block instanceof Slab && $block->getDamage() < 8) return false;
        return $block->isSolid() && !$block->isTransparent();
    }
}