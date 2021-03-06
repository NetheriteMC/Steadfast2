<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
 */

namespace pocketmine\item;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Liquid;
use pocketmine\block\Slab;
use pocketmine\block\Slab2;
use pocketmine\block\WoodSlab;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\level\Level;
use pocketmine\Player;

class Bucket extends Item {
	
	const BUCKET_EMPTY = 0;
	const BUCKET_MILK = 1;
	const BUCKET_WATER = 8;
	const BUCKET_LAVA = 10;

	public function __construct($meta = 0, $count = 1) {
		parent::__construct(self::BUCKET, $meta, $count, "Bucket");
	}

	public function getMaxStackSize() {
		return 1;
	}

	public function canBeActivated() {
		return true;
	}

	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if ($block instanceof Slab || $block instanceof Slab2 || $block instanceof WoodSlab) {
			return false;
		}
		$targetBlock = Block::get($this->meta);

		if ($targetBlock instanceof Air) {
			if ($target instanceof Liquid and $target->getDamage() === 0) {
				$result = clone $this;
				$result->setDamage($target->getId());
				$result->setCount(1);
				$player->getServer()->getPluginManager()->callEvent($ev = new PlayerBucketFillEvent($player, $block, $face, $this, $result));
				if (!$ev->isCancelled()) {
					$player->getLevel()->setBlock($target, new Air(), true, true);
					if($player->isSurvival()){
						if ($this->count <= 1) {
							$player->getInventory()->setItemInHand($ev->getItem(), $player);
						} else {
							$this->count--;
							$player->getInventory()->addItem($ev->getItem());
						}
					}
					return true;
				} else {
					$player->getInventory()->sendContents($player);
				}
			}
		} elseif ($targetBlock instanceof Liquid) {
			$result = clone $this;
			$result->setDamage(0);
			$player->getServer()->getPluginManager()->callEvent($ev = new PlayerBucketFillEvent($player, $block, $face, $this, $result));
			if (!$ev->isCancelled()) {
				$player->getLevel()->setBlock($block, $targetBlock, true, true);
				if ($player->isSurvival()) {
					$player->getInventory()->setItemInHand($ev->getItem(), $player);
				}
				return true;
			} else {
				$player->getInventory()->sendContents($player);
			}
		}

		return false;
	}

}
