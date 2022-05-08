<?php

namespace davidglitch04\EpicCustomAlerts\command;

use davidglitch04\EpicCustomAlerts\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;

class EpicCustomAlerts extends Command implements PluginOwned{

    protected Loader $eca;

    public function __construct(Loader $eca)
    {
        $this->eca = $eca;
        parent::__construct("epiccustomalerts");
        $this->setPermission("epiccustomalerts.command.allow");
        $this->setDescription("Allow to use epiccustomalerts control");
        $this->setAliases(["eca"]);
    }

    public function getOwningPlugin(): Plugin
    {
        return $this->eca;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void
    {
        if (!isset($args[0])){
            help: 
                $sender->sendMessage(TextFormat::colorize("&b-- &aAvailable Commands &b--"));
		        $sender->sendMessage(TextFormat::colorize("&d/eca help &b-&a Show help about this plugin"));
		        $sender->sendMessage(TextFormat::colorize("&d/eca reload &b-&a Reload the config"));
        } else{
            switch (strtolower($args[0])){
                case "reload":
			        $this->eca->reloadConfig();
			        $this->eca->config = (array)$this->eca->getConfig()->getAll();
			        $sender->sendMessage(TextFormat::colorize("&aConfiguration Reloaded."));
			        break;
                case "help":
                    goto help;
                    break;
            }
        }
    }
}