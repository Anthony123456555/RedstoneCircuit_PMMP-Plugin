<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Block;
use pocketmine\block\RedstoneLamp;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\block\RedstoneUpdateTrait;

class BlockRedstoneLamp extends RedstoneLamp implements IRedstoneComponent {
    use RedstoneUpdateTrait;
    use RedstoneComponentTrait;

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null): bool {
        $block = $this->isBlockPowered($this) ? new BlockRedstoneLampLit() : $this;
        return $this->getLevel()->setBlock($this, $block);
    }

    public function onRedstoneUpdate(): void {
        if (!$this->isBlockPowered($this)) return;
        $this->getLevel()->setBlock($this, new BlockRedstoneLampLit());
    }
}