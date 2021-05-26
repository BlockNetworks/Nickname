<?php

namespace Nickname;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\Listener;
use pocketmine\permission\Permission;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Player;
use Nickname\command\NicknameCommand;

class Nick extends PluginBase implements Listener {

	public function onEnable() {
		$this->registerPermissions();
		$this->registerCommands();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		if (!is_dir($this->getDataFolder())) {
			@mkdir($this->getDataFolder());
		}

		if (!file_exists($this->getDataFolder() . "nicks.yml")) {
			yaml_emit_file($this->getDataFolder() . "nicks.yml", []);
		}

		if (!file_exists($this->getDataFolder()."config.yml")) {
			$this->saveDefaultConfig();
		}

		$this->nicks = new Config($this->getDataFolder() . "nicks.yml", Config::YAML);
	}

	public function registerPermissions() {
		$this->getServer()->getPluginManager()->addPermission(new Permission("nick.help", "Permission for nickname help command", Permission::DEFAULT_TRUE));
		$this->getServer()->getPluginManager()->addPermission(new Permission("nick.set", "Permission for nickname set command", Permission::DEFAULT_TRUE));
		$this->getServer()->getPluginManager()->addPermission(new Permission("nick.reset", "Permission for nickname reset command", Permission::DEFAULT_TRUE));
		$this->getServer()->getPluginManager()->addPermission(new Permission("nick.see", "Permission for nickname see command", Permission::DEFAULT_OP));
		$this->getServer()->getPluginManager()->addPermission(new Permission("nick.admin.set", "Permission for nickname admin set command", Permission::DEFAULT_OP));
		$this->getServer()->getPluginManager()->addPermission(new Permission("nick.admin.reset", "Permission for nickname admin reset command", Permission::DEFAULT_OP));
	}

	public function registerCommands() {
		$this->getServer()->getCommandMap()->register("nick", new NicknameCommand($this));
	}

	public function onPreLogin(PlayerPreLoginEvent $event) {
		if ($this->getConfig()->get("keep-nick")) {
			if ($this->nicks->exists(strtolower($event->getPlayer()->getName()))) {
				$event->getPlayer()->setDisplayName($this->nicks->get(strtolower($event->getPlayer()->getName())));
			}
		}
	}

}
