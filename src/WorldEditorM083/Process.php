<?php

namespace WorldEditorM083;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\math\Vector3;

class Process extends PluginBase{

	private $data = [];
	private $undo = [];

	/**
	 * コピー
	 * @param  Player $player
	 * @param  int $x
	 * @param  int $y
	 * @param  int $z
	 * @return string
	 */
	public function copy(Player $player, $x, $y, $z){
		$level = $player->getLevel();
		$name = $player->getName();
		$data = $this->getData(__FUNCTION__, $name);

		if($data["flag"] === false){
			$data["pos"] = [$x, $y, $z];
			$data["flag"] = true;
			$this->setData(__FUNCTION__, $name, $data);
			return "select";
		}else{
			list($x2, $y2, $z2) = $data["pos"];
			$max = [
				"x" => max($x, $x2),
				"y" => max($y, $y2),
				"z" => max($z, $z2)
			];
			$min = [
				"x" => min($x, $x2),
				"y" => min($y, $y2),
				"z" => min($z, $z2)
			];
			for($xx = $min["x"]; $xx <= $max["x"]; $xx++){
				for($yy = $min["y"]; $yy <= $max["y"]; $yy++){
					for($zz = $min["z"]; $zz <= $max["z"]; $zz++){
						$data["copy"][] = [$xx, $yy, $zz, $level->getBlockIdAt($xx, $yy, $zz), $level->getBlockDataAt($xx, $yy, $zz)];
					}
				}
			}
			$this->setData(__FUNCTION__, $name, $data);
			return "copy";
		}
	}

	/**
	 * ペースト
	 * @param  Player $player
	 * @param  int $x
	 * @param  int $y
	 * @param  int $z
	 * @param  array $data ペーストするデータ(未指定の場合は自動で取得)
	 * @return string
	 */
	public function paste(Player $player, $x, $y, $z, $data = null){
		$level = $player->getLevel();
		$name = $player->getName();
		$data = !is_null($data) ? $this->getData("copy", $name) : $data;

		$undo = [];
		foreach ($data["copy"] as $value) {
			list($x, $y, $z, $id, $meta) = $value;
			$undo[] = [$x, $y, $z, $level->getBlockIdAt($x, $y, $z), $level->getBlockDataAt($x, $y, $z)];
			$level->setBlock(new Vector3($x, $y, $z), new Block($id, $meta));
		}
		$this->addUndo($name, $undo);
		return "execute";
	}

	/**
	 * Paste & 横 + 90°
	 * @param  Player $player
	 * @param  int $x
	 * @param  int $y
	 * @param  int $z
	 * @return string
	 */
	public function pasteNextTo90(Player $player, $x, $y, $z){
		$name = $player->getName();
		$copy = $this->getData("copy", $name)["copy"];
		$stairs = $this->getStairs();

		$data = [];
		foreach ($copy as $key => $value) {
			list($vx, $vy, $vz, $vid, $vmeta) = $value;
			if(in_array($vid, $stairs, true)){
				switch ($vmeta % 4) {
					case 0:
						$vmeta += 2;
						break;
					case 1:
						$vmeta += 2;
						break;
					case 2:
						$vmeta -= 1;
						break;
					case 3:
						$vmeta -= 3;
						break;
				}
			}
			if($vid === 17){
				if($vmeta >= 5 && $vmeta <= 13){
					($vmeta < 8) ? $vmeta += 4 : $vmeta -= 4;
				}
			}
			$data[] = [$vx, $vy, $vz, $vid, $vmeta];
		}
		return $this->paste($player, $x, $y, $z, $data);
	}

	/**
	 * Paste & 横 + 180°
	 * @param  Player $player
	 * @param  int $x
	 * @param  int $y
	 * @param  int $z
	 * @return string
	 */
	public function pasteNextTo180(Player $player, $x, $y, $z){
		$name = $player->getName();
		$copy = $this->getData("copy", $name)["copy"];
		$stairs = $this->getStairs();

		$data = [];
		foreach ($copy as $key => $value) {
			list($vx, $vy, $vz, $vid, $vmeta) = $value;
			if(in_array($vid, $stairs, true)){
				switch ($vmeta % 2) {
					case 0:
						$vmeta++;
						break;
					case 1:
						$vmeta--;
						break;
				}
			}
			if($vid === 106){
				switch($vmeta){
					case 1:
						$vmeta = 4;
						break;
					case 2:
						$vmeta = 8;
						break;
					case 4:
						$vmeta = 1;
						break;
					case 8:
						$vmeta = 2;
						break;
				}
			}
			$data[] = [$vx, $vy, $vz, $vid, $vmeta];
		}
		return $this->paste($player, $x, $y, $z, $data);
	}

	/**
	 * Paste & 横 + 270°
	 * @param  Player $player
	 * @param  int $x
	 * @param  int $y
	 * @param  int $z
	 * @return string
	 */
	public function pasteNextTo270(Player $player, $x, $y, $z){
		$name = $player->getName();
		$copy = $this->getData("copy", $name)["copy"];
		$stairs = $this->getStairs();

		$data = [];
		foreach ($copy as $key => $value) {
			list($vx, $vy, $vz, $vid, $vmeta) = $value;
			if(in_array($vid, $stairs, true)){
				switch ($vmeta % 4) {
					case 0:
						$vmeta += 3;
						break;
					case 1:
						$vmeta += 1;
						break;
					case 2:
					case 3:
						$vmeta -= 2;
						break;
				}
			}
			if($vid === 17){
				if($vmeta >= 4 && $vmeta <= 13){
					($vmeta < 8) ? $vmeta += 4 : $vmeta -= 4;
				}
			}
			$data[] = [$vx, $vy, $vz, $vid, $vmeta];
		}
		return $this->paste($player, $x, $y, $z, $data);
	}

	/**
	 * Paste & Xを軸に縦 + 90°
	 * @param  Player $player
	 * @param  int $x
	 * @param  int $y
	 * @param  int $z
	 * @return string
	 */
	public function pasteVertical90X(Player $player, $x, $y, $z){
		$name = $player->getName();
		$copy = $this->getData("copy", $name)["copy"];

		$data = [];
		foreach ($copy as $key => $value) {
			list($vx, $vy, $vz, $vid, $vmeta) = $value;
			$vx = $x + $vx;
			$vy = $y + $vz;
			$vz = $z + $vy;
			$data[] = [$vx, $vy, $vz, $vid, $vmeta];
		}
		return $this->paste($player, $x, $y, $z, $data);
	}

	/**
	 * Paste & Zを軸に縦 + 90°
	 * @param  Player $player
	 * @param  int $x
	 * @param  int $y
	 * @param  int $z
	 * @return string
	 */
	public function pasteVertical90Z(Player $player, $x, $y, $z){
		$name = $player->getName();
		$copy = $this->getData("copy", $name)["copy"];

		$data = [];
		foreach ($copy as $key => $value) {
			list($vx, $vy, $vz, $vid, $vmeta) = $value;
			$vx = $x + $vy;
			$vy = $y + $vx;
			$vz = $z + $vz;
			$data[] = [$vx, $vy, $vz, $vid, $vmeta];
		}
		return $this->paste($player, $x, $y, $z, $data);
	}

	/**
	 * Paste & 縦 + 90°
	 * @param  Player $player
	 * @param  int $x
	 * @param  int $y
	 * @param  int $z
	 * @return string
	 */
	public function pasteVertical180(Player $player, $x, $y, $z){
		$name = $player->getName();
		$copy = $this->getData("copy", $name)["copy"];

		$data = [];
		foreach ($copy as $key => $value) {
			list($vx, $vy, $vz, $vid, $vmeta) = $value;
			$vx = $x + $vx;
			$vy = $y + $vy;
			$vz = $z - $vz;
			$data[] = [$vx, $vy, $vz, $vid, $vmeta];
		}
		return $this->paste($player, $x, $y, $z, $data);
	}

	/**
	 * Paste & Xを軸に縦 + 270°
	 * @param  Player $player
	 * @param  int $x
	 * @param  int $y
	 * @param  int $z
	 * @return string
	 */
	public function pasteVertical270X(Player $player, $x, $y, $z){
		$name = $player->getName();
		$copy = $this->getData("copy", $name)["copy"];

		$data = [];
		foreach ($copy as $key => $value) {
			list($vx, $vy, $vz, $vid, $vmeta) = $value;
			$vx = $x + $vy;
			$vy = $y + $vz;
			$vz = $z - $vy;
			$data[] = [$vx, $vy, $vz, $vid, $vmeta];
		}
		return $this->paste($player, $x, $y, $z, $data);
	}

	/**
	 * Paste & Zを軸に縦 + 270°
	 * @param  Player $player
	 * @param  int $x
	 * @param  int $y
	 * @param  int $z
	 * @return string
	 */
	public function pasteVertical270X(Player $player, $x, $y, $z){
		$name = $player->getName();
		$copy = $this->getData("copy", $name)["copy"];

		$data = [];
		foreach ($copy as $key => $value) {
			list($vx, $vy, $vz, $vid, $vmeta) = $value;
			$vx = $x - $vy;
			$vy = $y + $vx;
			$vz = $z + $vz;
			$data[] = [$vx, $vy, $vz, $vid, $vmeta];
		}
		return $this->paste($player, $x, $y, $z, $data);
	}

	/**
	 * Paste & Xを軸に線対称
	 * @param  Player $player
	 * @param  int $x
	 * @param  int $y
	 * @param  int $z
	 * @return string
	 */
	public function pasteLineSymmetryX(Player $player, $x, $y, $z){
		$name = $player->getName();
		$copy = $this->getData("copy", $name)["copy"];

		$data = [];
		foreach ($copy as $key => $value) {
			list($vx, $vy, $vz, $vid, $vmeta) = $value;
			$vx = $x + $vx;
			$vy = $y + $vy;
			$vz = $z - $vz;
			$data[] = [$vx, $vy, $vz, $vid, $vmeta];
		}
		return $this->paste($player, $x, $y, $z, $data);
	}

	/**
	 * Paste & Zを軸に線対称
	 * @param  Player $player
	 * @param  int $x
	 * @param  int $y
	 * @param  int $z
	 * @return string
	 */
	public function pasteLineSymmetryZ(Player $player, $x, $y, $z){
		$name = $player->getName();
		$copy = $this->getData("copy", $name)["copy"];

		$data = [];
		foreach ($copy as $key => $value) {
			list($vx, $vy, $vz, $vid, $vmeta) = $value;
			$vx = $x - $vx;
			$vy = $y + $vy;
			$vz = $z + $vz;
			$data[] = [$vx, $vy, $vz, $vid, $vmeta];
		}
		return $this->paste($player, $x, $y, $z, $data);
	}

	/**
	 * 2倍の大きさでペースト
	 * @param  Player $player
	 * @param  int $x
	 * @param  int $y
	 * @param  int $z
	 * @return string
	 */
	public function pasteDoubleSize(Player $player, $x, $y, $z){
		$name = $player->getName();
		$copy = $this->getData("copy", $name)["copy"];

		$data = [];
		foreach ($copy as $key => $value) {
			list($vx, $vy, $vz, $vid, $vmeta) = $value;
			for($a = 0; $a <= 1; $a++){
				for($b = 0; $b <= 1; $b++){
					for($c = 0; $c <= 1; $c++){
						$vx = $x + $vx * 2 + $a;
						$vy = $y + $vy * 2 + $b;
						$vz + $z + $vz * 2 + $c;
						$data[] = [$vx, $vy, $vz, $vid, $vmeta];
					}
				}
			}
		}
		return $this->paste($player, $x, $y, $z, $data);
	}

	/**
	 * 埋める
	 * @param  Player $player
	 * @param  int    $x
	 * @param  int    $y
	 * @param  int    $z
	 * @param  int    $target 置き換え対象のブロックID
	 * @param  string $magic  内部関数用の変数
	 * @return string
	 */
	public function fill(Player $player, $x, $y, $z, $target, $magic = ""){
		$level = $player->getLevel();
		$name = $player->getName().$magic;
		$data = $this->getData(__FUNCTION__, $name);

		if($data["flag"] === false){
			$data["pos"] = [$x, $y, $z];
			$data["flag"] = true;
			$this->setData(__FUNCTION__, $name, $data);
			return "select";
		}else{
			list($x2, $y2, $z2) = $data["pos"];
			$max = [
				"x" => max($x, $x2),
				"y" => max($y, $y2),
				"z" => max($z, $z2)
			];
			$min = [
				"x" => min($x, $x2),
				"y" => min($y, $y2),
				"z" => min($z, $z2)
			];
			$undo = [];
			for($xx = $min["x"]; $xx <= $max["x"]; $xx++){
				for($yy = $min["y"]; $yy <= $max["y"]; $yy++){
					for($zz = $min["z"]; $zz <= $max["z"]; $zz++){
						$undo[] = [$xx, $yy, $zz, $level->getBlockIdAt($xx, $yy, $zz), $level->getBlockDataAt($xx, $yy, $zz)];
						$level->setBlock(new Vector3($xx, $yy, $zz), new Block($target));
					}
				}
			}
			$this->addUndo($name, $undo);
			$data["flag"] = false;
			$this->setData(__FUNCTION__, $name, $data);
			return "execute";
		}
	}

	/**
	 * 選択範囲内のブロックを削除(空気ブロックに変換)
	 * @param  Player $player
	 * @param  int    $x
	 * @param  int    $y
	 * @param  int    $z
	 * @return string
	 */
	public function fillAir(Player $player, $x, $y, $z){
		return $this->fill($player, $x, $y, $z, 0, __FUNCTION__);
	}


//----------
// Util
//----------
	/**
	 * 階段ブロックのID一覧を返します
	 * @return array
	 */
	private function getStairs(){
		return [53,67,108,109,114,128,134,135,136,156,163,164];
	}

	/**
	 * 編集を取り消します
	 * @param  Player $player
	 * @param  int  $num Undoする回数
	 * @return bool
	 */
	public function undo(Player $player, $num = 1){
		$level = $player->getLevel();

		$i = 0;
		foreach($this->undo as $key => $value){
			if($i < $num){
				foreach ($value as $undo) {
					list($x, $y, $z, $id, $meta) = $undo;
					$level->setBlock(new Vector3($x, $y, $z), new Block($id, $meta));
				}
				unset($this->undo[$key]);
				$i++;
			}else{
				break;
			}
		}
	}

	/**
	 * (NOT API) Undoデータを追加します
	 * @param array $data
	 */
	private function addUndo($name, $data){
		$this->undo[] = $data;
	}

	/**
	 * (NOT API) データを取得
	 * @param string $type
	 * @param string $name
	 * @return array
	 */
	private function getData($type, $name){
		return isset($this->data[$name][$type]) ? $this->data[$name][$type] : null;
	}

	/**
	 * (NOT API) データを代入
	 * @param string $type
	 * @param string $name
	 * @param array  $data
	 */
	private function setData($type, $name, $data){
		$this->data[$name][$type] = $data;
	}

}
