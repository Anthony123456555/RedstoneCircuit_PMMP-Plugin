<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\Block;
use pocketmine\block\Redstone;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use tedo0627\redstonecircuit\block\helper\IRedstoneComponent;
use tedo0627\redstonecircuit\block\helper\IRedstoneUpdate;
use tedo0627\redstonecircuit\block\helper\RedstoneComponentTrait;
use tedo0627\redstonecircuit\block\helper\RedstoneUpdateTrait;

class BlockRedstone extends Redstone implements IRedstoneComponent, IRedstoneUpdate {
    use RedstoneComponentTrait;
    use RedstoneUpdateTrait;

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null): bool {
        $bool = parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
        $this->updateAroundRedstone($this);
        return $bool;
    }

    public function onBreak(Item $item, Player $player = null): bool {
        $bool = parent::onBreak($item, $player);
        $this->updateAroundRedstone($this);
        return $bool;
    }

    public function getWeakPower(int $face): int {
        return 15;
    }

    public function isPowerSource(): bool {
        return true;
    }
}