<?php

namespace EnderChestCommand;

use pocketmine\block\inventory\EnderChestInventory;
use pocketmine\block\tile\EnderChest;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class EnderChestCommandMain extends PluginBase
{
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $commandName = strtolower($command->getName());
        if ($commandName !== 'ec') return true;
        if (!($sender instanceof Player)) return true;
        $location = $sender->getLocation();
        $world = $sender->getWorld();
        [$x, $y, $z] = [$location->getFloorX(), $location->getFloorY(), $location->getFloorZ()];
        $vector = new Vector3($x, $y, $z);
        $this->sendEnderChestPacket($sender, $vector);
        $inPositionTile = $world->getTileAt($x, $y, $z);
        if ($inPositionTile) $world->removeTile($inPositionTile);
        $ecTile = new EnderChest($world, $vector);
        $world->addTile($ecTile);
        $ecTile->setViewerCount($ecTile->getViewerCount() + 1);
        $sender->setCurrentWindow(new EnderChestInventory($ecTile->getPosition(), $sender->getEnderInventory()));
        return true;
    }

    private function sendEnderChestPacket(Player $player, Vector3 $vector): void
    {
        $blockPosition = BlockPosition::fromVector3($vector);
        $packet = UpdateBlockPacket::create(
            $blockPosition,
            RuntimeBlockMapping::getInstance()->toRuntimeId(VanillaBlocks::ENDER_CHEST()->getFullId()),
            UpdateBlockPacket::FLAG_NETWORK,
            UpdateBlockPacket::DATA_LAYER_NORMAL
        );
        $player->getNetworkSession()->sendDataPacket($packet);
    }
}