<?php

namespace WorldEditorM083;

# Base
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

# Other
use WorldEditorM083\Process;

class Main extends PluginBase implements Listener{

	private $p; // Process.php

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->p = new Process();
	}

}
