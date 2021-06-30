<?php

namespace tedo0627\redstonecircuit\block\transmission;

use pocketmine\block\Block;
use pocketmine\block\Flowable;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use tedo0627\redstonecircuit\block\FlowablePlaceTrait;
use tedo0627\redstonecircuit\block\IDirectional;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\block\RedstoneUpdateTrait;
use tedo0627\redstonecircuit\Facing;

abstract class BlockRedstoneDiode extends Flowable implements IRedstoneComponent, IDirectional {
    use FlowablePlaceTrait;
    use RedstoneComponentTrait;
    use RedstoneUpdateTrait;

    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getVariantBitmask() : int {
        return 0;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null): bool {
        if (!$this->canPlaceFlowable(Facing::DOWN)) return false;

        $faces = [
            0 => 1,
            1 => 2,
            2 => 3,
            3 => 0
        ];
        $this->setDamage($faces[$player instanceof Player ? $player->getDirection() : 0]);
        return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onNearbyBlockChange(): void {
        if (!$this->canPlaceFlowable(Facing::DOWN)) $this->getLevel()->useBreakOn($this);
    }

    public function getFace(): int {
        $faces = [
            0 => Facing::NORTH,
            1 => Facing::EAST,
            2 => Facing::SOUTH,
            3 => Facing::WEST,
        ];
        return Facing::opposite($faces[$this->getDamage() % 4]);
    }
}