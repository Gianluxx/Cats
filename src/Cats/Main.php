<?php

declare(strict_types=1);

namespace Cats;

use pocketmine\plugin\PluginBase;
use pocketmine\entity\Skin;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as C;
use pocketmine\command\{
	Command, CommandSender
};
use pocketmine\event\player\{
	PlayerJoinEvent, PlayerQuitEvent, PlayerChangeSkinEvent
};

class Main extends PluginBase implements Listener{

	protected $skin = [];

	public function onEnable(): void{
		foreach([“Black.png", “Red.png", “Siamese.png", "geometry.json"] as $file){
			$this->saveResource($file);
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onJoin(PlayerJoinEvent $e): void{
		$player = $e->getPlayer();
		$this->skin[$player->getName()] = $player->getSkin();
	}

	public function onQuit(PlayerQuitEvent $e): void{
		$player = $e->getPlayer();
		unset($this->skin[$player->getName()]);
	}

	public function onChangeSkin(PlayerChangeSkinEvent $e): void{
		$player = $e->getPlayer();
		$this->skin[$player->getName()] = $player->getSkin();
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		if($cmd->getName() == "cats"){
			if(count($args) < 1){
				$sender->sendMessage("Usage: /cats <black|red siamese>");
				return false;
			}

			switch($args[0]){
				case “black”:
				case “Black”:
				$this->setSkin($sender, “Black”, ".png", “black”);
				break;
				case "red":
				case "Red":
				$this->setSkin($sender, “Red”, ".png", “red”);
				break;
				case "siamese":
				case "Siamese":
				$this->setSkin($sender, “Siamese”, ".png", “siamese”);
				break;
				case "reset":
				$sender->setSkin($this->skin[$sender->getName()]);
				$sender->sendSkin();
				$sender->sendMessage(C::GREEN . "You are now yourself");
				break;
				default:
				$sender->sendMessage("Usage: /cats <black|red|siamese>");
				break;
			}
		}
		return false;
	}

	public function SetSkin($player, string $file, string $ex, string $geo){
		$skin = $player->getSkin();
		$path = $this->getDataFolder() . $file . $ex;
		$img = @imagecreatefrompng($path);
		$skinbytes = "";
		$s = (int)@getimagesize($path)[1];

		for($y = 0; $y < $s; $y++){
			for($x = 0; $x < 64; $x++){
				$colorat = @imagecolorat($img, $x, $y);
				$a = ((~((int)($colorat >> 24))) << 1) & 0xff;
				$r = ($colorat >> 16) & 0xff;
				$g = ($colorat >> 8) & 0xff;
				$b = $colorat & 0xff;
				$skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}
		@imagedestroy($img);

		$player->setSkin(new Skin($skin->getSkinId(), $skinbytes, "", "geometry.rmsp." . $geo, file_get_contents($this->getDataFolder() . "geometry.json")));
		$player->sendSkin();
		$player->sendMessage(C::GREEN . "You have disguise as " . $file);
	}
}
