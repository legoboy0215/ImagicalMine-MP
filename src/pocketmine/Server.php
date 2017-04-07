<?php
/**
 * src/pocketmine/Server.php
 *
 * @package default
 */


/*
 *
 *  _                       _           _ __  __ _
 * (_)                     (_)         | |  \/  (_)
 *  _ _ __ ___   __ _  __ _ _  ___ __ _| | \  / |_ _ __   ___
 * | | '_ ` _ \ / _` |/ _` | |/ __/ _` | | |\/| | | '_ \ / _ \
 * | | | | | | | (_| | (_| | | (_| (_| | | |  | | | | | |  __/
 * |_|_| |_| |_|\__,_|\__, |_|\___\__,_|_|_|  |_|_|_| |_|\___|
 *                     __/ |
 *                    |___/
 *
 * This program is a third party build by ImagicalMine.
 *
 * ImagicalMine is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ImagicalMine Team
 * @link http://forums.imagicalmine.net/
 *
 *
*/

/**
 * ImagicalMine is the Minecraft: PE multiplayer server software
 * Homepage: http://imagicalmine.net/
 */
namespace pocketmine;

use pocketmine\block\Block;
use pocketmine\command\CommandReader;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\SimpleCommandMap;
use pocketmine\entity\Arrow;
use pocketmine\entity\Bat;
use pocketmine\entity\Blaze;
use pocketmine\entity\Boat;
use pocketmine\entity\CavernSpider;
use pocketmine\entity\ChargedCreeper;
use pocketmine\entity\Chicken;
use pocketmine\entity\Cow;
use pocketmine\entity\Creeper;
use pocketmine\entity\Effect;
use pocketmine\entity\Egg;
use pocketmine\entity\Enderman;
use pocketmine\entity\Entity;
use pocketmine\entity\ExperienceOrb;
use pocketmine\entity\FallingSand;
use pocketmine\entity\Ghast;
use pocketmine\entity\Human;
use pocketmine\entity\IronGolem;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\entity\MagmaCube;
use pocketmine\entity\Minecart;
use pocketmine\entity\Mooshroom;
use pocketmine\entity\Ozelot;
use pocketmine\entity\Painting;
use pocketmine\entity\Pig;
use pocketmine\entity\PigZombie;
use pocketmine\entity\PrimedTNT;
use pocketmine\entity\Rabbit;
use pocketmine\entity\Sheep;
use pocketmine\entity\Silverfish;
use pocketmine\entity\Skeleton;
use pocketmine\entity\Slime;
use pocketmine\entity\Snowball;
use pocketmine\entity\SnowGolem;
use pocketmine\entity\Spider;
use pocketmine\entity\Squid;
use pocketmine\entity\ThrownExpBottle;
use pocketmine\entity\ThrownPotion;
use pocketmine\entity\Villager;
use pocketmine\entity\WitherSkeleton;
use pocketmine\entity\Wolf;
use pocketmine\entity\Zombie;
use pocketmine\entity\ZombieVillager;
use pocketmine\event\HandlerList;
use pocketmine\event\level\LevelInitEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\Timings;
use pocketmine\event\TimingsHandler;
use pocketmine\event\TranslationContainer;
use pocketmine\katana\Katana;
use pocketmine\inventory\CraftingManager;
use pocketmine\inventory\InventoryType;
use pocketmine\inventory\Recipe;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\lang\BaseLang;
use pocketmine\level\format\io\region\Anvil;
use pocketmine\level\format\io\region\McRegion;
use pocketmine\level\format\io\region\PMAnvil;
use pocketmine\level\format\io\LevelProviderManager;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\Flat;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\nether\Nether;
use pocketmine\level\generator\normal\Normal;
use pocketmine\level\generator\normal\Normal2;
use pocketmine\level\Level;
use pocketmine\level\LevelException;
use pocketmine\metadata\EntityMetadataStore;
use pocketmine\metadata\LevelMetadataStore;
use pocketmine\metadata\PlayerMetadataStore;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\CompressBatchedTask;
use pocketmine\network\Network;
use pocketmine\network\protocol\BatchPacket;
use pocketmine\network\protocol\CraftingDataPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\PlayerListPacket;
use pocketmine\network\query\QueryHandler;
use pocketmine\network\RakLibInterface;
use pocketmine\network\rcon\RCON;
use pocketmine\network\upnp\UPnP;
use pocketmine\permission\BanList;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\PharPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginLoadOrder;
use pocketmine\plugin\PluginManager;
use pocketmine\plugin\ScriptPluginLoader;
use pocketmine\scheduler\FileWriteTask;
use pocketmine\scheduler\SendUsageTask;
use pocketmine\scheduler\ServerScheduler;
use pocketmine\tile\BrewingStand;
use pocketmine\tile\Chest;
use pocketmine\tile\Dispenser;
use pocketmine\tile\Dropper;
use pocketmine\tile\EnchantTable;
use pocketmine\tile\FlowerPot;
use pocketmine\tile\Furnace;
use pocketmine\tile\Hopper;
use pocketmine\tile\Skull;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\tile\TrappedChest;
use pocketmine\utils\Binary;
use pocketmine\utils\Config;
//use pocketmine\utils\LevelException;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\utils\UUID;
use pocketmine\utils\VersionString;
use pocketmine\entity\FishingHook;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ListTag;

/**
 * The class that manages everything
 */
class Server {
	const BROADCAST_CHANNEL_ADMINISTRATIVE = "pocketmine.broadcast.admin";
	const BROADCAST_CHANNEL_USERS = "pocketmine.broadcast.user";

	const PLAYER_MSG_TYPE_MESSAGE = 0;
	const PLAYER_MSG_TYPE_TIP = 1;
	const PLAYER_MSG_TYPE_POPUP = 2;

	/** @var Server */
	private static $instance = null;

	/** @var \Threaded */
	private static $sleeper = null;

	/** @var BanList */
	private $banByName = null;

	/** @var BanList */
	private $banByIP = null;

	/** @var BanList */
	private $banByClient = null;

	/** @var Config */
	private $operators = null;

	/** @var Config */
	private $whitelist = null;

	/** @var bool */
	private $isRunning = true;

	private $hasStopped = false;

	/** @var PluginManager */
	private $pluginManager = null;

	private $profilingTickRate = 20;

	/** @var AutoUpdater */
	private $updater = null;

	/** @var ServerScheduler */
	private $scheduler = null;

	/**
	 * Counts the ticks since the server start
	 *
	 * @var int
	 */
	private $tickCounter = 0;
	private $nextTick = 0;
	private $tickAverage = [20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20];
	private $useAverage = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
	private $maxTick = 20;
	private $maxUse = 0;

	private $sendUsageTicker = 0;

	private $dispatchSignals = false;

	/** @var \AttachableThreadedLogger */
	private $logger;

	/** @var MemoryManager */
	private $memoryManager;

	/** @var CommandReader */
	private $console = null;
	private $consoleThreaded;

	/** @var SimpleCommandMap */
	private $commandMap = null;

	/** @var CraftingManager */
	private $craftingManager;

	/** @var ConsoleCommandSender */
	private $consoleSender;

	/** @var int */
	private $maxPlayers;

	/** @var bool */
	private $autoSave;

	/** @var RCON */
	private $rcon;

	/** @var EntityMetadataStore */
	private $entityMetadata;

	/** @var PlayerMetadataStore */
	private $playerMetadata;

	/** @var LevelMetadataStore */
	private $levelMetadata;

	/** @var Network */
	private $network;

	/** @var Katana */
	private $katana;

	private $networkCompressionAsync = true;
	public $networkCompressionLevel = 7;

	private $autoTickRate = true;
	private $autoTickRateLimit = 20;
	private $alwaysTickPlayers = false;
	private $baseTickRate = 1;

	private $autoSaveTicker = 0;
	private $autoSaveTicks = 6000;

	/** @var BaseLang */
	private $baseLang;

	private $forceLanguage = false;

	private $serverID;

	private $autoloader;
	private $filePath;
	private $dataPath;
	private $pluginPath;

	private $uniquePlayers = [];

	/** @var QueryHandler */
	private $queryHandler;

	/** @var QueryRegenerateEvent */
	private $queryRegenerateTask = null;

	/** @var Config */
	private $properties;

	private $propertyCache = [];

	/** @var Config */
	private $config;

	/** @var Player[] */
	private $players = [];

	/** @var Player[] */
	private $playerList = [];

	private $identifiers = [];

	/** @var Level[] */
	private $levels = [];

	/** @var Level */
	private $levelDefault = null;

	/** Advanced Config */
	public $advancedConfig = null;
	public $weatherEnabled = true;
	public $foodEnabled = true;
	public $expEnabled = true;
	public $keepInventory = false;
	public $netherEnabled = false;
	public $netherName = "nether";
	public $netherLevel = null;
	public $weatherChangeTime = 12000;
	public $lookup = [];
	public $hungerHealth = 10;
	public $lightningTime = 100;
	public $expCache = [];
	public $expWriteAhead = 200;
	public $aiConfig = [];
	public $aiEnabled = false;
	public $aiHolder = null;
	public $inventoryNum = 36;
	public $hungerTimer = 80;
	public $weatherLastTime = 1200;
	public $version;
	public $autoClearInv = true;
	public $dserverConfig = [];
	public $dserverPlayers = 0;
	public $dserverAllPlayers = 0;
	public $redstoneEnabled = false;
	public $allowFakeLowFrequencyPulse = false;
	public $anviletEnabled = false;
	public $pulseFrequency = 20;
	public $playerMsgType = self::PLAYER_MSG_TYPE_MESSAGE;
	public $playerLoginMsg = "";
	public $playerLogoutMsg = "";
	public $chunkRadius = 5;
	public $snowGolemEnabled = false;
	public $ironGolemEnabled = false;
	public $incompatibleplugins = false;

	/**
	 *
	 * @return mc3coreLib
	 */
	public function getKatana() {
		return $this->katana;
	}


	/**
	 *
	 * @return string
	 */
	public function getName(): string{
		return "ImagicalMine";
	}


	/**
	 *
	 * @return bool
	 */
	public function isRunning(): bool{
		return $this->isRunning === true;
	}


	/**
	 *
	 * @return string
	 */
	public function getPocketMineVersion(): string{
		return \pocketmine\VERSION;
	}


	/**
	 *
	 * @return string
	 */
	public function getCodename(): string{
		return \pocketmine\CODENAME;
	}


	/**
	 *
	 * @return string
	 */
	public function getVersion(): string{
		return \pocketmine\MINECRAFT_VERSION;
	}


	/**
	 *
	 * @return string
	 */
	public function getApiVersion(): string{
		return \pocketmine\API_VERSION;
	}


	/**
	 *
	 * @return string
	 */
	public function getFilePath(): string{
		return $this->filePath;
	}


	/**
	 *
	 * @return string
	 */
	public function getDataPath(): string{
		return $this->dataPath;
	}


	/**
	 *
	 * @return string
	 */
	public function getPluginPath(): string{
		return $this->pluginPath;
	}


	/**
	 *
	 * @return int
	 */
	public function getMaxPlayers(): int{
		return $this->maxPlayers;
	}


	/**
	 *
	 * @return int
	 */
	public function getPort(): int{
		return $this->getConfigInt("server-port", 19132);
	}


	/**
	 *
	 * @return int
	 */
	public function getViewDistance(): int{
		return max(56, $this->getProperty("chunk-sending.max-chunks", 256));
	}


	/**
	 *
	 * @return string
	 */
	public function getIp(): string{
		return $this->getConfigString("server-ip", "0.0.0.0");
	}


	/**
	 *
	 * @return unknown
	 */
	public function getServerUniqueId(): string{
		return $this->serverID;
	}


	/**
	 *
	 * @return bool
	 */
	public function getAutoSave(): bool{
		return $this->autoSave;
	}


	/**
	 *
	 * @param bool    $value
	 */
	public function setAutoSave($value) {
		$this->autoSave = (bool) $value;
		foreach ($this->getLevels() as $level) {
			$level->setAutoSave($this->autoSave);
		}
	}


	/**
	 *
	 * @return string
	 */
	public function getLevelType(): string{
		return $this->getConfigString("level-type", "DEFAULT");
	}


	/**
	 *
	 * @return bool
	 */
	public function getGenerateStructures(): bool{
		return $this->getConfigBoolean("generate-structures", true);
	}


	/**
	 *
	 * @return int
	 */
	public function getGamemode(): int{
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}


	/**
	 *
	 * @return bool
	 */
	public function getForceGamemode(): bool{
		return $this->getConfigBoolean("force-gamemode", false);
	}


	/**
	 * Returns the gamemode text name
	 *
	 *
	 * @param int     $mode
	 * @return string
	 */
	public static function getGamemodeString($mode): string{
		switch ((int) $mode) {
		case Player::SURVIVAL:
			return "%gameMode.survival";
		case Player::CREATIVE:
			return "%gameMode.creative";
		case Player::ADVENTURE:
			return "%gameMode.adventure";
		case Player::SPECTATOR:
			return "%gameMode.spectator";
		}

		return "UNKNOWN";
	}


	/**
	 * Parses a string and returns a gamemode integer, -1 if not found
	 *
	 *
	 * @param string  $str
	 * @return int
	 */
	public static function getGamemodeFromString($str): int{
		switch (strtolower(trim($str))) {
		case (string) Player::SURVIVAL:
		case "survival":
		case "s":
			return Player::SURVIVAL;

		case (string) Player::CREATIVE:
		case "creative":
		case "c":
			return Player::CREATIVE;

		case (string) Player::ADVENTURE:
		case "adventure":
		case "a":
			return Player::ADVENTURE;

		case (string) Player::SPECTATOR:
		case "spectator":
		case "view":
		case "v":
			return Player::SPECTATOR;
		}
		return -1;
	}


	/**
	 *
	 * @param string  $str
	 * @return int
	 */
	public static function getDifficultyFromString($str): int{
		switch (strtolower(trim($str))) {
		case "0":
		case "peaceful":
		case "p":
			return 0;

		case "1":
		case "easy":
		case "e":
			return 1;

		case "2":
		case "normal":
		case "n":
			return 2;

		case "3":
		case "hard":
		case "h":
			return 3;
		}
		return -1;
	}


	/**
	 *
	 * @return int
	 */
	public function getDifficulty(): int{
		return $this->getConfigInt("difficulty", 1);
	}


	/**
	 *
	 * @return bool
	 */
	public function hasWhitelist(): bool{
		return $this->getConfigBoolean("white-list", false);
	}


	/**
	 *
	 * @return int
	 */
	public function getSpawnRadius(): int{
		return $this->getConfigInt("spawn-protection", 16);
	}


	/**
	 *
	 * @return bool
	 */
	public function getAllowFlight(): bool{
		return $this->getConfigBoolean("allow-flight", false);
	}


	/**
	 *
	 * @return bool
	 */
	public function isAllowRedstoneCalculation(): bool{
		return $this->getConfigBoolean("redstone-calculation", true);
	}


	/**
	 *
	 * @return bool
	 */
	public function isHardcore(): bool{
		return $this->getConfigBoolean("hardcore", false);
	}


	/**
	 *
	 * @return int
	 */
	public function getDefaultGamemode(): int{
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}


	/**
	 *
	 * @return string
	 */
	public function getMotd(): string{
		return $this->getConfigString("motd", "Minecraft: PE Server");
	}


	/**
	 *
	 * @return \ClassLoader
	 */
	public function getLoader(): \ClassLoader{
		return $this->autoloader;
	}


	/**
	 *
	 * @return \AttachableThreadedLogger
	 */
	public function getLogger(): \AttachableThreadedLogger{
		return $this->logger;
	}


	/**
	 *
	 * @return EntityMetadataStore
	 */
	public function getEntityMetadata(): EntityMetadataStore{
		return $this->entityMetadata;
	}


	/**
	 *
	 * @return PlayerMetadataStore
	 */
	public function getPlayerMetadata(): PlayerMetadataStore{
		return $this->playerMetadata;
	}


	/**
	 *
	 * @return LevelMetadataStore
	 */
	public function getLevelMetadata(): LevelMetadataStore{
		return $this->levelMetadata;
	}


	/**
	 *
	 * @return PluginManager
	 */
	public function getPluginManager(): PluginManager{
		return $this->pluginManager;
	}


	/**
	 *
	 * @return CraftingManager
	 */
	public function getCraftingManager(): CraftingManager{
		return $this->craftingManager;
	}


	/**
	 *
	 * @return ServerScheduler
	 */
	public function getScheduler(): ServerScheduler{
		return $this->scheduler;
	}


	/**
	 *
	 * @return int
	 */
	public function getTick(): int{
		return is_null($this->tickCounter) ? 0 : $this->tickCounter;
	}


	/**
	 * Returns the last server TPS measure
	 *
	 * @return float
	 */
	public function getTicksPerSecond(): float{
		return round($this->maxTick, 2);
	}


	/**
	 * Returns the last server TPS average measure
	 *
	 * @return float
	 */
	public function getTicksPerSecondAverage(): float{
		return round(array_sum($this->tickAverage) / count($this->tickAverage), 2);
	}


	/**
	 * Returns the TPS usage/load in %
	 *
	 * @return float
	 */
	public function getTickUsage(): float{
		return round($this->maxUse * 100, 2);
	}


	/**
	 * Returns the TPS usage/load average in %
	 *
	 * @return float
	 */
	public function getTickUsageAverage(): float{
		return round((array_sum($this->useAverage) / count($this->useAverage)) * 100, 2);
	}


	/**
	 *
	 * @return SimpleCommandMap
	 */
	public function getCommandMap(): SimpleCommandMap{
		return $this->commandMap;
	}


	/**
	 *
	 * @return Player[]
	 */
	public function getOnlinePlayers(): array{
		return $this->playerList;
	}


	/**
	 *
	 * @param Recipe  $recipe
	 */
	public function addRecipe(Recipe $recipe) {
		$this->craftingManager->registerRecipe($recipe);
	}


	/**
	 *
	 * @param string  $name
	 * @return OfflinePlayer|Player
	 */
	public function getOfflinePlayer($name) {
		$name = strtolower($name);
		$result = $this->getPlayerExact($name);

		if ($result === null) {
			$result = new OfflinePlayer($this, $name);
		}

		return $result;
	}


	/**
	 *
	 * @param string  $name
	 * @return Compound
	 */
	public function getOfflinePlayerData($name): CompoundTag{
		$name = strtolower($name);
		$path = $this->getDataPath() . "players/";
		if (file_exists($path . "$name.dat")) {
			try{
				$nbt = new NBT(NBT::BIG_ENDIAN);
				$nbt->readCompressed(file_get_contents($path . "$name.dat"));

				return $nbt->getData();
			}catch(\Throwable $e) { //zlib decode error / corrupt data
				rename($path . "$name.dat", $path . "$name.dat.bak");
				$this->logger->notice($this->getLanguage()->translateString("pocketmine.data.playerCorrupted", [$name]));
			}
		}else {
			$this->logger->notice($this->getLanguage()->translateString("pocketmine.data.playerNotFound", [$name]));
		}
		$spawn = $this->getDefaultLevel()->getSafeSpawn();
		$nbt = new CompoundTag("", [
				new LongTag("firstPlayed", floor(microtime(true) * 1000)),
				new LongTag("lastPlayed", floor(microtime(true) * 1000)),
				new ListTag("Pos", [
						new DoubleTag(0, $spawn->x),
						new DoubleTag(1, $spawn->y),
						new DoubleTag(2, $spawn->z)
					]),
				new StringTag("Level", $this->getDefaultLevel()->getName()),
				//new StringTag("SpawnLevel", $this->getDefaultLevel()->getName()),
				//new IntTag("SpawnX", (int) $spawn->x),
				//new IntTag("SpawnY", (int) $spawn->y),
				//new IntTag("SpawnZ", (int) $spawn->z),
				//new ByteTag("SpawnForced", 1), //TODO
				new ListTag("Inventory", []),
				new CompoundTag("Achievements", []),
				new IntTag("playerGameType", $this->getGamemode()),
				new IntTag("food", 20),
				new ListTag("Motion", [
						new DoubleTag(0, 0.0),
						new DoubleTag(1, 0.0),
						new DoubleTag(2, 0.0)
					]),
				new ListTag("Rotation", [
						new FloatTag(0, 0.0),
						new FloatTag(1, 0.0)
					]),
				new FloatTag("FallDistance", 0.0),
				new ShortTag("Fire", 0),
				new ShortTag("Air", 300),
				new ByteTag("OnGround", 1),
				new ByteTag("Invulnerable", 0),
				new StringTag("NameTag", $name),
			]);
		$nbt->Pos->setTagType(NBT::TAG_Double);
		$nbt->Inventory->setTagType(NBT::TAG_Compound);
		$nbt->Motion->setTagType(NBT::TAG_Double);
		$nbt->Rotation->setTagType(NBT::TAG_Float);

		if (file_exists($path . "$name.yml")) { //Importing old ImagicalMine files
			$data = new Config($path . "$name.yml", Config::YAML, []);
			$nbt["playerGameType"] = (int) $data->get("gamemode");
			$nbt["Level"] = $data->get("position")["level"];
			$nbt["Pos"][0] = $data->get("position")["x"];
			$nbt["Pos"][1] = $data->get("position")["y"];
			$nbt["Pos"][2] = $data->get("position")["z"];
			$nbt["SpawnLevel"] = $data->get("spawn")["level"];
			$nbt["SpawnX"] = (int) $data->get("spawn")["x"];
			$nbt["SpawnY"] = (int) $data->get("spawn")["y"];
			$nbt["SpawnZ"] = (int) $data->get("spawn")["z"];
			$this->logger->notice($this->getLanguage()->translateString("pocketmine.data.playerOld", [$name]));
			foreach ($data->get("inventory") as $slot => $item) {
				if (count($item) === 3) {
					$nbt->Inventory[$slot + 9] = new CompoundTag("", [
							new ShortTag("id", $item[0]),
							new ShortTag("Damage", $item[1]),
							new ByteTag("Count", $item[2]),
							new ByteTag("Slot", $slot + 9),
							new ByteTag("TrueSlot", $slot + 9)
						]);
				}
			}
			foreach ($data->get("hotbar") as $slot => $itemSlot) {
				if (isset($nbt->Inventory[$itemSlot + 9])) {
					$item = $nbt->Inventory[$itemSlot + 9];
					$nbt->Inventory[$slot] = new CompoundTag("", [
							new ShortTag("id", $item["id"]),
							new ShortTag("Damage", $item["Damage"]),
							new ByteTag("Count", $item["Count"]),
							new ByteTag("Slot", $slot),
							new ByteTag("TrueSlot", $item["TrueSlot"])
						]);
				}
			}
			foreach ($data->get("armor") as $slot => $item) {
				if (count($item) === 2) {
					$nbt->Inventory[$slot + 100] = new CompoundTag("", [
							new ShortTag("id", $item[0]),
							new ShortTag("Damage", $item[1]),
							new ByteTag("Count", 1),
							new ByteTag("Slot", $slot + 100)
						]);
				}
			}
			foreach ($data->get("achievements") as $achievement => $status) {
				$nbt->Achievements[$achievement] = new ByteTag($achievement, $status == true ? 1 : 0);
			}
			unlink($path . "$name.yml");
		}
		$this->saveOfflinePlayerData($name, $nbt);

		return $nbt;

	}


	/**
	 *
	 * @param string      $name
	 * @param CompoundTag $nbtTag
	 * @param bool        $async  (optional)
	 */
	public function saveOfflinePlayerData($name, CompoundTag $nbtTag, $async = false) {
		$nbt = new NBT(NBT::BIG_ENDIAN);
		try{
			$nbt->setData($nbtTag);

			if ($async) {
				$this->getScheduler()->scheduleAsyncTask(new FileWriteTask($this->getDataPath() . "players/" . strtolower($name) . ".dat", $nbt->writeCompressed()));
			}else {
				file_put_contents($this->getDataPath() . "players/" . strtolower($name) . ".dat", $nbt->writeCompressed());
			}
		}catch(\Throwable $e) {
			$this->logger->critical($this->getLanguage()->translateString("pocketmine.data.saveError", [$name, $e->getMessage()]));
			$this->logger->logException($e);
		}
	}


	/**
	 *
	 * @param string  $name
	 * @return Player
	 */
	public function getPlayer($name) {
		$found = null;
		$name = strtolower($name);
		$delta = PHP_INT_MAX;
		foreach ($this->getOnlinePlayers() as $player) {
			if (stripos($player->getName(), $name) === 0) {
				$curDelta = strlen($player->getName()) - strlen($name);
				if ($curDelta < $delta) {
					$found = $player;
					$delta = $curDelta;
				}
				if ($curDelta === 0) {
					break;
				}
			}
		}

		return $found;
	}


	/**
	 *
	 * @param string  $name
	 * @return Player
	 */
	public function getPlayerExact($name) {
		$name = strtolower($name);
		foreach ($this->getOnlinePlayers() as $player) {
			if (strtolower($player->getName()) === $name) {
				return $player;
			}
		}

		return null;
	}


	/**
	 *
	 * @param string  $partialName
	 * @return Player[]
	 */
	public function matchPlayer($partialName): array{
		$partialName = strtolower($partialName);
		$matchedPlayers = [];
		foreach ($this->getOnlinePlayers() as $player) {
			if (strtolower($player->getName()) === $partialName) {
				$matchedPlayers = [$player];
				break;
			}elseif (stripos($player->getName(), $partialName) !== false) {
				$matchedPlayers[] = $player;
			}
		}

		return $matchedPlayers;
	}


	/**
	 *
	 * @param Player  $player
	 */
	public function removePlayer(Player $player) {
		if (isset($this->identifiers[$hash = spl_object_hash($player)])) {
			$identifier = $this->identifiers[$hash];
			unset($this->players[$identifier]);
			unset($this->identifiers[$hash]);
			return;
		}

		foreach ($this->players as $identifier => $p) {
			if ($player === $p) {
				unset($this->players[$identifier]);
				unset($this->identifiers[spl_object_hash($player)]);
				break;
			}
		}
	}


	/**
	 *
	 * @return Level[]
	 */
	public function getLevels(): array{
		return $this->levels;
	}


	/**
	 *
	 * @return Level
	 */
	public function getDefaultLevel() {
		return $this->levelDefault;
	}


	/**
	 * Sets the default level to a different level
	 * This won't change the level-name property,
	 * it only affects the server on runtime
	 *
	 * @param Level   $level
	 */
	public function setDefaultLevel($level) {
		if ($level === null or ($this->isLevelLoaded($level->getFolderName()) and $level !== $this->levelDefault)) {
			$this->levelDefault = $level;
		}
	}


	/**
	 *
	 * @param string  $name
	 * @return bool
	 */
	public function isLevelLoaded($name): bool{
		return $this->getLevelByName($name) instanceof Level;
	}


	/**
	 *
	 * @param int     $levelId
	 * @return Level
	 */
	public function getLevel($levelId): Level{
		if (isset($this->levels[$levelId])) {
			return $this->levels[$levelId];
		}

		return null;
	}


	/**
	 *
	 * @param unknown $name
	 * @return Level
	 */
	public function getLevelByName($name) {
		foreach ($this->getLevels() as $level) {
			if ($level->getFolderName() === $name) {
				return $level;
			}
		}

		return null;
	}


	/**
	 *
	 * @param Level   $level
	 * @param bool    $forceUnload (optional)
	 * @return bool
	 */
	public function unloadLevel(Level $level, $forceUnload = false): bool{
		if ($level === $this->getDefaultLevel() and !$forceUnload) {
			throw new \InvalidStateException("The default level cannot be unloaded while running, please switch levels.");
		}
		if ($level->unload($forceUnload) === true) {
			unset($this->levels[$level->getId()]);

			return true;
		}

		return false;
	}


	/**
	 * Loads a level from the data directory
	 *
	 *
	 *
	 * @throws LevelException
	 * @param string  $name
	 * @return bool
	 */
	public function loadLevel($name): bool{
		if (trim($name) === "") {
			throw new LevelException("Invalid empty level name");
		}
		if ($this->isLevelLoaded($name)) {
			return true;
		}elseif (!$this->isLevelGenerated($name)) {
			$this->logger->notice($this->getLanguage()->translateString("pocketmine.level.notFound", [$name]));

			return false;
		}

		$path = $this->getDataPath() . "worlds/" . $name . "/";

		$provider = LevelProviderManager::getProvider($path);

		if ($provider === null) {
			$this->logger->error($this->getLanguage()->translateString("pocketmine.level.loadError", [$name, "Unknown provider"]));
			$this->logger->logException($e);
			return false;
		}
		//$entities = new Config($path."entities.yml", Config::YAML);
		//if(file_exists($path . "tileEntities.yml")){
		// @rename($path . "tileEntities.yml", $path . "tiles.yml");
		//}

		try{
			$level = new Level($this, $name, $path, $provider);
		}catch(\Throwable $e) {

			$this->logger->error($this->getLanguage()->translateString("pocketmine.level.loadError", [$name, $e->getMessage()]));
			$this->logger->logException($e);
			return false;
		}

		$this->levels[$level->getId()] = $level;

		$level->initLevel();

		$this->getPluginManager()->callEvent(new LevelLoadEvent($level));

		$level->setTickRate($this->baseTickRate);

		return true;
	}


	/**
	 * Generates a new level if it does not exists
	 *
	 *
	 * @param string  $name
	 * @param int     $seed      (optional)
	 * @param string  $generator (optional) Class name that extends pocketmine\level\generator\Noise
	 * @param array   $options   (optional)
	 * @return bool
	 */
	public function generateLevel($name, $seed = null, $generator = null, $options = []) {
		if (trim($name) === "" or $this->isLevelGenerated($name)) {
			return false;
		}

		$seed = $seed === null ? Binary::readInt(@Utils::getRandomBytes(4, false)) : (int) $seed;

		if (!isset($options["preset"])) {
			$options["preset"] = $this->getConfigString("generator-settings", "");
		}

		if (!($generator !== null and class_exists($generator, true) and is_subclass_of($generator, Generator::class))) {
			$generator = Generator::getGenerator($this->getLevelType());
		}


		if (($provider = LevelProviderManager::getProviderByName($providerName = $this->getProperty("level-settings.default-format", "mcregion"))) === null) {
			$provider = LevelProviderManager::getProviderByName($providerName = "mcregion");
		}

		try{
			$path = $this->getDataPath() . "worlds/" . $name . "/";
			/** @var \pocketmine\level\format\LevelProvider $provider */
			$provider::generate($path, $name, $seed, $generator, $options);

			$level = new Level($this, $name, $path, $provider);
			$this->levels[$level->getId()] = $level;

			$level->initLevel();

			$level->setTickRate($this->baseTickRate);
		}catch(\Throwable $e) {
			$this->logger->error($this->getLanguage()->translateString("pocketmine.level.generateError", [$name, $e->getMessage()]));
			$this->logger->logException($e);
			return false;
		}

		$this->getPluginManager()->callEvent(new LevelInitEvent($level));

		$this->getPluginManager()->callEvent(new LevelLoadEvent($level));

		$this->getLogger()->notice($this->getLanguage()->translateString("pocketmine.level.backgroundGeneration", [$name]));

		$centerX = $level->getSpawnLocation()->getX() >> 4;
		$centerZ = $level->getSpawnLocation()->getZ() >> 4;

		$order = [];

		for ($X = -3;$X <= 3;++$X) {
			for ($Z = -3;$Z <= 3;++$Z) {
				$distance = $X ** 2 + $Z ** 2;
				$chunkX = $X + $centerX;
				$chunkZ = $Z + $centerZ;
				$index = Level::chunkHash($chunkX, $chunkZ);
				$order[$index] = $distance;
			}
		}

		asort($order);

		foreach ($order as $index => $distance) {
			Level::getXZ($index, $chunkX, $chunkZ);
			$level->populateChunk($chunkX, $chunkZ, true);
		}

		return true;
	}


	/**
	 *
	 * @param string  $name
	 * @return bool
	 */
	public function isLevelGenerated($name): bool{
		if (trim($name) === "") {
			return false;
		}
		$path = $this->getDataPath() . "worlds/" . $name . "/";
		if (!($this->getLevelByName($name) instanceof Level)) {

			if (LevelProviderManager::getProvider($path) === null) {
				return false;
			}
			/*if(file_exists($path)){
                $level = new LevelImport($path);
                if($level->import() === false){ //Try importing a world
                    return false;
                }
            }else{
                return false;
            }*/
		}

		return true;
	}


	/**
	 *
	 * @param string  $variable
	 * @param string  $defaultValue (optional)
	 * @return string
	 */
	public function getConfigString($variable, $defaultValue = ""): string{
		$v = getopt("", ["$variable::"]);
		if (isset($v[$variable])) {
			return (string) $v[$variable];
		}

		return $this->properties->exists($variable) ? $this->properties->get($variable) : $defaultValue;
	}


	/**
	 *
	 * @param string  $variable
	 * @param mixed   $defaultValue (optional)
	 * @return mixed
	 */
	public function getProperty($variable, $defaultValue = null) {
		if (!array_key_exists($variable, $this->propertyCache)) {
			$v = getopt("", ["$variable::"]);
			if (isset($v[$variable])) {
				$this->propertyCache[$variable] = $v[$variable];
			}else {
				$this->propertyCache[$variable] = $this->config->getNested($variable);
			}
		}

		return $this->propertyCache[$variable] === null ? $defaultValue : $this->propertyCache[$variable];
	}


	/**
	 *
	 * @param string  $variable
	 * @param string  $value
	 */
	public function setConfigString($variable, $value) {
		$this->properties->set($variable, $value);
	}


	/**
	 *
	 * @param string  $variable
	 * @param int     $defaultValue (optional)
	 * @return int
	 */
	public function getConfigInt($variable, $defaultValue = 0): int{
		$v = getopt("", ["$variable::"]);
		if (isset($v[$variable])) {
			return (int) $v[$variable];
		}

		return $this->properties->exists($variable) ? (int) $this->properties->get($variable) : (int) $defaultValue;
	}


	/**
	 *
	 * @param string  $variable
	 * @param int     $value
	 */
	public function setConfigInt($variable, $value) {
		$this->properties->set($variable, (int) $value);
	}


	/**
	 *
	 * @param string  $variable
	 * @param boolean $defaultValue (optional)
	 * @return boolean
	 */
	public function getConfigBoolean($variable, $defaultValue = false): bool{
		$v = getopt("", ["$variable::"]);
		if (isset($v[$variable])) {
			$value = $v[$variable];
		}else {
			$value = $this->properties->exists($variable) ? $this->properties->get($variable) : $defaultValue;
		}

		if (is_bool($value)) {
			return $value;
		}
		switch (strtolower($value)) {
		case "on":
		case "true":
		case "1":
		case "yes":
			return true;
		}

		return false;
	}


	/**
	 *
	 * @param string  $variable
	 * @param bool    $value
	 */
	public function setConfigBool($variable, $value) {
		$this->properties->set($variable, $value == true ? "1" : "0");
	}


	/**
	 *
	 * @param string  $name
	 * @return PluginIdentifiableCommand|null
	 */
	public function getPluginCommand($name) {
		if (($command = $this->commandMap->getCommand($name)) instanceof PluginIdentifiableCommand) {
			return $command;
		}else {
			return null;
		}
	}


	/**
	 *
	 * @return BanList
	 */
	public function getNameBans(): BanList{
		return $this->banByName;
	}


	/**
	 *
	 * @return BanList
	 */
	public function getIPBans(): BanList{
		return $this->banByIP;
	}



	/**
	 *
	 * @return unknown
	 */
	public function getClientBans() : BanList{
		return $this->banByClient;
	}


	/**
	 *
	 * @param string  $name
	 */
	public function addOp($name) {
		$this->operators->set(strtolower($name), true);

		if (($player = $this->getPlayerExact($name)) !== null) {
			$player->recalculatePermissions();
		}
		$this->operators->save(true);
	}


	/**
	 *
	 * @param string  $name
	 */
	public function removeOp($name) {
		$this->operators->remove(strtolower($name));

		if (($player = $this->getPlayerExact($name)) !== null) {
			$player->recalculatePermissions();
		}
		$this->operators->save();
	}


	/**
	 *
	 * @param string  $name
	 */
	public function addWhitelist($name) {
		$this->whitelist->set(strtolower($name), true);
		$this->whitelist->save(true);
	}


	/**
	 *
	 * @param string  $name
	 */
	public function removeWhitelist($name) {
		$this->whitelist->remove(strtolower($name));
		$this->whitelist->save();
	}


	/**
	 *
	 * @param string  $name
	 * @return bool
	 */
	public function isWhitelisted($name): bool{
		return !$this->hasWhitelist() or $this->operators->exists($name, true) or $this->whitelist->exists($name, true);
	}


	/**
	 *
	 * @param string  $name
	 * @return bool
	 */
	public function isOp($name): bool{
		return $this->operators->exists($name, true);
	}


	/**
	 *
	 * @return Config
	 */
	public function getWhitelisted(): Config{
		return $this->whitelist;
	}


	/**
	 *
	 * @return Config
	 */
	public function getOps(): Config{
		return $this->operators;
	}


	/**
	 *
	 */
	public function reloadWhitelist() {
		$this->whitelist->reload();
	}


	/**
	 *
	 * @return string[]
	 */
	public function getCommandAliases(): array{
		$section = $this->getProperty("aliases");
		$result = [];
		if (is_array($section)) {
			foreach ($section as $key => $value) {
				$commands = [];
				if (is_array($value)) {
					$commands = $value;
				}else {
					$commands[] = $value;
				}

				$result[$key] = $commands;
			}
		}

		return $result;
	}


	/**
	 *
	 * @return Server
	 */
	public static function getInstance(): Server{
		return self::$instance;
	}


	/**
	 *
	 * @param int     $microseconds
	 */
	public static function microSleep(int $microseconds) {
		Server::$sleeper->synchronized(function(int $ms) {
				Server::$sleeper->wait($ms);
			}, $microseconds);
	}


	/**
	 *
	 * @param unknown $level
	 * @return unknown
	 */
	public function getExpectedExperience($level) {
		if (isset($this->expCache[$level])) return $this->expCache[$level];
		$levelSquared = $level ** 2;
		if ($level < 16) $this->expCache[$level] = $levelSquared + 6 * $level;
		elseif ($level < 31) $this->expCache[$level] = 2.5 * $levelSquared - 40.5 * $level + 360;
		else $this->expCache[$level] = 4.5 * $levelSquared - 162.5 * $level + 2220;
		return $this->expCache[$level];
	}


	/**
	 *
	 * @param \ClassLoader    $autoloader
	 * @param \ThreadedLogger $logger
	 * @param string          $filePath
	 * @param string          $dataPath
	 * @param string          $pluginPath
	 */
	public function __construct(\ClassLoader $autoloader, \ThreadedLogger $logger, $filePath, $dataPath, $pluginPath) {
		self::$instance = $this;
		self::$sleeper = new \Threaded;
		$this->autoloader = $autoloader;
		$this->logger = $logger;

		try{
			$this->filePath = $filePath;
			if (!file_exists($dataPath . "worlds/")) {
				mkdir($dataPath . "worlds/", 0777);
			}

			if (!file_exists($dataPath . "players/")) {
				mkdir($dataPath . "players/", 0777);
			}

			if (!file_exists($pluginPath)) {
				mkdir($pluginPath, 0777);
			}

			$this->dataPath = realpath($dataPath) . DIRECTORY_SEPARATOR;
			$this->pluginPath = realpath($pluginPath) . DIRECTORY_SEPARATOR;

			$this->console = new CommandReader();

			$version = new VersionString($this->getPocketMineVersion());

			$this->katana = new Katana($this);

			$this->logger->info("Loading ImagicalMine system files....");
			if (!file_exists($this->dataPath . "pocketmine.yml")) {
				$content = file_get_contents($this->filePath . "src/pocketmine/resources/pocketmine.yml");
				if ($version->isDev()) {
					$content = str_replace("preferred-channel: stable", "preferred-channel: beta", $content);
				}
				@file_put_contents($this->dataPath . "pocketmine.yml", $content);
			}
			$this->config = new Config($this->dataPath . "pocketmine.yml", Config::YAML, []);

			$this->logger->info("Loading server properties...");
			$this->properties = new Config($this->dataPath . "server.properties", Config::PROPERTIES, [
					"motd" => "Minecraft: PE Server",
					"server-port" => 19132,
					"white-list" => false,
					"announce-player-achievements" => true,
					"spawn-protection" => 16,
					"redstone-calculation" => true,
					"max-players" => 20,
					"allow-flight" => false,
					"spawn-animals" => true,
					"spawn-mobs" => true,
					"gamemode" => 0,
					"force-gamemode" => false,
					"hardcore" => false,
					"pvp" => true,
					"difficulty" => 1,
					"generator-settings" => "",
					"level-name" => "world",
					"level-seed" => "",
					"level-type" => "DEFAULT",
					"enable-query" => true,
					"enable-rcon" => false,
					"rcon.password" => substr(base64_encode(@Utils::getRandomBytes(20, false)), 3, 10),
					"auto-save" => true,
					"disable-logfile" => false,
				]);

			if ($this->getProperty("log.enable", true)) {
				$this->logger->info("Enabled log writing to server.log");
				$this->logger->Enable();
			}else {
				$this->logger->info("Disabled log writing to server.log");
				$this->logger->Disable();
			}

			$this->forceLanguage = $this->getProperty("settings.force-language", false);
			$this->baseLang = new BaseLang($this->getProperty("settings.language", BaseLang::FALLBACK_LANGUAGE));
			$this->logger->info($this->getLanguage()->translateString("language.selected", [$this->getLanguage()->getName(), $this->getLanguage()->getLang()]));

			$this->memoryManager = new MemoryManager($this);

			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.start", [TextFormat::AQUA . $this->getVersion()]));

			if (($poolSize = $this->getProperty("settings.async-workers", "auto")) === "auto") {
				$poolSize = ServerScheduler::$WORKERS;
				$processors = Utils::getCoreCount() - 2;

				if ($processors > 0) {
					$poolSize = max(1, $processors);
				}
			}

			ServerScheduler::$WORKERS = $poolSize;

			if ($this->getProperty("network.batch-threshold", 256) >= 0) {
				Network::$BATCH_THRESHOLD = (int) $this->getProperty("network.batch-threshold", 256);
			}else {
				Network::$BATCH_THRESHOLD = -1;
			}
			$this->networkCompressionLevel = $this->getProperty("network.compression-level", 7);
			$this->networkCompressionAsync = $this->getProperty("network.async-compression", true);
			$this->autoTickRate = (bool) $this->getProperty("level-settings.auto-tick-rate", true);
			$this->autoTickRateLimit = (int) $this->getProperty("level-settings.auto-tick-rate-limit", 20);
			$this->alwaysTickPlayers = (int) $this->getProperty("level-settings.always-tick-players", false);
			$this->baseTickRate = (int) $this->getProperty("level-settings.base-tick-rate", 1);
			$this->chunkRadius = (int) $this->getProperty("level-settings.chunk-radius", 5);
			$this->snowGolemEnabled = (bool) $this->getProperty("golem.snow-golem-enabled", false);
			$this->ironGolemEnabled = (bool) $this->getProperty("golem.iron-golem-enabled", false);
			$this->incompatibleplugins = (bool) $this->getProperty("settings.incompatible-plugins", false);
			$this->scheduler = new ServerScheduler();

			if ($this->getConfigBoolean("enable-rcon", false) === true) {
				$this->rcon = new RCON($this, $this->getConfigString("rcon.password", ""), $this->getConfigInt("rcon.port", $this->getPort()), ($ip = $this->getIp()) != "" ? $ip : "0.0.0.0", $this->getConfigInt("rcon.threads", 1), $this->getConfigInt("rcon.clients-per-thread", 50));
			}

			$this->entityMetadata = new EntityMetadataStore();
			$this->playerMetadata = new PlayerMetadataStore();
			$this->levelMetadata = new LevelMetadataStore();

			$this->operators = new Config($this->dataPath . "ops.txt", Config::ENUM);
			$this->whitelist = new Config($this->dataPath . "white-list.txt", Config::ENUM);
			if (file_exists($this->dataPath . "banned.txt") and !file_exists($this->dataPath . "banned-players.txt")) {
				@rename($this->dataPath . "banned.txt", $this->dataPath . "banned-players.txt");
			}
			@touch($this->dataPath . "banned-players.txt");
			$this->banByName = new BanList($this->dataPath . "banned-players.txt");
			$this->banByName->load();
			@touch($this->dataPath . "banned-ips.txt");
			$this->banByIP = new BanList($this->dataPath . "banned-ips.txt");
			$this->banByIP->load();
			@touch($this->dataPath . "banned-client.txt");
			$this->banByClient = new BanList($this->dataPath . "banned-client.txt");
			$this->banByClient->load();

			$this->maxPlayers = $this->getConfigInt("max-players", 20);
			$this->setAutoSave($this->getConfigBoolean("auto-save", true));

			if ($this->getConfigBoolean("hardcore", false) === true and $this->getDifficulty() < 3) {
				$this->setConfigInt("difficulty", 3);
			}

			define("pocketmine\\DEBUG", (int) $this->getProperty("debug.level", 1));

			ini_set('assert.exception', 1);

			if ($this->logger instanceof MainLogger) {
				$this->logger->setLogDebug(\pocketmine\DEBUG > 1);
			}

			if (\pocketmine\DEBUG >= 0) {
				@cli_set_process_title($this->getName() . " " . $this->getPocketMineVersion());
			}

			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.networkStart", [$this->getIp() === "" ? "*" : $this->getIp(), $this->getPort()]));
			define("BOOTUP_RANDOM", @Utils::getRandomBytes(16));
			$this->serverID = Utils::getMachineUniqueId($this->getIp() . $this->getPort());

			$this->getLogger()->debug("Server unique id: " . $this->getServerUniqueId());
			$this->getLogger()->debug("Machine unique id: " . Utils::getMachineUniqueId());

			$this->network = new Network($this);
			$this->network->setName($this->getMotd());


			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.info", [
						$this->getName(),
						($version->isDev() ? TextFormat::YELLOW : "") . $version->get(true) . TextFormat::WHITE,
						$this->getCodename(),
						$this->getApiVersion()
					]));
			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.license", [$this->getName()]));
			Timings::init();

			$this->consoleSender = new ConsoleCommandSender();
			$this->commandMap = new SimpleCommandMap($this);

			$this->registerEntities();
			$this->registerTiles();

			InventoryType::init();
			Block::init();
			Item::init();
			Biome::init();
			Effect::init();
			Enchantment::init();
			$this->craftingManager = new CraftingManager();

			$this->pluginManager = new PluginManager($this, $this->commandMap);
			$this->pluginManager->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this->consoleSender);
			$this->pluginManager->setUseTimings($this->getProperty("settings.enable-profiling", false));
			$this->profilingTickRate = (float) $this->getProperty("settings.profile-report-trigger", 20);
			$this->pluginManager->registerInterface(PharPluginLoader::class);
			$this->pluginManager->registerInterface(ScriptPluginLoader::class);

			register_shutdown_function([$this, "crashDump"]);

			$this->queryRegenerateTask = new QueryRegenerateEvent($this, 5);

			$this->network->registerInterface(new RakLibInterface($this));

			$this->pluginManager->loadPlugins($this->pluginPath);

			$this->enablePlugins(PluginLoadOrder::STARTUP);

			LevelProviderManager::addProvider($this, Anvil::class);
			LevelProviderManager::addProvider($this, McRegion::class);
			if (extension_loaded("leveldb")) {
				$this->logger->debug($this->getLanguage()->translateString("pocketmine.debug.enable"));
				LevelProviderManager::addProvider($this, LevelDB::class);
			}


			Generator::addGenerator(Flat::class, "flat");
			Generator::addGenerator(Normal::class, "normal");
			Generator::addGenerator(Normal::class, "default");
			Generator::addGenerator(Nether::class, "hell");
			Generator::addGenerator(Nether::class, "nether");

			foreach ((array) $this->getProperty("worlds", []) as $name => $worldSetting) {
				if ($this->loadLevel($name) === false) {
					$seed = $this->getProperty("worlds.$name.seed", time());
					$options = explode(":", $this->getProperty("worlds.$name.generator", Generator::getGenerator("default")));
					$generator = Generator::getGenerator(array_shift($options));
					if (count($options) > 0) {
						$options = [
							"preset" => implode(":", $options),
						];
					}else {
						$options = [];
					}

					$this->generateLevel($name, $seed, $generator, $options);
				}
			}

			if ($this->getDefaultLevel() === null) {
				$default = $this->getConfigString("level-name", "world");
				if (trim($default) == "") {
					$this->getLogger()->warning("level-name cannot be null, using default");
					$default = "world";
					$this->setConfigString("level-name", "world");
				}
				if ($this->loadLevel($default) === false) {
					$seed = $this->getConfigInt("level-seed", time());
					$this->generateLevel($default, $seed === 0 ? time() : $seed);
				}

				$this->setDefaultLevel($this->getLevelByName($default));
			}


			$this->properties->save(true);

			if (!($this->getDefaultLevel() instanceof Level)) {
				$this->getLogger()->emergency($this->getLanguage()->translateString("pocketmine.level.defaultError"));
				$this->forceShutdown();

				return;
			}

			if ($this->getProperty("ticks-per.autosave", 6000) > 0) {
				$this->autoSaveTicks = (int) $this->getProperty("ticks-per.autosave", 6000);
			}

			$this->enablePlugins(PluginLoadOrder::POSTWORLD);

			$this->start();
		}catch(\Throwable $e) {
			$this->exceptionHandler($e);
		}
	}


	/**
	 *
	 * @param string        $message
	 * @param Player[]|null $recipients
	 * @return int
	 */
	public function broadcastMessage($message, $recipients = null) {
		if (!is_array($recipients)) {
			return $this->broadcast($message, self::BROADCAST_CHANNEL_USERS);
		}

		/** @var Player[] $recipients */
		foreach ($recipients as $recipient) {
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}


	/**
	 *
	 * @param string        $tip
	 * @param Player[]|null $recipients
	 * @return int
	 */
	public function broadcastTip($tip, $recipients = null): int{
		if (!is_array($recipients)) {
			/** @var Player[] $recipients */
			$recipients = [];

			foreach ($this->pluginManager->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible) {
				if ($permissible instanceof Player and $permissible->hasPermission(self::BROADCAST_CHANNEL_USERS)) {
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		/** @var Player[] $recipients */
		foreach ($recipients as $recipient) {
			$recipient->sendTip($tip);
		}

		return count($recipients);
	}


	/**
	 *
	 * @param string        $popup
	 * @param Player[]|null $recipients
	 * @return int
	 */
	public function broadcastPopup(string $popup, $recipients = null): int{
		if (!is_array($recipients)) {
			/** @var Player[] $recipients */
			$recipients = [];

			foreach ($this->pluginManager->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible) {
				if ($permissible instanceof Player and $permissible->hasPermission(self::BROADCAST_CHANNEL_USERS)) {
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		/** @var Player[] $recipients */
		foreach ($recipients as $recipient) {
			$recipient->sendPopup($popup);
		}

		return count($recipients);
	}


	/**
	 *
	 * @param string  $message
	 * @param string  $permissions
	 * @return int
	 */
	public function broadcast($message, $permissions): int{
		/** @var CommandSender[] $recipients */
		$recipients = [];
		foreach (explode(";", $permissions) as $permission) {
			foreach ($this->pluginManager->getPermissionSubscriptions($permission) as $permissible) {
				if ($permissible instanceof CommandSender and $permissible->hasPermission($permission)) {
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		foreach ($recipients as $recipient) {
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}


	/**
	 * Broadcasts a Minecraft packet to a list of players
	 *
	 * @param Player[]   $players
	 * @param DataPacket $packet
	 */
	public static function broadcastPacket(array $players, DataPacket $packet) {
		$packet->encode();
		$packet->isEncoded = true;
		if (Network::$BATCH_THRESHOLD >= 0 and strlen($packet->buffer) >= Network::$BATCH_THRESHOLD) {
			Server::getInstance()->batchPackets($players, [$packet->buffer], false);
			return;
		}

		foreach ($players as $player) {
			$player->dataPacket($packet);
		}
		if (isset($packet->__encapsulatedPacket)) {
			unset($packet->__encapsulatedPacket);
		}
	}


	/**
	 * Broadcasts a list of packets in a batch to a list of players
	 *
	 * @param Player[]            $players
	 * @param DataPacket[]|string $packets
	 * @param bool                $forceSync (optional)
	 */
	public function batchPackets(array $players, array $packets, $forceSync = false) {
		Timings::$playerNetworkTimer->startTiming();
		$str = "";

		foreach ($packets as $p) {
			if ($p instanceof DataPacket) {
				if (!$p->isEncoded) {
					$p->encode();
				}
				$str .= Binary::writeInt(strlen($p->buffer)) . $p->buffer;
			}else {
				$str .= Binary::writeInt(strlen($p)) . $p;
			}
		}

		$targets = [];
		foreach ($players as $p) {
			if ($p->isConnected()) {
				$targets[] = $this->identifiers[spl_object_hash($p)];
			}
		}

		if (!$forceSync and $this->networkCompressionAsync) {
			$task = new CompressBatchedTask($str, $targets, $this->networkCompressionLevel);
			$this->getScheduler()->scheduleAsyncTask($task);
		}else {
			$this->broadcastPacketsCallback(zlib_encode($str, ZLIB_ENCODING_DEFLATE, $this->networkCompressionLevel), $targets);
		}

		Timings::$playerNetworkTimer->stopTiming();
	}


	/**
	 *
	 * @param unknown $data
	 * @param array   $identifiers
	 */
	public function broadcastPacketsCallback($data, array $identifiers) {
		$pk = new BatchPacket();
		$pk->payload = $data;
		$pk->encode();
		$pk->isEncoded = true;

		foreach ($identifiers as $i) {
			if (isset($this->players[$i])) {
				$this->players[$i]->dataPacket($pk);
			}
		}
	}


	/**
	 *
	 * @param int     $type
	 */
	public function enablePlugins($type) {
		foreach ($this->pluginManager->getPlugins() as $plugin) {
			if (!$plugin->isEnabled() and $plugin->getDescription()->getOrder() === $type) {
				$this->enablePlugin($plugin);
			}
		}

		if ($type === PluginLoadOrder::POSTWORLD) {
			$this->commandMap->registerServerAliases();
			DefaultPermissions::registerCorePermissions();
		}
	}


	/**
	 *
	 * @param Plugin  $plugin
	 */
	public function enablePlugin(Plugin $plugin) {
		$this->pluginManager->enablePlugin($plugin);
	}


	/**
	 *
	 */
	public function disablePlugins() {
		$this->pluginManager->disablePlugins();
	}


	/**
	 *
	 */
	public function checkConsole() {
		Timings::$serverCommandTimer->startTiming();
		if (($line = $this->console->getLine()) !== null) {
			$this->pluginManager->callEvent($ev = new ServerCommandEvent($this->consoleSender, $line));
			if (!$ev->isCancelled()) {
				$this->dispatchCommand($ev->getSender(), $ev->getCommand());
			}
		}
		Timings::$serverCommandTimer->stopTiming();
	}


	/**
	 * Executes a command from a CommandSender
	 *
	 *
	 *
	 * @throws \Throwable
	 * @param CommandSender $sender
	 * @param string        $commandLine
	 * @return bool
	 */
	public function dispatchCommand(CommandSender $sender, $commandLine): bool{
		if ($this->commandMap->dispatch($sender, $commandLine)) {
			return true;
		}


		$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.notFound"));

		return false;
	}


	/**
	 *
	 */
	public function reload() {
		$this->logger->info("Saving levels...");

		foreach ($this->levels as $level) {
			$level->save();
		}

		$this->pluginManager->disablePlugins();
		$this->pluginManager->clearPlugins();
		$this->commandMap->clearCommands();

		$this->logger->info("Reloading properties...");
		$this->properties->reload();
		$this->maxPlayers = $this->getConfigInt("max-players", 20);

		if ($this->getConfigBoolean("hardcore", false) === true and $this->getDifficulty() < 3) {
			$this->setConfigInt("difficulty", 3);
		}

		$this->banByIP->load();
		$this->banByName->load();
		$this->banByClient->load();
		$this->reloadWhitelist();
		$this->operators->reload();

		foreach ($this->getIPBans()->getEntries() as $entry) {
			$this->getNetwork()->blockAddress($entry->getName(), -1);
		}

		$this->pluginManager->registerInterface(PharPluginLoader::class);
		$this->pluginManager->registerInterface(ScriptPluginLoader::class);
		$this->pluginManager->loadPlugins($this->pluginPath);
		$this->enablePlugins(PluginLoadOrder::STARTUP);
		$this->enablePlugins(PluginLoadOrder::POSTWORLD);
		TimingsHandler::reload();
	}


	/**
	 * Shutdowns the server correctly
	 */
	public function shutdown() {
		$this->isRunning = false;
	}


	/**
	 *
	 */
	public function forceShutdown() {
		if ($this->hasStopped) {
			return;
		}

		try{
			if (!$this->isRunning()) {
				$this->sendUsage(SendUsageTask::TYPE_CLOSE);
			}

			$this->hasStopped = true;

			$this->shutdown();
			if ($this->rcon instanceof RCON) {
				$this->rcon->stop();
			}

			if ($this->getProperty("network.upnp-forwarding", false) === true) {
				$this->logger->info("[UPnP] Removing port forward...");
				UPnP::RemovePortForward($this->getPort());
			}

			$this->getLogger()->debug("Disabling all plugins");
			$this->pluginManager->disablePlugins();

			foreach ($this->players as $player) {
				$player->close($player->getLeaveMessage(), $this->getProperty("settings.shutdown-message", "Server closed"));
			}

			$this->getLogger()->debug("Unloading all levels");
			foreach ($this->getLevels() as $level) {
				$this->unloadLevel($level, true);
			}

			$this->getLogger()->debug("Removing event handlers");
			HandlerList::unregisterAll();

			$this->getLogger()->debug("Stopping all tasks");
			$this->scheduler->cancelAllTasks();
			$this->scheduler->mainThreadHeartbeat(PHP_INT_MAX);

			$this->getLogger()->debug("Saving properties");
			$this->properties->save();

			$this->getLogger()->debug("Closing console");
			$this->console->shutdown();
			$this->console->notify();

			$this->getLogger()->debug("Stopping network interfaces");
			foreach ($this->network->getInterfaces() as $interface) {
				$interface->shutdown();
				$this->network->unregisterInterface($interface);
			}

			gc_collect_cycles();
		}catch(\Throwable $e) {
			$this->logger->emergency("Crashed while crashing, killing process");
			$this->logger->emergency(get_class($e) . ": ". $e->getMessage());
			@kill(getmypid());
		}

	}


	/**
	 *
	 * @return unknown
	 */
	public function getQueryInformation() {
		return $this->queryRegenerateTask;
	}


	/**
	 * Starts the ImagicalMine server and starts processing ticks and packets
	 */
	public function start() {
		if ($this->getConfigBoolean("enable-query", true) === true) {
			$this->queryHandler = new QueryHandler();
		}

		foreach ($this->getIPBans()->getEntries() as $entry) {
			$this->network->blockAddress($entry->getName(), -1);
		}

		if ($this->getProperty("settings.send-usage", true)) {
			$this->sendUsageTicker = 6000;
			$this->sendUsage(SendUsageTask::TYPE_OPEN);
		}


		if ($this->getProperty("network.upnp-forwarding", false) == true) {
			$this->logger->info("[UPnP] Trying to port forward...");
			UPnP::PortForward($this->getPort());
		}

		$this->tickCounter = 0;

		if (function_exists("pcntl_signal")) {
			pcntl_signal(SIGTERM, [$this, "handleSignal"]);
			pcntl_signal(SIGINT, [$this, "handleSignal"]);
			pcntl_signal(SIGHUP, [$this, "handleSignal"]);
			$this->dispatchSignals = true;
		}

		$this->logger->info($this->getLanguage()->translateString("pocketmine.server.defaultGameMode", [self::getGamemodeString($this->getGamemode())]));

		$this->logger->info($this->getLanguage()->translateString("pocketmine.server.startFinished", [round(microtime(true) - \pocketmine\START_TIME, 3)]));

		$this->tickProcessor();
		$this->forceShutdown();

		gc_collect_cycles();
	}


	/**
	 *
	 * @param unknown $signo
	 */
	public function handleSignal($signo) {
		if ($signo === SIGTERM or $signo === SIGINT or $signo === SIGHUP) {
			$this->shutdown();
		}
	}


	/**
	 *
	 * @param Throwable $e
	 * @param unknown   $trace (optional)
	 */
	public function exceptionHandler(\Throwable $e, $trace = null) {
		if ($e === null) {
			return;
		}

		global $lastError;

		if ($trace === null) {
			$trace = $e->getTrace();
		}

		$errstr = $e->getMessage();
		$errfile = $e->getFile();
		$errno = $e->getCode();
		$errline = $e->getLine();

		$type = ($errno === E_ERROR or $errno === E_USER_ERROR) ? \LogLevel::ERROR : (($errno === E_USER_WARNING or $errno === E_WARNING) ? \LogLevel::WARNING : \LogLevel::NOTICE);
		if (($pos = strpos($errstr, "\n")) !== false) {
			$errstr = substr($errstr, 0, $pos);
		}

		$errfile = cleanPath($errfile);

		$this->logger->logException($e, $trace);

		$lastError = [
			"type" => $type,
			"message" => $errstr,
			"fullFile" => $e->getFile(),
			"file" => $errfile,
			"line" => $errline,
			"trace" => @getTrace(1, $trace)
		];

		global $lastExceptionError, $lastError;
		$lastExceptionError = $lastError;
		$this->crashDump();
	}


	/**
	 *
	 */
	public function crashDump() {
		if ($this->isRunning === false) {
			return;
		}
		if ($this->sendUsageTicker > 0) {
			$this->sendUsage(SendUsageTask::TYPE_CLOSE);
		}
		$this->hasStopped = false;

		ini_set("error_reporting", 0);
		ini_set("memory_limit", -1); //Fix error dump not dumped on memory problems
		$this->logger->emergency($this->getLanguage()->translateString("pocketmine.crash.create"));
		try{
			$dump = new CrashDump($this);
		}catch(\Throwable $e) {
			$this->logger->critical($this->getLanguage()->translateString("pocketmine.crash.error", $e->getMessage()));
			return;
		}

		$this->logger->emergency($this->getLanguage()->translateString("pocketmine.crash.submit", [$dump->getPath()]));


		if ($this->getProperty("auto-report.enabled", true) !== false) {
			$report = true;
			$plugin = $dump->getData()["plugin"];
			if (is_string($plugin)) {
				$p = $this->pluginManager->getPlugin($plugin);
				if ($p instanceof Plugin and !($p->getPluginLoader() instanceof PharPluginLoader)) {
					$report = false;
				}
			}elseif (\Phar::running(true) == "") {
				$report = false;
			}
			if ($dump->getData()["error"]["type"] === "E_PARSE" or $dump->getData()["error"]["type"] === "E_COMPILE_ERROR") {
				$report = false;
			}

			if ($report) {
				$reply = Utils::postURL("http://" . $this->getProperty("auto-report.host", "crash.pocketmine.net") . "/submit/api", [
						"report" => "yes",
						"name" => $this->getName() . " " . $this->getPocketMineVersion(),
						"email" => "crash@pocketmine.net",
						"reportPaste" => base64_encode($dump->getEncodedData())
					]);

				if (($data = json_decode($reply)) !== false and isset($data->crashId)) {
					$reportId = $data->crashId;
					$reportUrl = $data->crashUrl;
					$this->logger->emergency($this->getLanguage()->translateString("pocketmine.crash.archive", [$reportUrl, $reportId]));
				}
			}
		}

		//$this->checkMemory();
		//$dump .= "Memory Usage Tracking: \r\n" . chunk_split(base64_encode(gzdeflate(implode(";", $this->memoryStats), 9))) . "\r\n";

		$this->forceShutdown();
		$this->isRunning = false;
		@kill(getmypid());
		exit(1);
	}


	/**
	 *
	 * @return unknown
	 */
	public function __debugInfo() {
		return [];
	}


	/**
	 *
	 */
	private function tickProcessor() {
		$this->nextTick = microtime(true);
		while ($this->isRunning) {
			$this->tick();
			$next = $this->nextTick - 0.0001;
			if ($next > microtime(true)) {
				try{
					time_sleep_until($next);
				}catch(\Throwable $e) {
					//Sometimes $next is less than the current time. High load?
				}
			}
		}
	}


	/**
	 *
	 * @param Player  $player
	 */
	public function onPlayerLogin(Player $player) {
		if ($this->sendUsageTicker > 0) {
			$this->uniquePlayers[$player->getRawUniqueId()] = $player->getRawUniqueId();
		}

		$this->sendFullPlayerListData($player);
		$this->sendRecipeList($player);
	}


	/**
	 *
	 * @param unknown $identifier
	 * @param Player  $player
	 */
	public function addPlayer($identifier, Player $player) {
		$this->players[$identifier] = $player;
		$this->identifiers[spl_object_hash($player)] = $identifier;
	}


	/**
	 *
	 * @param Player  $player
	 */
	public function addOnlinePlayer(Player $player) {
		$this->playerList[$player->getRawUniqueId()] = $player;

		$this->updatePlayerListData($player->getUniqueId(), $player->getId(), $player->getDisplayName(), $player->getSkinName(), $player->getSkinData());
	}


	/**
	 *
	 * @param Player  $player
	 */
	public function removeOnlinePlayer(Player $player) {
		if (isset($this->playerList[$player->getRawUniqueId()])) {
			unset($this->playerList[$player->getRawUniqueId()]);
			$pk = new PlayerListPacket();
			$pk->type = PlayerListPacket::TYPE_REMOVE;
			$pk->entries[] = [$player->getUniqueId()];
			Server::broadcastPacket($this->playerList, $pk);
		}
	}


	/**
	 *
	 * @param UUID    $uuid
	 * @param unknown $entityId
	 * @param unknown $name
	 * @param unknown $skinName
	 * @param unknown $skinData
	 * @param array   $players          (optional)
	 * @param unknown $skinTransparency (optional)
	 */
	public function updatePlayerListData(UUID $uuid, $entityId, $name, $skinName, $skinData, array $players = null, $skinTransparency = false) {
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;
		$pk->entries[] = [$uuid, $entityId, $name, $skinName, $skinData, $skinTransparency];
		Server::broadcastPacket($players === null ? $this->playerList : $players, $pk);
	}


	/**
	 *
	 * @param UUID    $uuid
	 * @param array   $players (optional)
	 */
	public function removePlayerListData(UUID $uuid, array $players = null) {
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_REMOVE;
		$pk->entries[] = [$uuid];
		Server::broadcastPacket($players === null ? $this->playerList : $players, $pk);
	}


	/**
	 *
	 * @param Player  $p
	 */
	public function sendFullPlayerListData(Player $p) {
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;
		foreach ($this->playerList as $player) {
			$pk->entries[] = [$player->getUniqueId(), $player->getId(), $player->getDisplayName(), $player->getSkinName(), $player->getSkinData(), $player->isSkinTransparent()];
		}

		$p->dataPacket($pk);
	}


	/**
	 *
	 * @param Player  $p
	 */
	public function sendRecipeList(Player $p) {
		$pk = new CraftingDataPacket();
		$pk->cleanRecipes = true;

		foreach ($this->getCraftingManager()->getRecipes() as $recipe) {
			if ($recipe instanceof ShapedRecipe) {
				$pk->addShapedRecipe($recipe);
			}elseif ($recipe instanceof ShapelessRecipe) {
				$pk->addShapelessRecipe($recipe);
			}
		}

		foreach ($this->getCraftingManager()->getFurnaceRecipes() as $recipe) {
			$pk->addFurnaceRecipe($recipe);
		}

		$p->dataPacket($pk);
	}


	/**
	 *
	 * @param unknown $currentTick
	 * @param unknown $tickTime
	 */
	private function checkTickUpdates($currentTick, $tickTime) {
		foreach ($this->players as $p) {
			if (!$p->loggedIn and ($tickTime - $p->creationTime) >= 10) {
				$p->close("", "Login timeout");
			}elseif ($this->alwaysTickPlayers) {
				$p->onUpdate($currentTick);
			}
		}

		//Do level ticks
		foreach ($this->getLevels() as $level) {
			if ($level->getTickRate() > $this->baseTickRate and --$level->tickRateCounter > 0) {
				continue;
			}
			try{
				$levelTime = microtime(true);
				$level->doTick($currentTick);
				$tickMs = (microtime(true) - $levelTime) * 1000;
				$level->tickRateTime = $tickMs;

				if ($this->autoTickRate) {
					if ($tickMs < 50 and $level->getTickRate() > $this->baseTickRate) {
						$level->setTickRate($r = $level->getTickRate() - 1);
						if ($r > $this->baseTickRate) {
							$level->tickRateCounter = $level->getTickRate();
						}
						$this->getLogger()->debug("Raising level \"" . $level->getName() . "\" tick rate to " . $level->getTickRate() . " ticks");
					}elseif ($tickMs >= 50) {
						if ($level->getTickRate() === $this->baseTickRate) {
							$level->setTickRate(max($this->baseTickRate + 1, min($this->autoTickRateLimit, floor($tickMs / 50))));
							$this->getLogger()->debug("Level \"" . $level->getName() . "\" took " . round($tickMs, 2) . "ms, setting tick rate to " . $level->getTickRate() . " ticks");
						}elseif (($tickMs / $level->getTickRate()) >= 50 and $level->getTickRate() < $this->autoTickRateLimit) {
							$level->setTickRate($level->getTickRate() + 1);
							$this->getLogger()->debug("Level \"" . $level->getName() . "\" took " . round($tickMs, 2) . "ms, setting tick rate to " . $level->getTickRate() . " ticks");
						}
						$level->tickRateCounter = $level->getTickRate();
					}
				}
			}catch(\Throwable $e) {
				$this->logger->critical($this->getLanguage()->translateString("pocketmine.level.tickError", [$level->getName(), $e->getMessage()]));
				$this->logger->logException($e);
			}
		}
	}


	/**
	 *
	 */
	public function doAutoSave() {
		if ($this->getAutoSave()) {
			Timings::$worldSaveTimer->startTiming();
			foreach ($this->players as $index => $player) {
				if ($player->isOnline()) {
					$player->save(true);
				}elseif (!$player->isConnected()) {
					$this->removePlayer($player);
				}
			}

			foreach ($this->getLevels() as $level) {
				$level->save(false);
			}
			Timings::$worldSaveTimer->stopTiming();
		}
	}


	/**
	 *
	 * @param unknown $type (optional)
	 */
	public function sendUsage($type = SendUsageTask::TYPE_STATUS) {
		$this->scheduler->scheduleAsyncTask(new SendUsageTask($this, $type, $this->uniquePlayers));
		$this->uniquePlayers = [];
	}


	/**
	 *
	 * @return BaseLang
	 */
	public function getLanguage(): BaseLang{
		return $this->baseLang;
	}


	/**
	 *
	 * @return bool
	 */
	public function isLanguageForced(): bool{
		return $this->forceLanguage;
	}


	/**
	 *
	 * @return Network
	 */
	public function getNetwork(): Network{
		return $this->network;
	}


	/**
	 *
	 * @return MemoryManager
	 */
	public function getMemoryManager(): MemoryManager{
		return $this->memoryManager;
	}


	/**
	 *
	 */
	private function titleTick() {
		if (!Terminal::hasFormattingCodes()) {
			return;
		}

		$d = Utils::getRealMemoryUsage();

		$u = Utils::getMemoryUsage(true);
		$usage = round(($u[0] / 1024) / 1024, 2) . "/" . round(($d[0] / 1024) / 1024, 2) . "/" . round(($u[1] / 1024) / 1024, 2) . "/" . round(($u[2] / 1024) / 1024, 2) . " MB @ " . Utils::getThreadCount() . " threads";

		echo "\x1b]0;" . $this->getName() . " " .
			$this->getPocketMineVersion() .
			" | Online " . count($this->players) . "/" . $this->getMaxPlayers() .
			" | Memory " . $usage .
			" | U " . round($this->network->getUpload() / 1024, 2) .
			" D " . round($this->network->getDownload() / 1024, 2) .
			" kB/s | TPS " . $this->getTicksPerSecond() .
			" | Load " . $this->getTickUsage() . "%\x07";

		$this->network->resetStatistics();
	}


	/**
	 * TODO: move this to Network
	 *
	 * @param string  $address
	 * @param int     $port
	 * @param string  $payload
	 */
	public function handlePacket($address, $port, $payload) {
		try{
			if (strlen($payload) > 2 and substr($payload, 0, 2) === "\xfe\xfd" and $this->queryHandler instanceof QueryHandler) {
				$this->queryHandler->handle($address, $port, $payload);
			}
		}catch(\Throwable $e) {
			if (\pocketmine\DEBUG > 1) {
				$this->logger->logException($e);
			}

			$this->getNetwork()->blockAddress($address, 600);
		}
		//TODO: add raw packet events
	}


	/**
	 * Tries to execute a server tick
	 *
	 * @return unknown
	 */
	private function tick() {
		$tickTime = microtime(true);
		if (($tickTime - $this->nextTick) < -0.025) { //Allow half a tick of diff
			return false;
		}

		Timings::$serverTickTimer->startTiming();

		++$this->tickCounter;

		$this->checkConsole();

		Timings::$connectionTimer->startTiming();
		$this->network->processInterfaces();

		if ($this->rcon !== null) {
			$this->rcon->check();
		}

		Timings::$connectionTimer->stopTiming();

		Timings::$schedulerTimer->startTiming();
		$this->scheduler->mainThreadHeartbeat($this->tickCounter);
		Timings::$schedulerTimer->stopTiming();

		$this->checkTickUpdates($this->tickCounter, $tickTime);

		foreach ($this->players as $player) {
			$player->checkNetwork();
		}

		if (($this->tickCounter & 0b1111) === 0) {
			$this->titleTick();
			$this->maxTick = 20;
			$this->maxUse = 0;

			if (($this->tickCounter & 0b111111111) === 0) {
				try{
					$this->getPluginManager()->callEvent($this->queryRegenerateTask = new QueryRegenerateEvent($this, 5));
					if ($this->queryHandler !== null) {
						$this->queryHandler->regenerateInfo();
					}
				}catch(\Throwable $e) {
					$this->logger->logException($e);
				}
			}

			$this->getNetwork()->updateName();
		}

		if ($this->autoSave and ++$this->autoSaveTicker >= $this->autoSaveTicks) {
			$this->autoSaveTicker = 0;
			$this->doAutoSave();
		}

		if ($this->sendUsageTicker > 0 and --$this->sendUsageTicker === 0) {
			$this->sendUsageTicker = 6000;
			$this->sendUsage(SendUsageTask::TYPE_STATUS);
		}

		if (($this->tickCounter % 100) === 0) {
			foreach ($this->levels as $level) {
				$level->clearCache();
			}

			if ($this->getTicksPerSecondAverage() < 12) {
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.tickOverload"));
			}
		}

		if ($this->dispatchSignals and $this->tickCounter % 5 === 0) {
			pcntl_signal_dispatch();
		}

		$this->getMemoryManager()->check();

		Timings::$serverTickTimer->stopTiming();

		$now = microtime(true);
		$tick = min(20, 1 / max(0.001, $now - $tickTime));
		$use = min(1, ($now - $tickTime) / 0.05);

		TimingsHandler::tick($tick <= $this->profilingTickRate);

		if ($this->maxTick > $tick) {
			$this->maxTick = $tick;
		}

		if ($this->maxUse < $use) {
			$this->maxUse = $use;
		}

		array_shift($this->tickAverage);
		$this->tickAverage[] = $tick;
		array_shift($this->useAverage);
		$this->useAverage[] = $use;

		if (($this->nextTick - $tickTime) < -1) {
			$this->nextTick = $tickTime;
		}else {
			$this->nextTick += 0.05;
		}

		return true;
	}


	/**
	 *
	 */
	private function registerEntities() {
		Entity::registerEntity(Arrow::class);
		Entity::registerEntity(Bat::class);
		Entity::registerEntity(Blaze::class);
		Entity::registerEntity(Boat::class);
		Entity::registerEntity(CavernSpider::class);
		Entity::registerEntity(ChargedCreeper::class);
		Entity::registerEntity(Chicken::class);
		Entity::registerEntity(Cow::class);
		Entity::registerEntity(Creeper::class);
		Entity::registerEntity(DroppedItem::class);
		Entity::registerEntity(Egg::class);
		Entity::registerEntity(Enderman::class);
		Entity::registerEntity(ExperienceOrb::class);
		Entity::registerEntity(FallingSand::class);
		Entity::registerEntity(FishingHook::class);
		Entity::registerEntity(Ghast::class);
		Entity::registerEntity(IronGolem::class);
		Entity::registerEntity(MagmaCube::class);
		Entity::registerEntity(Minecart::class);
		Entity::registerEntity(Mooshroom::class);
		Entity::registerEntity(Ozelot::class);
		Entity::registerEntity(Painting::class);
		Entity::registerEntity(Pig::class);
		Entity::registerEntity(PigZombie::class);
		Entity::registerEntity(PrimedTNT::class);
		Entity::registerEntity(Rabbit::class);
		Entity::registerEntity(Sheep::class);
		Entity::registerEntity(Silverfish::class);
		Entity::registerEntity(Skeleton::class);
		Entity::registerEntity(Slime::class);
		Entity::registerEntity(Snowball::class);
		Entity::registerEntity(SnowGolem::class);
		Entity::registerEntity(Spider::class);
		Entity::registerEntity(Squid::class);
		Entity::registerEntity(ThrownExpBottle::class);
		Entity::registerEntity(ThrownPotion::class);
		Entity::registerEntity(Villager::class);
		Entity::registerEntity(WitherSkeleton::class);
		Entity::registerEntity(Wolf::class);
		Entity::registerEntity(Zombie::class);
		Entity::registerEntity(ZombieVillager::class);
		Entity::registerEntity(Human::class, true);
	}


	/**
	 *
	 */
	private function registerTiles() {
		Tile::registerTile(BrewingStand::class);
		Tile::registerTile(Chest::class);
		Tile::registerTile(Dispenser::class);
		Tile::registerTile(Dropper::class);
		Tile::registerTile(EnchantTable::class);
		Tile::registerTile(Furnace::class);
		Tile::registerTile(FlowerPot::class);
		Tile::registerTile(Hopper::class);
		Tile::registerTile(Sign::class);
		Tile::registerTile(Skull::class);
		Tile::registerTile(TrappedChest::class);
	}


}
