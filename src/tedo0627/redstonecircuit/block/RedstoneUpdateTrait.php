<?php

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use tedo0627\redstonecircuit\Facing;

trait RedstoneUpdateTrait {

    /**
     * Returns the target Level, or null if the target is not valid.
     * If a reference exists to a Level which is closed, the reference will be destroyed and null will be returned.
     *
     * @return Level|null
     */
    public abstract function getLevel();

    private function getBlock(): Block {
        return $this instanceof Block ? $this : Block::get(0);
    }

    public function updateAroundRedstone(?int $ignoreDirection = null, Block $center = null): void {
        if ($center == null) $center = $this->getBlock();

        $directions = Facing::ALL;
        for ($i = 0; $i < count($directions); $i++) {
            $direction = $directions[$i];
            if ($ignoreDirection == $direction) continue;

            $block = $center->getSide($direction);
            if ($block instanceof IRedstoneComponent) $block->onRedstoneUpdate();
        }
    }

    public function updateAroundDirectionRedstone(int $direction): void {
        $this->updateAroundRedstone();
        $this->updateAroundRedstone(Facing::opposite($direction), $this->getBlock()->getSide($direction));
    }

    public function updateAroundStrongRedstone(): void {
        $directions = Facing::ALL;
        $center = $this->getBlock();
        for ($i = 0; $i < count($directions); $i++) {
            $direction = $directions[$i];
            $block = $center->getSide($direction);
            if ($block instanceof IRedstoneComponent) $block->onRedstoneUpdate();

            for ($j = 0; $j < count($directions); $j++) {
                $sideDirection = $directions[$j];
                if (Facing::axis($direction) == Facing::AXIS_Y && Facing::axis($sideDirection) != Facing::AXIS_Y) continue;
                if (Facing::axis($direction) != Facing::AXIS_Y && Facing::rotate($direction, Facing::AXIS_Y, true) == $sideDirection) continue;

                $sideBlock = $block->getSide($sideDirection);
                if ($sideBlock instanceof IRedstoneComponent) $sideBlock->onRedstoneUpdate();
            }
        }
    }

    public function getRedstonePower(Vector3 $pos, ?int $face = null): int {
        $block = $this->getLevel()->getBlock($pos);
        return BlockPowerHelper::isNormalBlock($block) ? $this->getAroundStrongPower($pos) : BlockPowerHelper::getWeakPower($block, $face);
    }

    public function isBlockPowered(Vector3 $pos, ?int $face = null): bool {
        $directions = Facing::ALL;
        for ($i = 0; $i < count($directions); $i++) {
            $direction = $directions[$i];
            if ($face == $direction) continue;
            if ($this->isSidePowered($pos, $direction)) return true;
        }

        return false;
    }

    public function isSidePowered(Vector3 $pos, int $face): bool {
        return $this->getRedstonePower($pos->getSide($face), $face) > 0;
    }

    public function getAroundStrongPower(Vector3 $pos): int {
        $power = 0;
        $directions = Facing::ALL;
        for ($i = 0; $i < count($directions); $i++) {
            $direction = $directions[$i];
            $power = max($power, $this->getSideStrongPower($pos->getSide($direction), $direction));
            if ($power >= 15) return $power;
        }

        return $power;
    }

    public function getSideStrongPower(Vector3 $pos, int $face): int {
        return BlockPowerHelper::getStrongPower($this->getLevel()->getBlock($pos), $face);
    }
}
