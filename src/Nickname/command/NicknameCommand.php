<?php

namespace Nickname\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use Nickname\Nick;

class NicknameCommand extends Command implements PluginIdentifiableCommand {

	private $plugin;

	public function __construct(Nick $plugin) {
		$this->plugin = $plugin;
		parent::__construct(
			"nick",
			"Nickname main command",
			"/nick <sub-command>",
			["nickname"]
		);
		$this->setPermission("nick.help");
	}

	public function getPlugin() : Plugin {
		return $this->plugin;
	}

	public function execute(CommandSender $sender, string $label, array $args) {
		if (!$this->testPermission($sender)) {
			return;
		}

		if ($sender instanceof Player) {
			if (isset($args[0])) {
				$config = $this->plugin->getConfig();
				$pureChat = $this->plugin->getServer()->getPluginManager()->getPlugin("PureChat");
				switch (strtolower($args[0])) {
					case $config->get("help-command"):
						$sender->sendMessage($config->get("help-header"));
						$commandColor = $config->get("help-command-color");
						$usageColor = $config->get("help-usage-color");
						$twoPointsColor = $config->get("help-two-points-color");
						$descriptionColor = $config->get("help-description-color");
						if ($sender->hasPermission("nick.set")) {
							$sender->sendMessage($commandColor . "/nick " . $config->get("set-command") . " " . $usageColor . $config->get("set-usage") . $twoPointsColor . ": " . $descriptionColor . $config->get("set-description"));
						}
						if ($sender->hasPermission("nick.admin.set")) {
							$sender->sendMessage($commandColor . "/nick " . $config->get("set-command") . " " . $usageColor . $config->get("set-admin-usage") . $twoPointsColor . ": " . $descriptionColor . $config->get("set-admin-description"));
						}
						if ($sender->hasPermission("nick.reset")) {
							$sender->sendMessage($commandColor . "/nick " . $config->get("reset-command") . $twoPointsColor . ": " . $descriptionColor . $config->get("reset-description"));
						}
						if ($sender->hasPermission("nick.admin.reset")) {
							$sender->sendMessage($commandColor . "/nick" . $config->get("reset-command") . " " . $usageColor . $config->get("reset-admin-usage") . $twoPointsColor . ": " . $descriptionColor . $config->get("reset-admin-description"));
						}
						if ($sender->hasPermission("nick.see")) {
							$sender->sendMessage($commandColor . "/nick" . $config->get("see-command") . " " . $usageColor . $config->get("see-usage") . $twoPointsColor . ": " . $descriptionColor . $config->get("see-description"));
						}
						break;
					case $config->get("set-command"):
						if ($sender->hasPermission("nick.set")) {
							if (isset($args[1])) {
								if (isset($args[2])) {
									if ($sender->hasPermission("nick.admin.set")) {
										$player = null;
										if ($this->plugin->getServer()->getPlayer($args[2]) !== null) {
											$player = $this->plugin->getServer()->getPlayer($args[2]);
										} else {
											foreach ($this->plugin->getServer()->getOnlinePlayers() as $onlinePlayer) {
												if ($onlinePlayer->getDisplayName() == $args[2]) {
													$player = $onlinePlayer;
												}
											}
										}
										if ($player !== null) {
											$player->setDisplayName($args[1]);
											$player->setNameTag($pureChat->getNametag($player));
											$player->sendMessage($config->get("set-by-admin"));
											$sender->sendMessage($config->get("set"));
											if ($config->get("keep-nick")) {
												$this->plugin->nicks->set(strtolower($player->getName(), $args[1]));
												$this->plugin->nicks->save();
											}
										} else {
											$sender->sendMessage($config->get("player-not-online"));
										}
									} else {
										$sender->sendMessage($config->get("no-permission-admin-set"));
									}
								} else {
									$sender->setDisplayName($args[1]);
									$sender->setNameTag($pureChat->getNametag($sender));
									$sender->sendMessage($config->get("set"));
									if ($config->get("keep-nick")) {
										$this->plugin->nicks->set(strtolower($sender->getName(), $args[1]));
										$this->plugin->nicks->save();
									}
								}
							} else {
								$sender->sendMessage($config->get("must-specify-nick"));
							}
						} else {
							$sender->sendMessage($config->get("no-permission-set"));
						}
						break;
					case $config->get("reset-command"):
						if ($sender->hasPermission("nick.reset")) {
							if (isset($args[1])) {
								if ($sender->hasPermission("nick.admin.reset")) {
									$player = null;
									if ($this->plugin->getServer()->getPlayer($args[1]) !== null) {
										$player = $this->plugin->getServer()->getPlayer($args[1]);
									} else {
										foreach ($this->plugin->getServer->getOnlinePlayers() as $onlinePlayer) {
											if ($onlinePlayer->getDisplayName() == $args[1]) {
												$player = $onlinePlayer;
											}
										}
									}
									if ($player !== null) {
										$player->setDisplayName($player->getName());
										$player->setNameTag($pureChat->getNametag($sender));
										$player->sendMessage($config->get("reset-by-admin"));
										$sender->sendMessage($config->get("reset-admin"));
										if ($this->plugin->nicks->get(strtolower($player->getName()))) {
											$this->plugin->nicks->remove(strtolower($player->getName()));
											$this->plugin->nicks->save();
										}
									} else {
										$sender->sendMessage($config->get("player-not-online"));
									}
								} else {
									$sender->sendMessage($config->get("no-permission-admin-reset"));
								}
							} else {
								$sender->setDisplayName($sender->getName());
								$sender->setNameTag($pureChat->getNametag($sender));
								$sender->sendMessage($config->get("reset"));
								if ($this->plugin->nicks->exists(strtolower($sender->getName()))) {
									$this->plugin->nicks->remove(strtolower($sender->getName()));
									$this->plugin->nicks->save();
								}
							}
						} else {
							$sender->sendMessage($config->get("no-permission-reset"));
						}
						break;
					case $config->get("see-command"):
						if ($sender->hasPermission("nick.see")) {
							if (isset($args[1])) {
								$player = null;
								if ($this->plugin->getServer()->getPlayer($args[1]) !== null) {
									$player = $this->plugin->getServer()->getPlayer($args[1]);
								} else {
									foreach ($this->plugin->getServer()->getOnlinePlayers() as $onlinePlayer) {
										if ($onlinePlayer->getDisplayName() == $args[1]) {
											$player = $onlinePlayer;
										}
									}
								}
								if ($player !== null) {
									$sender->sendMessage(str_replace(["{player_nick}","{player_name}"], [$player->getDisplayName(), $player->getName()], $config->get("see")));
								} else {
									$sender->sendMessage($config->get("player-not-online"));
								}
							} else {
								$sender->sendMessage($config->get("must-specify-player"));
							}
						}
						break;
				}
			} else {
				$sender->sendMessage($this->plugin->getConfig()->get("no-subcommand"));
			}
		} else {
			$sender->sendMessage($this->plugin->getConfig()->get("no-run-in-console"));
		}
	}

}
