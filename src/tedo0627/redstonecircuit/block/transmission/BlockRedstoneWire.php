<?php

namespace tedo0627\redstonecircuit\block\transmission;

use pocketmine\block\Block;
use pocketmine\block\Flowable;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\FlowablePlaceTrait;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\block\RedstoneUpdateTrait;
use tedo0627\redstonecircuit\Facing;

class BlockRedstoneWire extends Flowable implements IRedstoneComponent {
    use FlowablePlaceTrait;
    use RedstoneComponentTrait;
    use RedstoneUpdateTrait;

    protected $id = self::REDSTONE_WIRE;
    protected $itemId = Item::REDSTONE;

    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string {
        return "Redstone Wire";
    }

    public function getVariantBitmask() : int {
        return 0;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        if (!$this->canPlaceFlowable()) return false;

        $bool = parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
        $this->calculatePower();
        return $bool;
    }

    public function onBreak(Item $item, Player $player = null) : bool {
        $bool = parent::onBreak($item, $player);
        $this->updateAroundDiodeRedstone($this);
        return $bool;
    }

    public function onNearbyBlockChange() : void {
        if ($this->canPlaceFlowable()) {
            $this->calculatePower();
        } else {
            $this->level->useBreakOn($this);
        }
    }

    public function getWeakPower(int $face): int {
        return $this->getDamage();
    }

    public function onRedstoneUpdate(): void {
        $this->calculatePower();
    }

    private function calculatePower(): void {
        $power = 0;
        $directions = Facing::ALL;
        for ($i = 0; $i < count($directions); $i++) {
            $direction = $directions[$i];
            $block = $this->getSide($direction);
            if ($block instanceof BlockRedstoneWire) {
                $power = max($power, $block->getWeakPower($direction) - 1);
                continue;
            }

            if (BlockPowerHelper::isPowerSource($block)) {
                $power = max($power, BlockPowerHelper::getWeakPower($block, $direction));
                continue;
            }

            if (BlockPowerHelper::isNormalBlock($block)) {
                for ($j = 0; $j < count($directions); $j++) {
                    $face = $directions[$j];
                    if ($direction == Facing::opposite($face)) continue;

                    $power = max($power, BlockPowerHelper::getStrongPower($block->getSide($face), $face));
                }
            }

            if ($block->isTransparent()) {
                if ($direction == Facing::UP) {
                    $horizontal = Facing::HORIZONTAL;
                    for ($j = 0; $j < count($horizontal); $j++) {
                        $face = $horizontal[$j];
                        $sideBlock = $block->getSide($face);
                        if (!($sideBlock instanceof BlockRedstoneWire)) continue;

                        $power = max($power, $sideBlock->getWeakPower($face) - 1);
                    }
                    continue;
                }

                if ($direction == Facing::DOWN) continue;

                $downBlock = $block->getSide(Facing::DOWN);
                if (!($downBlock instanceof BlockRedstoneWire)) continue;

                $power = max($power, $downBlock->getWeakPower(Facing::UP) - 1);
            }
        }

        if ($this->getDamage() == $power) return;

        $this->setDamage($power);
        $this->level->setBlock($this, $this);
        $this->updateAroundDiodeRedstone($this);
    }
}