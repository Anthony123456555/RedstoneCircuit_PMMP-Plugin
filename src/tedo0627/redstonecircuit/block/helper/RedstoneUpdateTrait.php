<?php

namespace tedo0627\redstonecircuit\block\helper;

use LogicException;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use tedo0627\redstonecircuit\Facing;

/**
 * @see IRedstoneUpdate
 */
trait RedstoneUpdateTrait {

    /** @var Level|null */
    public $level;

    public function updateAroundRedstone(Vector3 $pos, ?int $face = null): void {
        if ($this->level == null) throw new LogicException("Block's level property is null");

        $directions = Facing::ALL;
        for ($i = 0; $i < count($directions); $i++) {
            $direction = $directions[$i];
            if ($face != null && $face == $direction) continue;

            $block = $this->level->getBlock($pos->getSide($direction));
            if ($block instanceof IRedstoneComponent) $block->onRedstoneUpdate();
        }
    }

    public function updateAroundDiodeRedstone(Vector3 $pos, ?int $face = null): void {
        $this->updateAroundRedstone($pos);
        $directions = Facing::ALL;
        for ($i = 0; $i < count($directions); $i++) {
            $direction = $directions[$i];
            if ($direction != $face) $this->updateAroundRedstone($pos->getSide($direction));
        }
    }

    public function getRedstonePower(Vector3 $pos, ?int $face = null): int {
        if ($this->level == null) throw new LogicException("Block's level property is null");

        $block = $this->level->getBlock($pos);
        return BlockPowerHelper::isNormalBlock($block) ? $this->getStrongPowered($pos) : BlockPowerHelper::getWeakPower($block, $face);
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

    public function getStrongPowered(Vector3 $pos): int {
        $power = 0;
        $directions = Facing::ALL;
        for ($i = 0; $i < count($directions); $i++) {
            $direction = $directions[$i];
            $power = max($power, $this->getSideStrongPowered($pos->getSide($direction), $direction));
            if ($power >= 15) return $power;
        }

        return $power;
    }

    public function getSideStrongPowered(Vector3 $pos, int $face): int {
        if ($this->level == null) throw new LogicException("Block's level property is null");
        return BlockPowerHelper::getStrongPower($this->level->getBlock($pos), $face);
    }
}
