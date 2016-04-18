<?php
/**
 * src/pocketmine/item/DiamondChestplate.php
 *
 * @package default
 */


/*
 *
 *  _                       _           _ __  __ _
 * (_)                     (_)         | |  \/  (_)
 *  _ _ __ ___   __ _  __ _ _  ___ __ _| | \  / |_ _ __   ___
 * | | '_ ` _ \ / _` |/ _` | |/ __/ _` | | |\/| | | '_ \ / _ \
 * | | | | | | | (_| | (_| | | (_| (_| | | |  | | | | | |  __/
 * |_|_| |_| |_|\__,_|\__, |_|\___\__,_|_|_|  |_|_|_| |_|\___|
 *                     __/ |
 *                    |___/
 *
 * This program is a third party build by ImagicalMine.
 *
 * PocketMine is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ImagicalMine Team
 * @link http://forums.imagicalcorp.ml/
 *
 *
*/

namespace pocketmine\item;


class DiamondChestplate extends Armor{

	/**
	 *
	 * @param unknown $meta  (optional)
	 * @param unknown $count (optional)
	 */
	public function __construct($meta = 0, $count = 1) {
		parent::__construct(self::DIAMOND_CHESTPLATE, $meta, $count, "Diamond Chestplate");
	}


	/**
	 *
	 * @return unknown
	 */
	public function isChestplate() {
		return self::TIER_DIAMOND;
	}


	/**
	 *
	 * @return unknown
	 */
	public function getProtection() {
		return parent::getProtection() + 8;
	}


	/**
	 *
	 * @return unknown
	 */
	public function getMaxDurability() {
		return 529;
	}


}