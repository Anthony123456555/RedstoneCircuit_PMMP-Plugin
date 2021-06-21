<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Block;
use pocketmine\block\StoneButton;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use tedo0627\redstonecircuit\block\FlowablePlaceTrait;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\block\RedstoneUpdateTrait;
use tedo0627\redstonecircuit\Facing;

class BlockStoneButton extends StoneButton implements IRedstoneComponent {
    use FlowablePlaceTrait;
    use RedstoneComponentTrait;
    use RedstoneUpdateTrait;

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null): bool {
        if (!$this->canPlaceFlowable(Facing::opposite($face))) return false;

        $bool = parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
        $this->updateAroundRedstone($this);
        return $bool;
    }

    public function onBreak(Item $item, Player $player = null): bool {
        $bool = parent::onBreak($item, $player);
        $this->updateAroundRedstone($this);
        return $bool;
    }

    public function onNearbyBlockChange(): void {
        if ($this->canPlaceFlowable($this->getFace())) return;
        $this->level->useBreakOn($this);
    }

    public function onScheduledUpdate(): void {
        if (!$this->isPowerSource()) return;

        $this->setDamage($this->getDamage() - 8);
        $this->level->setBlock($this, $this);
        $this->level->broadcastLevelSoundEvent($this->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_POWER_OFF);
        $this->updateAroundDiodeRedstone($this);
    }

    public function onActivate(Item $item, Player $player = null): bool {
        if ($this->isPowerSource()) return true;

        $this->setDamage($this->getDamage() + 8);
        $this->level->setBlock($this, $this);
        $this->level->broadcastLevelSoundEvent($this->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_POWER_ON);
        $this->updateAroundDiodeRedstone($this);
        $this->level->scheduleDelayedBlockUpdate($this, 20);
        return true;
    }

    private function getFace(): int {
        $damage = $this->getDamage();
        if ($damage > 8) $damage -= 8;
        return Facing::opposite($damage);
    }

    public function getStrongPower(int $face): int {
        return $face == Facing::opposite($this->getFace()) && $this->isPowerSource() ? 15 : 0;
    }

    public function getWeakPower(int $face): int {
        return $this->isPowerSource() ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return $this->getDamage() > 8;
    }
}