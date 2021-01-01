<?php
namespace MerLant;

use MerLant\lib\MinecraftQuery;
use MerLant\lib\MinecraftQueryException;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\server\QueryRegenerateEvent;

class OneInMore extends PluginBase implements Listener {

    private $Query;
    private $server = array(), $timeout = 3;

    public function onEnable() {
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();

        $this->timeout = $this->getConfig()->get("timeout");

        $servers = $this->getConfig()->get("servers");
        foreach($servers as $server){
            $this->server[]=$server;
        }

        $this->Query = new MinecraftQuery();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

    }

    public function queryRegen(QueryRegenerateEvent $ev)
    {
        $totalPlayers = 0;
        $maxPlayers = 0;
        foreach ($this->server as $server) {
            $server = explode(":", $server);
            try {
                $this->Query->Connect($server[0], $server[1], $this->timeout);
                $array = ($this->Query->GetInfo());

                $totalPlayers = $totalPlayers + $array['Players'];
                $maxPlayers = $maxPlayers + $array['MaxPlayers'];
            } catch (MinecraftQueryException $e) {
                $this->getLogger()->critical($e->getMessage());
            }
        }
        $localPlayersCount = count($this->getServer()->getOnlinePlayers());
        $localMaxPlayerCount = $this->getServer()->getMaxPlayers();

        $ev->setPlayerCount($localPlayersCount + $totalPlayers);
        $ev->setMaxPlayerCount($localMaxPlayerCount + $maxPlayers);

    }

}