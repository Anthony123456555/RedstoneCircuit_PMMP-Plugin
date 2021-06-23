<?php

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\LitRedstoneLamp;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\block\RedstoneUpdateTrait;

class BlockRedstoneLampLit extends LitRedstoneLamp implements IRedstoneComponent {
    use RedstoneUpdateTrait;
    use RedstoneComponentTrait;

    public function onScheduledUpdate(): void {
        if ($this->isBlockPowered($this)) return;
        $this->getLevel()->setBlock($this, new BlockRedstoneLamp());
    }

    public function onRedstoneUpdate(): void {
        if ($this->isBlockPowered($this)) return;
        $this->getLevel()->scheduleDelayedBlockUpdate($this, 8);
    }
}