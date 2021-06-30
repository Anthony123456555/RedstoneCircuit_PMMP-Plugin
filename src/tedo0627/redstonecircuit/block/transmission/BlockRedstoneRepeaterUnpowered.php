<?php

namespace tedo0627\redstonecircuit\block\transmission;

use pocketmine\item\Item;
use pocketmine\Player;
use tedo0627\redstonecircuit\Facing;

class BlockRedstoneRepeaterUnpowered extends BlockRedstoneDiode {

    protected $id = self::UNPOWERED_REPEATER;

    public function getName(): string {
        return "Unpowered Repeater";
    }

    public function onActivate(Item $item, Player $player = null): bool {
        $damage = $this->getDamage();
        $this->setDamage(12 <= $damage ? $damage - 12 : $damage + 4);
        $this->getLevel()->setBlock($this, $this);
        return true;
    }

    public function onScheduledUpdate(): void {
        $this->getLevel()->setBlock($this, new BlockRedstoneRepeaterPowered($this->getDamage()));
        $this->updateDiodeRedstone(Facing::opposite($this->getFace()));
    }

    public function onRedstoneUpdate(): void {
        if ($this->isLocked()) return;
        if (!$this->isSidePowered($this, $this->getFace())) return;

        $this->getLevel()->scheduleDelayedBlockUpdate($this, ($this->getDamage() / 4 + 1) * 2);
    }

    public function isLocked(): bool {
        $face = Facing::rotate($this->getFace(), Facing::AXIS_Y, false);
        $block = $this->getSide($face);
        if ($block instanceof BlockRedstoneDiode && $block->getStrongPower($face) > 0) return true;

        $face = Facing::opposite($face);
        $block = $this->getSide($face);
        return $block instanceof BlockRedstoneDiode && $block->getStrongPower($face) > 0;
    }
}