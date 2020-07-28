<?php


namespace SWD;


class Material extends Base
{

    private static $instance = null;

    /**
     * @var Buildings
     */
    public $buildings;

    /**
     * @var Wonders
     */
    public $wonders;

    /**
     * @var ProgressTokens
     */
    public $progressTokens;

    public $buildingIdsToLinkIconId;

    /**
     * @return Material
     */
    public static function get() {
        if (is_null(self::$instance)) {
            self::$instance = new Material();
        }
        return self::$instance;
    }

    private function __construct() {
        // __        __              _
        // \ \      / /__  _ __   __| | ___ _ __ ___
        //  \ \ /\ / / _ \| '_ \ / _` |/ _ \ '__/ __|
        //   \ V  V / (_) | | | | (_| |  __/ |  \__ \
        //    \_/\_/ \___/|_| |_|\__,_|\___|_|  |___/

        $this->wonders = new Wonders();

        $this->wonders[1] = (new Wonder(1, clienttranslate("The Pyramids"), clienttranslate("
                This Wonder is worth 9 victory points.
            ")))
            ->setCost([PAPYRUS => 1, STONE => 3])
            ->setVictoryPoints(9);

        $this->wonders[2] = (new Wonder(2, clienttranslate("The Colossus"), clienttranslate("
                This Wonder is worth 2 Shields.<br/>
                This Wonder is worth 3 victory points.
            ")))
            ->setCost([GLASS => 1, CLAY => 3])
            ->setMilitary(2)
            ->setVictoryPoints(3);

        $this->wonders[3] = (new Wonder(3, clienttranslate("The Great Lighthouse"), clienttranslate("
                This Wonder produces one unit of the resources shown (Stone, Clay, or Wood) for you each turn.<br/>
                This Wonder is worth 4 victory points.
            ")))
            ->setCost([PAPYRUS => 2, STONE => 1, WOOD => 1])
            ->setResourceChoice([WOOD, STONE, CLAY])
            ->setVictoryPoints(4);

        $this->wonders[4] = (new Wonder(4, clienttranslate("The Temple of Artemis"), clienttranslate("
                Immediately take 12 coins from the Bank.<br/>
                Immediately play a second turn.
            ")))
            ->setCost([PAPYRUS => 1, GLASS => 1, STONE => 1, WOOD => 1])
            ->setCoins(12)
            ->setVisualCoinPosition([0.412, -0.125])
            ->setExtraTurn();

        $this->wonders[5] = (new Wonder(5, clienttranslate("The Mausoleum"), clienttranslate("
                Take all of the cards which have been discarded since the beginning of the game and immediately construct one of your choice for free.<br/>
                This Wonder is worth 2 victory points.
            ")))
            ->setCost([PAPYRUS => 1, GLASS => 2, CLAY => 2])
            ->setConstructDiscardedBuilding()
            ->setVictoryPoints(2);

        $this->wonders[6] = (new Wonder(6, clienttranslate("The Great Library"), clienttranslate("
                Randomly draw 3 Progress tokens from among those discarded at the beginning of the game. Choose one, play it, and return the other 2 to the box.<br/>
                This Wonder is worth 4 victory points.
            ")))
            ->setCost([PAPYRUS => 1, GLASS => 1, WOOD => 3])
            ->setProgressTokenFromBox()
            ->setVictoryPoints(6);

        $this->wonders[7] = (new Wonder(7, clienttranslate("Piraeus"), clienttranslate("
                This Wonder produces one unit of one of the resources shown (Glass or Papyrus) for you each turn.<br/>
                Immediately play a second turn.<br/>
                This Wonder is worth 2 victory points.
            ")))
            ->setCost([CLAY => 1, STONE => 1, WOOD => 2])
            ->setResourceChoice([PAPYRUS, GLASS])
            ->setExtraTurn()
            ->setVictoryPoints(2);

        $this->wonders[8] = (new Wonder(8, clienttranslate("The Hanging Gardens"), clienttranslate("
                You take 6 coins from the bank.<br/>
                Immediately play a second turn.<br/>
                This Wonder is worth 3 victory points.
            ")))
            ->setCost([PAPYRUS => 1, GLASS => 1, WOOD => 2])
            ->setCoins(6)
            ->setVisualCoinPosition([0.412, -0.208])
            ->setExtraTurn()
            ->setVictoryPoints(3);

        $this->wonders[9] = (new Wonder(9, clienttranslate("The Statue of Zeus"), clienttranslate("
                Put in the discard pile one brown card (Raw goods) of your choice constructed by their opponent.<br/>
                This Wonder is worth 1 Shield.<br/>
                This Wonder is worth 3 victory points.
            ")))
            ->setCost([PAPYRUS => 2, CLAY => 1, WOOD => 1, STONE => 1])
            ->setDiscardOpponentBuilding(Building::TYPE_BROWN)
            ->setMilitary(1)
            ->setVictoryPoints(3);

        $this->wonders[10] = (new Wonder(10, clienttranslate("The Sphinx"), clienttranslate("
                Immediately play a second turn.<br/>
                This Wonder is worth 6 victory points.
            ")))
            ->setCost([GLASS => 2, CLAY => 1, STONE => 1])
            ->setExtraTurn()
            ->setVictoryPoints(6);

        $this->wonders[11] = (new Wonder(11, clienttranslate("The Appian Way"), clienttranslate("
                You take 3 coins from the bank.<br/>
                Your opponent loses 3 coins, which are returned to the bank.<br/>
                Immediately play a second turn.<br/>
                This Wonder is worth 3 victory points.
            ")))
            ->setCost([PAPYRUS => 1, CLAY => 2, STONE => 2])
            ->setCoins(3)
            ->setVisualCoinPosition([0.412, -0.296])
            ->setOpponentCoinLoss(3)
            ->setVisualOpponentCoinLossPosition([0.854, 0.333])
            ->setExtraTurn()
            ->setVictoryPoints(3);

        $this->wonders[12] = (new Wonder(12, clienttranslate("Circus Maximus"), clienttranslate("
                Place in the discard pile a grey card (manufactured goods) of your choice constructed by your opponent.<br/>
                This Wonder is worth 1 Shield.<br/>
                This Wonder is worth 3 victory points.
            ")))
            ->setCost([GLASS => 1, WOOD => 1, STONE => 2])
            ->setDiscardOpponentBuilding(Building::TYPE_GREY)
            ->setMilitary(1)
            ->setVictoryPoints(3);
        
        //     _                  ___
        //    / \   __ _  ___    |_ _|
        //   / _ \ / _` |/ _ \    | |
        //  / ___ \ (_| |  __/    | |
        // /_/   \_\__, |\___|   |___|
        //         |___/

        $this->buildings = new Buildings();

        $this->buildings[1] = (new Building(1, 1, clienttranslate("Lumber Yard"), Building::TYPE_BROWN))
            ->setResources([WOOD => 1]);

        $this->buildings[2] = (new Building(2, 1, clienttranslate("Stone Pit"), Building::TYPE_BROWN))
            ->setCost([COINS => 1])
            ->setResources([STONE => 1]);

        $this->buildings[3] = (new Building(3, 1, clienttranslate("Clay Pool"), Building::TYPE_BROWN))
            ->setResources([CLAY => 1]);

        $this->buildings[4] = (new Building(4, 1, clienttranslate("Logging Camp"), Building::TYPE_BROWN))
            ->setCost([COINS => 1])
            ->setResources([WOOD => 1]);

        $this->buildings[5] = (new Building(5, 1, clienttranslate("Quarry"), Building::TYPE_BROWN))
            ->setResources([STONE => 1]);

        $this->buildings[6] = (new Building(6, 1, clienttranslate("Clay Pit"), Building::TYPE_BROWN))
            ->setCost([COINS => 1])
            ->setResources([CLAY => 1]);

        $this->buildings[7] = (new Building(7, 1, clienttranslate("Glassworks"), Building::TYPE_GREY))
            ->setCost([COINS => 1])
            ->setResources([GLASS => 1]);

        $this->buildings[8] = (new Building(8, 1, clienttranslate("Press"), Building::TYPE_GREY))
            ->setCost([COINS => 1])
            ->setResources([PAPYRUS => 1]);

        $this->buildings[9] = (new Building(9, 1, clienttranslate("Stable"), Building::TYPE_RED))
            ->setCost([WOOD => 1])
            ->setMilitary(1);

        $this->buildings[10] = (new Building(10, 1, clienttranslate("Garrison"), Building::TYPE_RED))
            ->setCost([CLAY => 1])
            ->setMilitary(1);

        $this->buildings[11] = (new Building(11, 1, clienttranslate("Palisade"), Building::TYPE_RED))
            ->setCost([COINS => 2])
            ->setMilitary(1);

        $this->buildings[12] = (new Building(12, 1, clienttranslate("Guard Tower"), Building::TYPE_RED))
            ->setMilitary(1);

        $this->buildings[13] = (new Building(13, 1, clienttranslate("Scriptorium"), Building::TYPE_GREEN))
            ->setCost([COINS => 2])
            ->setScientificSymbol(6);

        $this->buildings[14] = (new Building(14, 1, clienttranslate("Workshop"), Building::TYPE_GREEN))
            ->setCost([PAPYRUS => 1])
            ->setVictoryPoints(1)
            ->setScientificSymbol(5);

        $this->buildings[15] = (new Building(15, 1, clienttranslate("Pharmacist"), Building::TYPE_GREEN))
            ->setCost([COINS => 2])
            ->setScientificSymbol(4);

        $this->buildings[16] = (new Building(16, 1, clienttranslate("Apothecary"), Building::TYPE_GREEN))
            ->setCost([GLASS => 1])
            ->setVictoryPoints(1)
            ->setScientificSymbol(7);

        $this->buildings[17] = (new Building(17, 1, clienttranslate("Tavern"), Building::TYPE_YELLOW))
            ->setCoins(4);

        $this->buildings[18] = (new Building(18, 1, clienttranslate("Stone Reserve"), Building::TYPE_YELLOW))
            ->setCost([COINS => 3])
            ->setFixedPriceResources([STONE => 1]);

        $this->buildings[19] = (new Building(19, 1, clienttranslate("Clay Reserve"), Building::TYPE_YELLOW))
            ->setCost([COINS => 3])
            ->setFixedPriceResources([CLAY => 1]);

        $this->buildings[20] = (new Building(20, 1, clienttranslate("Wood Reserve"), Building::TYPE_YELLOW))
            ->setCost([COINS => 3])
            ->setFixedPriceResources([WOOD => 1]);

        $this->buildings[21] = (new Building(21, 1, clienttranslate("Theater"), Building::TYPE_BLUE))
            ->setVictoryPoints(3);

        $this->buildings[22] = (new Building(22, 1, clienttranslate("Altar"), Building::TYPE_BLUE))
            ->setVictoryPoints(3);

        $this->buildings[23] = (new Building(23, 1, clienttranslate("Baths"), Building::TYPE_BLUE))
            ->setCost([STONE => 1])
            ->setVictoryPoints(3);

        //     _                  ___ ___
        //    / \   __ _  ___    |_ _|_ _|
        //   / _ \ / _` |/ _ \    | | | |
        //  / ___ \ (_| |  __/    | | | |
        // /_/   \_\__, |\___|   |___|___|
        //         |___/

        $this->buildings[24] = (new Building(24, 2, clienttranslate("Sawmill"), Building::TYPE_BROWN))
            ->setCost([COINS => 2])
            ->setResources([WOOD => 2]);

        $this->buildings[25] = (new Building(25, 2, clienttranslate("Shelf Quarry"), Building::TYPE_BROWN))
            ->setCost([COINS => 2])
            ->setResources([STONE => 2]);

        $this->buildings[26] = (new Building(26, 2, clienttranslate("Brickyard"), Building::TYPE_BROWN))
            ->setCost([COINS => 2])
            ->setResources([CLAY => 2]);

        $this->buildings[27] = (new Building(27, 2, clienttranslate("Glass-Blower"), Building::TYPE_GREY))
            ->setResources([GLASS => 1]);

        $this->buildings[28] = (new Building(28, 2, clienttranslate("Drying Room"), Building::TYPE_GREY))
            ->setResources([PAPYRUS => 1]);

        $this->buildings[29] = (new Building(29, 2, clienttranslate("Horse Breeders"), Building::TYPE_RED))
            ->setCost([CLAY => 1, WOOD => 1])
            ->setLinkedBuilding(9) // Stable
            ->setMilitary(1);

        $this->buildings[30] = (new Building(30, 2, clienttranslate("Barracks"), Building::TYPE_RED))
            ->setCost([COINS => 3])
            ->setLinkedBuilding(10) // Garrison
            ->setMilitary(1);

        $this->buildings[31] = (new Building(31, 2, clienttranslate("Walls"), Building::TYPE_RED))
            ->setCost([STONE => 2])
            ->setMilitary(2);

        $this->buildings[32] = (new Building(32, 2, clienttranslate("Archery Range"), Building::TYPE_RED))
            ->setCost([STONE => 1, WOOD => 1, PAPYRUS => 1])
            ->setMilitary(2);

        $this->buildings[33] = (new Building(33, 2, clienttranslate("Parade Ground"), Building::TYPE_RED))
            ->setCost([CLAY => 2, GLASS => 1])
            ->setMilitary(2);

        $this->buildings[34] = (new Building(34, 2, clienttranslate("School"), Building::TYPE_GREEN))
            ->setCost([WOOD => 1, PAPYRUS => 2])
            ->setScientificSymbol(7)
            ->setVictoryPoints(1);

        $this->buildings[35] = (new Building(35, 2, clienttranslate("Laboratory"), Building::TYPE_GREEN))
            ->setCost([WOOD => 1, GLASS => 2])
            ->setScientificSymbol(5)
            ->setVictoryPoints(1);

        $this->buildings[36] = (new Building(36, 2, clienttranslate("Dispensary"), Building::TYPE_GREEN))
            ->setCost([CLAY => 2, STONE => 1])
            ->setLinkedBuilding(15) // Pharmacist
            ->setScientificSymbol(4)
            ->setVictoryPoints(2);

        $this->buildings[37] = (new Building(37, 2, clienttranslate("Library"), Building::TYPE_GREEN))
            ->setCost([STONE => 1, WOOD => 1, GLASS => 1])
            ->setLinkedBuilding(13) // Scriptorium
            ->setScientificSymbol(6)
            ->setVictoryPoints(2);

        $this->buildings[38] = (new Building(38, 2, clienttranslate("Brewery"), Building::TYPE_YELLOW))
            ->setCoins(6);

        $this->buildings[39] = (new Building(39, 2, clienttranslate("Forum"), Building::TYPE_YELLOW))
            ->setCost([COINS => 3, CLAY => 2])
            ->setResourceChoice([GLASS, PAPYRUS]);

        $this->buildings[40] = (new Building(40, 2, clienttranslate("Caravansery"), Building::TYPE_YELLOW))
            ->setCost([COINS => 2, GLASS => 1, PAPYRUS => 1])
            ->setResourceChoice([WOOD, CLAY, STONE]);

        $this->buildings[41] = (new Building(41, 2, clienttranslate("Customs House"), Building::TYPE_YELLOW))
            ->setCost([COINS => 4])
            ->setFixedPriceResources([PAPYRUS => 1, GLASS => 1]);

        $this->buildings[42] = (new Building(42, 2, clienttranslate("Temple"), Building::TYPE_BLUE))
            ->setCost([WOOD => 1, PAPYRUS => 1])
            ->setLinkedBuilding(22) // Altar
            ->setVictoryPoints(4);

        $this->buildings[43] = (new Building(43, 2, clienttranslate("Statue"), Building::TYPE_BLUE))
            ->setCost([CLAY => 2])
            ->setLinkedBuilding(21) // Theater
            ->setVictoryPoints(4);

        $this->buildings[44] = (new Building(44, 2, clienttranslate("Courthouse"), Building::TYPE_BLUE))
            ->setCost([WOOD => 2, GLASS => 1])
            ->setVictoryPoints(5);

        $this->buildings[45] = (new Building(45, 2, clienttranslate("Aqueduct"), Building::TYPE_BLUE))
            ->setCost([STONE => 3])
            ->setLinkedBuilding(23) // Baths
            ->setVictoryPoints(5);

        $this->buildings[46] = (new Building(46, 2, clienttranslate("Rostrum"), Building::TYPE_BLUE))
            ->setCost([STONE => 1, WOOD => 1])
            ->setVictoryPoints(4);

        //     _                  ___ ___ ___
        //    / \   __ _  ___    |_ _|_ _|_ _|
        //   / _ \ / _` |/ _ \    | | | | | |
        //  / ___ \ (_| |  __/    | | | | | |
        // /_/   \_\__, |\___|   |___|___|___|
        //         |___/

        $this->buildings[47] = (new Building(47, 3, clienttranslate("Circus"), Building::TYPE_RED))
            ->setCost([CLAY => 2, STONE => 2])
            ->setLinkedBuilding(33) // Parade Ground
            ->setMilitary(2);

        $this->buildings[48] = (new Building(48, 3, clienttranslate("Arsenal"), Building::TYPE_RED))
            ->setCost([CLAY => 3, WOOD => 2])
            ->setMilitary(3);

        $this->buildings[49] = (new Building(49, 3, clienttranslate("Siege Workshop"), Building::TYPE_RED))
            ->setCost([WOOD => 3, GLASS => 1])
            ->setLinkedBuilding(32) // Archery Range
            ->setMilitary(2);

        $this->buildings[50] = (new Building(50, 3, clienttranslate("Fortifications"), Building::TYPE_RED))
            ->setCost([STONE => 2, CLAY => 1, PAPYRUS => 1])
            ->setLinkedBuilding(11) // Palisade
            ->setMilitary(2);

        $this->buildings[51] = (new Building(51, 3, clienttranslate("Pretorium"), Building::TYPE_RED))
            ->setCost([COINS => 8])
            ->setMilitary(3);

        $this->buildings[52] = (new Building(52, 3, clienttranslate("Academy"), Building::TYPE_GREEN))
            ->setCost([STONE => 1, WOOD => 1, GLASS => 2])
            ->setScientificSymbol(3)
            ->setVictoryPoints(3);

        $this->buildings[53] = (new Building(53, 3, clienttranslate("University"), Building::TYPE_GREEN))
            ->setCost([CLAY => 1, GLASS => 1, PAPYRUS => 1])
            ->setLinkedBuilding(34) // School
            ->setScientificSymbol(1)
            ->setVictoryPoints(2);

        $this->buildings[54] = (new Building(54, 3, clienttranslate("Study"), Building::TYPE_GREEN))
            ->setCost([WOOD => 2, GLASS => 1, PAPYRUS => 1])
            ->setScientificSymbol(3)
            ->setVictoryPoints(3);

        $this->buildings[55] = (new Building(55, 3, clienttranslate("Observatory"), Building::TYPE_GREEN))
            ->setCost([STONE => 1, PAPYRUS => 2])
            ->setLinkedBuilding(35) // Laboratory
            ->setScientificSymbol(1)
            ->setVictoryPoints(2);

        $this->buildings[56] = (new Building(56, 3, clienttranslate("Arena"), Building::TYPE_YELLOW))
            ->setCost([CLAY => 1, STONE => 1, WOOD => 1])
            ->setLinkedBuilding(38) // Brewery
            ->setCoinsPerWonder(2)
            ->setVictoryPoints(3);

        $this->buildings[57] = (new Building(57, 3, clienttranslate("Chamber Of Commerce"), Building::TYPE_YELLOW))
            ->setCost([PAPYRUS => 2])
            ->setCoinsPerBuildingOfType(Building::TYPE_GREY, 3)
            ->setVictoryPoints(3);

        $this->buildings[58] = (new Building(58, 3, clienttranslate("Port"), Building::TYPE_YELLOW))
            ->setCost([WOOD => 1, GLASS => 1, PAPYRUS => 1])
            ->setCoinsPerBuildingOfType(Building::TYPE_BROWN, 2)
            ->setVictoryPoints(3);

        $this->buildings[59] = (new Building(59, 3, clienttranslate("Lighthouse"), Building::TYPE_YELLOW))
            ->setCost([CLAY => 2, GLASS => 1])
            ->setLinkedBuilding(17) // Tavern
            ->setCoinsPerBuildingOfType(Building::TYPE_YELLOW, 1)
            ->setVictoryPoints(3);

        $this->buildings[60] = (new Building(60, 3, clienttranslate("Armory"), Building::TYPE_YELLOW))
            ->setCost([STONE => 2, GLASS => 1])
            ->setCoinsPerBuildingOfType(Building::TYPE_RED, 1)
            ->setVictoryPoints(3);

        $this->buildings[61] = (new Building(61, 3, clienttranslate("Palace"), Building::TYPE_BLUE))
            ->setCost([CLAY => 1, STONE => 1, WOOD => 1, GLASS => 2])
            ->setVictoryPoints(7);

        $this->buildings[62] = (new Building(62, 3, clienttranslate("Gardens"), Building::TYPE_BLUE))
            ->setCost([CLAY => 2, WOOD => 2])
            ->setLinkedBuilding(43) // Statue
            ->setVictoryPoints(6);

        $this->buildings[63] = (new Building(63, 3, clienttranslate("Pantheon"), Building::TYPE_BLUE))
            ->setCost([CLAY => 1, WOOD => 1, PAPYRUS => 2])
            ->setLinkedBuilding(42) // Temple
            ->setVictoryPoints(6);

        $this->buildings[64] = (new Building(64, 3, clienttranslate("Town Hall"), Building::TYPE_BLUE))
            ->setCost([STONE => 3, WOOD => 2])
            ->setVictoryPoints(7);

        $this->buildings[65] = (new Building(65, 3, clienttranslate("Senate"), Building::TYPE_BLUE))
            ->setCost([CLAY => 2, STONE => 1, PAPYRUS => 1])
            ->setLinkedBuilding(46) // Rostrum
            ->setVictoryPoints(5);

        $this->buildings[66] = (new Building(66, 3, clienttranslate("Obelisk"), Building::TYPE_BLUE))
            ->setCost([STONE => 2, GLASS => 1])
            ->setVictoryPoints(5);

        //   ____       _ _     _
        //  / ___|_   _(_) | __| |___
        // | |  _| | | | | |/ _` / __|
        // | |_| | |_| | | | (_| \__ \
        //  \____|\__,_|_|_|\__,_|___/

        $this->buildings[67] = (new Building(67, 4, clienttranslate("Merchants Guild"), Building::TYPE_PURPLE))
            ->setCost([CLAY => 1, WOOD => 1, GLASS => 1, PAPYRUS => 1])
            ->setGuildRewardBuildingTypes([Building::TYPE_YELLOW]);

        $this->buildings[68] = (new Building(68, 4, clienttranslate("Shipowners Guild"), Building::TYPE_PURPLE))
            ->setCost([CLAY => 1, STONE => 1, GLASS => 1, PAPYRUS => 1])
            ->setGuildRewardBuildingTypes([Building::TYPE_BROWN, Building::TYPE_GREY]);

        $this->buildings[69] = (new Building(69, 4, clienttranslate("Builders Guild"), Building::TYPE_PURPLE))
            ->setCost([STONE => 2, CLAY => 1, WOOD => 1, GLASS => 1])
            ->setGuildRewardWonders(true);

        $this->buildings[70] = (new Building(70, 4, clienttranslate("Magistrates Guild"), Building::TYPE_PURPLE))
            ->setCost([WOOD => 2, CLAY => 1, PAPYRUS => 1])
            ->setGuildRewardBuildingTypes([Building::TYPE_BLUE]);

        $this->buildings[71] = (new Building(71, 4, clienttranslate("Scientists Guild"), Building::TYPE_PURPLE))
            ->setCost([CLAY => 2, WOOD => 2])
            ->setGuildRewardBuildingTypes([Building::TYPE_GREEN]);

        $this->buildings[72] = (new Building(72, 4, clienttranslate("Moneylenders Guild"), Building::TYPE_PURPLE))
            ->setCost([STONE => 2, WOOD => 2])
            ->setGuildRewardCoinTriplets(true);

        $this->buildings[73] = (new Building(73, 4, clienttranslate("Tacticians Guild"), Building::TYPE_PURPLE))
            ->setCost([STONE => 2, CLAY => 1, PAPYRUS => 1])
            ->setGuildRewardBuildingTypes([Building::TYPE_RED]);

        //  ____                                      _____     _
        // |  _ \ _ __ ___   __ _ _ __ ___  ___ ___  |_   _|__ | | _____ _ __  ___
        // | |_) | '__/ _ \ / _` | '__/ _ \/ __/ __|   | |/ _ \| |/ / _ \ '_ \/ __|
        // |  __/| | | (_) | (_| | | |  __/\__ \__ \   | | (_) |   <  __/ | | \__ \
        // |_|   |_|  \___/ \__, |_|  \___||___/___/   |_|\___/|_|\_\___|_| |_|___/
        //                  |___/

        $this->progressTokens = new ProgressTokens();
        
        $this->progressTokens[1] = (new ProgressToken(1, clienttranslate("Agriculture"), clienttranslate("
            Immediately take 6 coins from the Bank.<br/>
            The token is worth 4 victory points.")))
            ->setCoins(6)
            ->setVictoryPoints(4);

        $this->progressTokens[2] = (new ProgressToken(2, clienttranslate("Architecture"), clienttranslate("
            Any future Wonders built by you will cost 2 fewer resources.<br/>
            BGA will calculate and choose the most advantageous resources for you.")));

        $this->progressTokens[3] = (new ProgressToken(3, clienttranslate("Economy"), clienttranslate("
            You gain the money spent by your opponent when they trade for resources.")));

        $this->progressTokens[4] = (new ProgressToken(4, clienttranslate("Law"), clienttranslate("
            This token is worth a scientific symbol.")))
            ->setScientificSymbol(2);

        $this->progressTokens[5] = (new ProgressToken(5, clienttranslate("Masonry"), clienttranslate("
            Any future blue cards constructed by you will cost 2 fewer resources.<br/>
            BGA will calculate and choose the most advantageous resources for you.")));

        $this->progressTokens[6] = (new ProgressToken(6, clienttranslate("Mathematics"), clienttranslate("
            At the end of the game, score 3 victory points for each Progress token in your possession (including itself).")));

        $this->progressTokens[7] = (new ProgressToken(7, clienttranslate("Philosophy"), clienttranslate("
            The token is worth 7 victory points.")))
            ->setVictoryPoints(7);

        $this->progressTokens[8] = (new ProgressToken(8, clienttranslate("Strategy"), clienttranslate("
            Once this token enters play, your new military Buildings (red cards) will benefit from 1 extra Shield.")));

        $this->progressTokens[9] = (new ProgressToken(9, clienttranslate("Theology"), clienttranslate("
            All future Wonders constructed by you are all treated as though they have the “Play Again” effect.<br/>
            Wonders which already have this effect are not affected.")));

        $this->progressTokens[10] = (new ProgressToken(10, clienttranslate("Urbanism"), clienttranslate("
            Immediately take 6 coins from the Bank.<br/>
            Each time you construct a Building for free through linking (free construction condition, chain), you gain 4 coins.")))
            ->setCoins(6);

        $this->buildingIdsToLinkIconId = [
            9 => 12, // Horseshoe sybmol
            10 => 13, // Sword sybmol
            11 => 14, // Tower sybmol
            32 => 10, // Target sybmol
            33 => 11, // Roman helmet sybmol
            13 => 17, // Book sybmol
            15 => 16, // Gear sybmol
            34 => 15, // Lyre sybmol
            35 => 18, // Oil lamp sybmol
            21 => 3, // Theatre mask sybmol
            43 => 7, // Pillar sybmol
            22 => 8, // Moon sybmol
            42 => 5, // Sun sybmol
            23 => 6, // Water drop sybmol
            46 => 7, // Greek building sybmol
            17 => 1, // Amphora (vase) sybmol
            38 => 2, // Barrel sybmol
        ];

    }

}