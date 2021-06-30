<?php

namespace tedo0627\redstonecircuit\block\power;

use LogicException;
use pocketmine\block\Block;
use pocketmine\block\WoodenButton;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use tedo0627\redstonecircuit\block\FlowablePlaceTrait;
use tedo0627\redstonecircuit\block\IDirectional;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\block\RedstoneUpdateTrait;
use tedo0627\redstonecircuit\Facing;

class BlockWoodenButton extends WoodenButton implements IRedstoneComponent, IDirectional {
    use FlowablePlaceTrait;
    use RedstoneComponentTrait;
    use RedstoneUpdateTrait;

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null): bool {
        if (!$this->canPlaceFlowable(Facing::opposite($face))) return false;

        $bool = parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
        $this->updateAroundDirectionRedstone(Facing::opposite($this->getFace()));
        return $bool;
    }

    public function onBreak(Item $item, Player $player = null): bool {
        $bool = parent::onBreak($item, $player);
        $this->updateAroundDirectionRedstone(Facing::opposite($this->getFace()));
        return $bool;
    }

    public function onNearbyBlockChange(): void {
        if ($this->canPlaceFlowable(Facing::opposite($this->getFace()))) return;
        $this->getLevel()->useBreakOn($this);
    }

    public function onScheduledUpdate(): void {
        if (!$this->isPowerSource()) return;

        $entities = $this->getLevel()->getNearbyEntities($this->getCollisionBoundingBox());
        foreach ($entities as $entitiy) {
            if ($entitiy instanceof Arrow) return;
        }

        $this->toggleButton(false);
    }

    public function onActivate(Item $item, Player $player = null): bool {
        if ($this->isPowerSource()) return true;

        echo "damage" . $this->getDamage() . "\n";
        $this->toggleButton(true);
        $this->getLevel()->scheduleDelayedBlockUpdate($this, 30);
        return true;
    }

    public function onEntityCollide(Entity $entity): void {
        if (!($entity instanceof Arrow)) return;
        if (!$this->getCollisionBoundingBox()->intersectsWith($entity->getBoundingBox())) return;

        if (!$this->isPowerSource()) $this->toggleButton(true);
        $this->getLevel()->scheduleDelayedBlockUpdate($this, 1);
    }

    public function hasEntityCollision(): bool {
        return true;
    }

    public function getFace(): int {
        $damage = $this->getDamage();
        return 8 <= $damage ? $damage - 8 : $damage;
    }

    private function toggleButton(bool $toggle): void {
        $damage = $this->getDamage();
        if ($toggle && $damage < 8) $this->setDamage($damage + 8);
        if (!$toggle && 8 <= $damage) $this->setDamage($damage - 8);

        $this->getLevel()->setBlock($this, $this);
        $soundId = $toggle ? LevelSoundEventPacket::SOUND_POWER_ON : LevelSoundEventPacket::SOUND_POWER_OFF;
        $this->getLevel()->broadcastLevelSoundEvent($this->add(0.5, 0.5, 0.5), $soundId);
        echo "update " . Facing::opposite($this->getFace()) . "\n";
        $this->updateAroundDirectionRedstone(Facing::opposite($this->getFace()));
    }

    protected function getCollisionBoundingBox(): AxisAlignedBB {
        $bb = null;
        $face = $this->getFace();
        if ($face == Facing::UP) {
            $bb = new AxisAlignedBB(6.0, 0.0, 5.0, 10.0, 2.0, 11.0);
        } else if ($face == Facing::DOWN) {
            $bb = new AxisAlignedBB(6.0, 14.0, 5.0, 10.0, 16.0, 11.0);
        } else if ($face == Facing::NORTH) {
            $bb = new AxisAlignedBB(5.0, 6.0, 14.0, 11.0, 10.0, 16.0);
        } else if ($face == Facing::SOUTH) {
            $bb = new AxisAlignedBB(5.0, 6.0, 0.0, 11.0, 10.0, 2.0);
        } else if ($face == Facing::WEST) {
            $bb = new AxisAlignedBB(14.0, 6.0, 5.0, 16.0, 10.0, 11.0);
        } else if ($face == Facing::EAST) {
            $bb = new AxisAlignedBB(0.0, 6.0, 5.0, 2.0, 10.0, 11.0);
        }
        if ($bb == null) throw new LogicException("face " . $face . " isn't match");
        $bb->setBounds($bb->minX / 16, $bb->minY / 16, $bb->minZ / 16, $bb->maxX / 16, $bb->maxY / 16, $bb->maxZ / 16);
        return $bb->offset($this->getX(), $this->getY(), $this->getZ());
    }

    public function getStrongPower(int $face): int {
        return $face == $this->getFace() && $this->isPowerSource() ? 15 : 0;
    }

    public function getWeakPower(int $face): int {
        return $this->isPowerSource() ? 15 : 0;
    }

    public function isPowerSource(): bool {
        return 8 <= $this->getDamage();
    }
}