<?php

namespace tedo0627\redstonecircuit\block\helper;

interface IRedstoneComponent {

    /**
     * Returns the redstone power through the block.
     */
    public function getStrongPower(int $face): int;

    /**
     * Returns the redstone power that does not through the block.
     */
    public function getWeakPower(int $face): int;

    /**
     * Returns whether this block is the source of the redstone power.
     */
    public function isPowerSource(): bool;

    /**
     * Called when this block is redstone power updated.
     */
    public function onRedstoneUpdate(): void;
}