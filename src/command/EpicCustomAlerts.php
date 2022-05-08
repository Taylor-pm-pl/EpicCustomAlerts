<?php

namespace davidglitch04\EpicCustomAlerts\command;

use davidglitch04\EpicCustomAlerts\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

class EpicCustomAlerts extends Command implements PluginOwned{

    protected Loader $eca;

    public function __construct(Loader $eca)
    {
        $this->eca = $eca;
        parent::__construct("epiccustomalerts");
        $this->setPermission("epiccustomalerts.command.allow");
        $this->setDescription("Allow to use epiccustomalerts control");
    }

    public function getOwningPlugin(): Plugin
    {
        return $this->eca;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        // TODO:
        // Reload
        // Info
        // Help
    }
}