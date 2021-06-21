<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\Block;
use pocketmine\block\Slab;
use pocketmine\block\Stair;

trait FlowablePlaceTrait {

    /**
     * Returns the Block on the side $side, works like Vector3::getSide()
     *
     * @return Block
     */
    public abstract function getSide(int $side, int $step = 1);

    public function canPlaceFlowable(int $side): bool {
        $block = $this->getSide($side);
        if ($block instanceof Stair && $block->getDamage() < 4) return false;
        if ($block instanceof Slab && $block->getDamage() < 8) return false;
        return $block->isSolid() && !$block->isTransparent();
    }
}