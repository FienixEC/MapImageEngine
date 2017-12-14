<?php
namespace FaigerSYS\MapImageEngine\item;

use pocketmine\item\Item;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;

use FaigerSYS\MapImageEngine\MapImageEngine;

class FilledMap extends Item {
	
	const CURRENT_MAP_API = 2;
	const SUPPORTED_MAP_API = [1, 2];
	
	public function __construct() {
		parent::__construct(self::FILLED_MAP ?? 358, 0, 1, 'Map');
	}
	
	public function setCompoundTag($tags): Item {
		parent::setCompoundTag($tags);
		$this->updateMapData();
		
		return $this;
	}
	
	public function setNamedTag(CompoundTag $tags): Item {
		parent::setNamedTag($tags);
		$this->updateMapData();
		
		return $this;
	}
	
	protected function updateMapData() {
		$plugin = MapImageEngine::getInstance();
		if (!$plugin) {
			return;
		}
		
		$tags = $this->getNamedTag();
		if (!isset($tags->mie_data)) {
			return;
		}
		
		$mie_data = json_decode((string) $tags->mie_data, true);
		
		$api = $mie_data['api'] ?? -1;;
		if (!in_array($api, self::SUPPORTED_MAP_API)) {
			$map_id = 0;
		} else {
			if ($api !== self::CURRENT_MAP_API) {
				if ($api === 1) {
					$mie_data['image_hash'] = $plugin->getImageStorage()->getNewHash($mie_data['image_hash']);
					if ($mie_data['image_hash'] === null) {
						return;
					}
				}
				
				$mie_data['api'] = self::CURRENT_MAP_API;
				$tags->mie_data = new StringTag('mie_data', json_encode($mie_data));
			}
			
			$map_id = $plugin->getImageStorage()->getMapId($mie_data['image_hash'], $mie_data['x_block'], $mie_data['y_block']) ?: 0;
		}
		
		$tags->map_uuid = new StringTag('map_uuid', (string) $map_id);
		
		parent::setNamedTag($tags);
	}
	
	public function setImageData(string $image_hash, int $x, int $y) {
		$tags = $this->getNamedTag() ?? new CompoundTag('', []);
		$tags->mie_data = new StringTag('mie_data', json_encode([
			'api'        => self::CURRENT_MAP_API,
			'image_hash' => $image_hash,
			'x_block'    => $x,
			'y_block'    => $y
		]));
		parent::setNamedTag($tags);
		
		$this->updateMapData();
	}
	
	public function getMaxStackSize() : int {
		return 1;

	}

}
