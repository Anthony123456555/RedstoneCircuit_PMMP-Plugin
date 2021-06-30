<?php

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Block;
use pocketmine\block\StoneButton;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use tedo0627\redstonecircuit\block\FlowablePlaceTrait;
use tedo0627\redstonecircuit\block\IDirectional;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\block\RedstoneUpdateTrait;
use tedo0627\redstonecircuit\Facing;

class BlockStoneButton extends StoneButton implements IRedstoneComponent, IDirectional {
    use FlowablePlaceTrait;
    use RedstoneComponentTrait;
    use RedstoneUpdateTrait;

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null): bool {
        if (!$this->canPlaceFlowable(Facing::opposite($face))) return false;

        $bool = parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
        $this->updateAroundDirectionRedstone($this->getFace());
        return $bool;
    }

    public function onBreak(Item $item, Player $player = null): bool {
        $bool = parent::onBreak($item, $player);
        $this->updateAroundDirectionRedstone($this->getFace());
        return $bool;
    }

    public function onNearbyBlockChange(): void {
        if ($this->canPlaceFlowable($this->getFace())) return;
        $this->getLevel()->useBreakOn($this);
    }

    public function onScheduledUpdate(): void {
        if (!$this->isPowerSource()) return;

        $this->toggleButton(false);
    }

    public function onActivate(Item $item, Player $player = null): bool {
        if ($this->isPowerSource()) return true;

        $this->toggleButton(true);
        $this->getLevel()->scheduleDelayedBlockUpdate($this, 20);
        return true;
    }

    public function getFace(): int {
        $damage = $this->getDamage();
        if (8 <= $damage) $damage -= 8;
        return Facing::opposite($damage);
    }

    private function toggleButton(bool $toggle): void {
        $damage = $this->getDamage();
        if ($toggle && $damage < 8) $this->setDamage($damage + 8);
        if (!$toggle && 8 <= $damage) $this->setDamage($damage - 8);

        $this->getLevel()->setBlock($this, $this);
        $soundId = $toggle ? LevelSoundEventPacket::SOUND_POWER_ON : LevelSoundEventPacket::SOUND_POWER_OFF;
        $this->getLevel()->broadcastLevelSoundEvent($this->add(0.5, 0.5, 0.5), $soundId);
        $this->updateAroundDirectionRedstone($this->getFace());
    }

    public function getStrongPower(int $face): int {
        return $face == Facing::opposite($this->getFace()) && $this->isPowerSource() ? 15 : 0;
    }

    public function getWeakPower(int $face): int {
        return $this->isPowerSource() ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return 8 <= $this->getDamage();
    }
}