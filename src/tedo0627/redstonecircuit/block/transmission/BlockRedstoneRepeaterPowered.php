<?php

namespace tedo0627\redstonecircuit\block\transmission;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use tedo0627\redstonecircuit\Facing;

class BlockRedstoneRepeaterPowered extends BlockRedstoneRepeaterUnpowered {

    protected $id = self::POWERED_REPEATER;

    public function getName(): string {
        return "Powered Repeater";
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null): bool {
        $bool = parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
        $this->updateDiodeRedstone(Facing::opposite($this->getFace()));
        return $bool;
    }

    public function onBreak(Item $item, Player $player = null): bool {
        $bool = parent::onBreak($item, $player);
        $this->updateDiodeRedstone(Facing::opposite($this->getFace()));
        return $bool;
    }

    public function onScheduledUpdate(): void {
        $this->getLevel()->setBlock($this, new BlockRedstoneRepeaterUnpowered($this->getDamage()));
        $this->updateDiodeRedstone(Facing::opposite($this->getFace()));
    }

    public function onRedstoneUpdate(): void {
        if ($this->isLocked()) return;
        if ($this->isSidePowered($this, $this->getFace())) return;

        $this->getLevel()->scheduleDelayedBlockUpdate($this, ($this->getDamage() / 4 + 1) * 2);
    }

    public function getStrongPower(int $face): int {
        return $this->getWeakPower($face);
    }

    public function getWeakPower(int $face): int {
        return $face == $this->getFace() ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return true;
    }
}