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
            self::$instance->initialize();
        }
        return self::$instance;
    }

    /**
     * This would be the constructor, but later on we use Building::get(), which in turn tries to get the Material singleton instance which wouldn't exist yet during execution of the constructor.
     */
    private function initialize() {
        // __        __              _
        // \ \      / /__  _ __   __| | ___ _ __ ___
        //  \ \ /\ / / _ \| '_ \ / _` |/ _ \ '__/ __|
        //   \ V  V / (_) | | | | (_| |  __/ |  \__ \
        //    \_/\_/ \___/|_| |_|\__,_|\___|_|  |___/

        $this->wonders = new Wonders();

        $this->wonders[1] = (new Wonder(1, clienttranslate("The Pyramids")))
            ->setCost([PAPYRUS => 1, STONE => 3])
            ->setVictoryPoints(9);

        $this->wonders[2] = (new Wonder(2, clienttranslate("The Colossus")))
            ->setCost([GLASS => 1, CLAY => 3])
            ->setMilitary(2)
            ->setVictoryPoints(3);

        $this->wonders[3] = (new Wonder(3, clienttranslate("The Great Lighthouse")))
            ->setCost([PAPYRUS => 2, STONE => 1, WOOD => 1])
            ->setResourceChoice([WOOD, STONE, CLAY])
            ->setVictoryPoints(4);

        $this->wonders[4] = (new Wonder(4, clienttranslate("The Temple of Artemis")))
            ->setCost([PAPYRUS => 1, GLASS => 1, STONE => 1, WOOD => 1])
            ->setCoins(12)
            ->setVisualCoinPosition([0.412, -0.125])
            ->setExtraTurn();

        $this->wonders[5] = (new Wonder(5, clienttranslate("The Mausoleum")))
            ->setCost([PAPYRUS => 1, GLASS => 2, CLAY => 2])
            ->setConstructDiscardedBuilding()
            ->setVictoryPoints(2);

        $this->wonders[6] = (new Wonder(6, clienttranslate("The Great Library")))
            ->setCost([PAPYRUS => 1, GLASS => 1, WOOD => 3])
            ->setProgressTokenFromBox()
            ->setVictoryPoints(6);

        $this->wonders[7] = (new Wonder(7, clienttranslate("Piraeus")))
            ->setCost([CLAY => 1, STONE => 1, WOOD => 2])
            ->setResourceChoice([PAPYRUS, GLASS])
            ->setExtraTurn()
            ->setVictoryPoints(2);

        $this->wonders[8] = (new Wonder(8, clienttranslate("The Hanging Gardens")))
            ->setCost([PAPYRUS => 1, GLASS => 1, WOOD => 2])
            ->setCoins(6)
            ->setVisualCoinPosition([0.412, -0.208])
            ->setExtraTurn()
            ->setVictoryPoints(3);

        $this->wonders[9] = (new Wonder(9, clienttranslate("The Statue of Zeus")))
            ->setCost([PAPYRUS => 2, CLAY => 1, WOOD => 1, STONE => 1])
            ->setDiscardOpponentBuilding(Building::TYPE_BROWN)
            ->setMilitary(1)
            ->setVictoryPoints(3);

        $this->wonders[10] = (new Wonder(10, clienttranslate("The Sphinx")))
            ->setCost([GLASS => 2, CLAY => 1, STONE => 1])
            ->setExtraTurn()
            ->setVictoryPoints(6);

        $this->wonders[11] = (new Wonder(11, clienttranslate("The Appian Way")))
            ->setCost([PAPYRUS => 1, CLAY => 2, STONE => 2])
            ->setCoins(3)
            ->setVisualCoinPosition([0.412, -0.296])
            ->setOpponentCoinLoss(3)
            ->setVisualOpponentCoinLossPosition([0.854, 0.333])
            ->setExtraTurn()
            ->setVictoryPoints(3);

        $this->wonders[12] = (new Wonder(12, clienttranslate("Circus Maximus")))
            ->setCost([GLASS => 1, WOOD => 1, STONE => 2])
            ->setDiscardOpponentBuilding(Building::TYPE_GREY)
            ->setMilitary(1)
            ->setVictoryPoints(3);

        $this->wonders[13] = (new Wonder(13, clienttranslate("Curia Julia")))
            ->setCost([PAPYRUS => 1, GLASS => 1, CLAY => 1, WOOD => 1, STONE => 1])
            ->setCoins(6)
            ->setExtraTurn();

        $this->wonders[14] = (new Wonder(14, clienttranslate("Knossos")))
            ->setCost([GLASS => 2, CLAY => 1, WOOD => 1, STONE => 1])
            ->setVictoryPoints(3);
        
        //     _                  ___
        //    / \   __ _  ___    |_ _|
        //   / _ \ / _` |/ _ \    | |
        //  / ___ \ (_| |  __/    | |
        // /_/   \_\__, |\___|   |___|
        //         |___/

        $this->buildings = new Buildings();

        $this->buildings[1] = (new Building(1, 1, clienttranslate("Lumber Yard"), Building::TYPE_BROWN))
            ->setResources([WOOD => 1])
            ->setListPosition([0, 0]);

        $this->buildings[2] = (new Building(2, 1, clienttranslate("Stone Pit"), Building::TYPE_BROWN))
            ->setCost([COINS => 1])
            ->setResources([STONE => 1])
            ->setListPosition([0, 298]);

        $this->buildings[3] = (new Building(3, 1, clienttranslate("Clay Pool"), Building::TYPE_BROWN))
            ->setResources([CLAY => 1])
            ->setListPosition([0, 119]);

        $this->buildings[4] = (new Building(4, 1, clienttranslate("Logging Camp"), Building::TYPE_BROWN))
            ->setCost([COINS => 1])
            ->setResources([WOOD => 1])
            ->setListPosition([0, 58]);

        $this->buildings[5] = (new Building(5, 1, clienttranslate("Quarry"), Building::TYPE_BROWN))
            ->setResources([STONE => 1])
            ->setListPosition([0, 238]);

        $this->buildings[6] = (new Building(6, 1, clienttranslate("Clay Pit"), Building::TYPE_BROWN))
            ->setCost([COINS => 1])
            ->setResources([CLAY => 1])
            ->setListPosition([0, 177]);

        $this->buildings[7] = (new Building(7, 1, clienttranslate("Glassworks"), Building::TYPE_GREY))
            ->setCost([COINS => 1])
            ->setResources([GLASS => 1])
            ->setListPosition([0, 357]);

        $this->buildings[8] = (new Building(8, 1, clienttranslate("Press"), Building::TYPE_GREY))
            ->setCost([COINS => 1])
            ->setResources([PAPYRUS => 1])
            ->setListPosition([0, 417]);

        $this->buildings[9] = (new Building(9, 1, clienttranslate("Stable"), Building::TYPE_RED))
            ->setCost([WOOD => 1])
            ->setMilitary(1)
            ->setListPosition([0, 0]);

        $this->buildings[10] = (new Building(10, 1, clienttranslate("Garrison"), Building::TYPE_RED))
            ->setCost([CLAY => 1])
            ->setMilitary(1)
            ->setListPosition([0, 63]);

        $this->buildings[11] = (new Building(11, 1, clienttranslate("Palisade"), Building::TYPE_RED))
            ->setCost([COINS => 2])
            ->setMilitary(1)
            ->setListPosition([0, 127]);

        $this->buildings[12] = (new Building(12, 1, clienttranslate("Guard Tower"), Building::TYPE_RED))
            ->setMilitary(1)
            ->setListPosition([0, 477]);

        $this->buildings[13] = (new Building(13, 1, clienttranslate("Scriptorium"), Building::TYPE_GREEN))
            ->setCost([COINS => 2])
            ->setScientificSymbol(6)
            ->setListPosition([0, 317]);

        $this->buildings[14] = (new Building(14, 1, clienttranslate("Workshop"), Building::TYPE_GREEN))
            ->setCost([PAPYRUS => 1])
            ->setVictoryPoints(1)
            ->setScientificSymbol(5)
            ->setListPosition([0, 536]);

        $this->buildings[15] = (new Building(15, 1, clienttranslate("Pharmacist"), Building::TYPE_GREEN))
            ->setCost([COINS => 2])
            ->setScientificSymbol(4)
            ->setListPosition([0, 381]);

        $this->buildings[16] = (new Building(16, 1, clienttranslate("Apothecary"), Building::TYPE_GREEN))
            ->setCost([GLASS => 1])
            ->setVictoryPoints(1)
            ->setScientificSymbol(7)
            ->setListPosition([0, 596]);

        $this->buildings[17] = (new Building(17, 1, clienttranslate("Tavern"), Building::TYPE_YELLOW))
            ->setCoins(4)
            ->setListPosition([0, 828]);

        $this->buildings[18] = (new Building(18, 1, clienttranslate("Stone Reserve"), Building::TYPE_YELLOW))
            ->setCost([COINS => 3])
            ->setFixedPriceResources([STONE => 1])
            ->setListPosition([0, 656]);

        $this->buildings[19] = (new Building(19, 1, clienttranslate("Clay Reserve"), Building::TYPE_YELLOW))
            ->setCost([COINS => 3])
            ->setFixedPriceResources([CLAY => 1])
            ->setListPosition([0, 715]);

        $this->buildings[20] = (new Building(20, 1, clienttranslate("Wood Reserve"), Building::TYPE_YELLOW))
            ->setCost([COINS => 3])
            ->setFixedPriceResources([WOOD => 1])
            ->setListPosition([0, 775]);

        $this->buildings[21] = (new Building(21, 1, clienttranslate("Theater"), Building::TYPE_BLUE))
            ->setVictoryPoints(3)
            ->setListPosition([0, 571]);

        $this->buildings[22] = (new Building(22, 1, clienttranslate("Altar"), Building::TYPE_BLUE))
            ->setVictoryPoints(3)
            ->setListPosition([0, 635]);

        $this->buildings[23] = (new Building(23, 1, clienttranslate("Baths"), Building::TYPE_BLUE))
            ->setCost([STONE => 1])
            ->setVictoryPoints(3)
            ->setListPosition([0, 698]);

        //     _                  ___ ___
        //    / \   __ _  ___    |_ _|_ _|
        //   / _ \ / _` |/ _ \    | | | |
        //  / ___ \ (_| |  __/    | | | |
        // /_/   \_\__, |\___|   |___|___|
        //         |___/

        $this->buildings[24] = (new Building(24, 2, clienttranslate("Sawmill"), Building::TYPE_BROWN))
            ->setCost([COINS => 2])
            ->setResources([WOOD => 2])
            ->setListPosition([244, 0]);

        $this->buildings[25] = (new Building(25, 2, clienttranslate("Shelf Quarry"), Building::TYPE_BROWN))
            ->setCost([COINS => 2])
            ->setResources([STONE => 2])
            ->setListPosition([244, 119]);

        $this->buildings[26] = (new Building(26, 2, clienttranslate("Brickyard"), Building::TYPE_BROWN))
            ->setCost([COINS => 2])
            ->setResources([CLAY => 2])
            ->setListPosition([244, 59]);

        $this->buildings[27] = (new Building(27, 2, clienttranslate("Glass-Blower"), Building::TYPE_GREY))
            ->setResources([GLASS => 1])
            ->setListPosition([244, 178]);

        $this->buildings[28] = (new Building(28, 2, clienttranslate("Drying Room"), Building::TYPE_GREY))
            ->setResources([PAPYRUS => 1])
            ->setListPosition([244, 238]);

        $this->buildings[29] = (new Building(29, 2, clienttranslate("Horse Breeders"), Building::TYPE_RED))
            ->setCost([CLAY => 1, WOOD => 1])
            ->setMilitary(1)
            ->setLinkedBuilding(9)
            ->setListPosition([355, 0]); // Stable

        $this->buildings[30] = (new Building(30, 2, clienttranslate("Barracks"), Building::TYPE_RED))
            ->setCost([COINS => 3])
            ->setMilitary(1)
            ->setLinkedBuilding(10)
            ->setListPosition([355, 63]); // Garrison

        $this->buildings[31] = (new Building(31, 2, clienttranslate("Walls"), Building::TYPE_RED))
            ->setCost([STONE => 2])
            ->setMilitary(2)
            ->setListPosition([244, 298]);

        $this->buildings[32] = (new Building(32, 2, clienttranslate("Archery Range"), Building::TYPE_RED))
            ->setCost([STONE => 1, WOOD => 1, PAPYRUS => 1])
            ->setMilitary(2)
            ->setListPosition([355, 190]);

        $this->buildings[33] = (new Building(33, 2, clienttranslate("Parade Ground"), Building::TYPE_RED))
            ->setCost([CLAY => 2, GLASS => 1])
            ->setMilitary(2)
            ->setListPosition([355, 254]);

        $this->buildings[34] = (new Building(34, 2, clienttranslate("School"), Building::TYPE_GREEN))
            ->setCost([WOOD => 1, PAPYRUS => 2])
            ->setScientificSymbol(7)
            ->setVictoryPoints(1)
            ->setListPosition([355, 444]);

        $this->buildings[35] = (new Building(35, 2, clienttranslate("Laboratory"), Building::TYPE_GREEN))
            ->setCost([WOOD => 1, GLASS => 2])
            ->setScientificSymbol(5)
            ->setVictoryPoints(1)
            ->setListPosition([355, 508]);

        $this->buildings[36] = (new Building(36, 2, clienttranslate("Dispensary"), Building::TYPE_GREEN))
            ->setCost([CLAY => 2, STONE => 1])
            ->setScientificSymbol(4)
            ->setVictoryPoints(2)
            ->setLinkedBuilding(15)
            ->setListPosition([355, 381]); // Pharmacist

        $this->buildings[37] = (new Building(37, 2, clienttranslate("Library"), Building::TYPE_GREEN))
            ->setCost([STONE => 1, WOOD => 1, GLASS => 1])
            ->setScientificSymbol(6)
            ->setVictoryPoints(2)
            ->setLinkedBuilding(13)
            ->setListPosition([355, 317]); // Scriptorium

        $this->buildings[38] = (new Building(38, 2, clienttranslate("Brewery"), Building::TYPE_YELLOW))
            ->setCoins(6)
            ->setListPosition([355, 891]);

        $this->buildings[39] = (new Building(39, 2, clienttranslate("Forum"), Building::TYPE_YELLOW))
            ->setCost([COINS => 3, CLAY => 2])
            ->setResourceChoice([GLASS, PAPYRUS])
            ->setListPosition([244, 357]);

        $this->buildings[40] = (new Building(40, 2, clienttranslate("Caravansery"), Building::TYPE_YELLOW))
            ->setCost([COINS => 2, GLASS => 1, PAPYRUS => 1])
            ->setResourceChoice([WOOD, CLAY, STONE])
            ->setListPosition([244, 417]);

        $this->buildings[41] = (new Building(41, 2, clienttranslate("Customs House"), Building::TYPE_YELLOW))
            ->setCost([COINS => 4])
            ->setFixedPriceResources([PAPYRUS => 1, GLASS => 1])
            ->setListPosition([244, 477]);

        $this->buildings[42] = (new Building(42, 2, clienttranslate("Temple"), Building::TYPE_BLUE))
            ->setCost([WOOD => 1, PAPYRUS => 1])
            ->setVictoryPoints(4)
            ->setLinkedBuilding(22)
            ->setListPosition([355, 635]); // Altar

        $this->buildings[43] = (new Building(43, 2, clienttranslate("Statue"), Building::TYPE_BLUE))
            ->setCost([CLAY => 2])
            ->setVictoryPoints(4)
            ->setLinkedBuilding(21)
            ->setListPosition([355, 571]); // Theater

        $this->buildings[44] = (new Building(44, 2, clienttranslate("Courthouse"), Building::TYPE_BLUE))
            ->setCost([WOOD => 2, GLASS => 1])
            ->setVictoryPoints(5)
            ->setListPosition([244, 536]);

        $this->buildings[45] = (new Building(45, 2, clienttranslate("Aqueduct"), Building::TYPE_BLUE))
            ->setCost([STONE => 3])
            ->setVictoryPoints(5)
            ->setLinkedBuilding(23)
            ->setListPosition([355, 698]); // Baths

        $this->buildings[46] = (new Building(46, 2, clienttranslate("Rostrum"), Building::TYPE_BLUE))
            ->setCost([STONE => 1, WOOD => 1])
            ->setVictoryPoints(4)
            ->setListPosition([355, 762]);

        //     _                  ___ ___ ___
        //    / \   __ _  ___    |_ _|_ _|_ _|
        //   / _ \ / _` |/ _ \    | | | | | |
        //  / ___ \ (_| |  __/    | | | | | |
        // /_/   \_\__, |\___|   |___|___|___|
        //         |___/

        $this->buildings[47] = (new Building(47, 3, clienttranslate("Circus"), Building::TYPE_RED))
            ->setCost([CLAY => 2, STONE => 2])
            ->setMilitary(2)
            ->setLinkedBuilding(33)
            ->setListPosition([709, 254]); // Parade Ground

        $this->buildings[48] = (new Building(48, 3, clienttranslate("Arsenal"), Building::TYPE_RED))
            ->setCost([CLAY => 3, WOOD => 2])
            ->setMilitary(3)
            ->setListPosition([489, 0]);

        $this->buildings[49] = (new Building(49, 3, clienttranslate("Siege Workshop"), Building::TYPE_RED))
            ->setCost([WOOD => 3, GLASS => 1])
            ->setMilitary(2)
            ->setLinkedBuilding(32)
            ->setListPosition([709, 190]); // Archery Range

        $this->buildings[50] = (new Building(50, 3, clienttranslate("Fortifications"), Building::TYPE_RED))
            ->setCost([STONE => 2, CLAY => 1, PAPYRUS => 1])
            ->setMilitary(2)
            ->setLinkedBuilding(11)
            ->setListPosition([709, 127]); // Palisade

        $this->buildings[51] = (new Building(51, 3, clienttranslate("Pretorium"), Building::TYPE_RED))
            ->setCost([COINS => 8])
            ->setMilitary(3)
            ->setListPosition([489, 59]);

        $this->buildings[52] = (new Building(52, 3, clienttranslate("Academy"), Building::TYPE_GREEN))
            ->setCost([STONE => 1, WOOD => 1, GLASS => 2])
            ->setScientificSymbol(3)
            ->setVictoryPoints(3)
            ->setListPosition([489, 119]);

        $this->buildings[53] = (new Building(53, 3, clienttranslate("University"), Building::TYPE_GREEN))
            ->setCost([CLAY => 1, GLASS => 1, PAPYRUS => 1])
            ->setScientificSymbol(1)
            ->setVictoryPoints(2)
            ->setLinkedBuilding(34)
            ->setListPosition([709, 444]); // School

        $this->buildings[54] = (new Building(54, 3, clienttranslate("Study"), Building::TYPE_GREEN))
            ->setCost([WOOD => 2, GLASS => 1, PAPYRUS => 1])
            ->setScientificSymbol(3)
            ->setVictoryPoints(3)
            ->setListPosition([489, 178]);

        $this->buildings[55] = (new Building(55, 3, clienttranslate("Observatory"), Building::TYPE_GREEN))
            ->setCost([STONE => 1, PAPYRUS => 2])
            ->setScientificSymbol(1)
            ->setVictoryPoints(2)
            ->setLinkedBuilding(35)
            ->setListPosition([709, 508]); // Laboratory

        $this->buildings[56] = (new Building(56, 3, clienttranslate("Arena"), Building::TYPE_YELLOW))
            ->setCost([CLAY => 1, STONE => 1, WOOD => 1])
            ->setCoinsPerWonder(2)
            ->setVictoryPoints(3)
            ->setLinkedBuilding(38)
            ->setListPosition([709, 891]); // Brewery

        $this->buildings[57] = (new Building(57, 3, clienttranslate("Chamber Of Commerce"), Building::TYPE_YELLOW))
            ->setCost([PAPYRUS => 2])
            ->setCoinsPerBuildingOfType(Building::TYPE_GREY, 3)
            ->setVictoryPoints(3)
            ->setListPosition([489, 238]);

        $this->buildings[58] = (new Building(58, 3, clienttranslate("Port"), Building::TYPE_YELLOW))
            ->setCost([WOOD => 1, GLASS => 1, PAPYRUS => 1])
            ->setCoinsPerBuildingOfType(Building::TYPE_BROWN, 2)
            ->setVictoryPoints(3)
            ->setListPosition([489, 298]);

        $this->buildings[59] = (new Building(59, 3, clienttranslate("Lighthouse"), Building::TYPE_YELLOW))
            ->setCost([CLAY => 2, GLASS => 1])
            ->setCoinsPerBuildingOfType(Building::TYPE_YELLOW, 1)
            ->setVictoryPoints(3)
            ->setLinkedBuilding(17)
            ->setListPosition([709, 830]); // Tavern

        $this->buildings[60] = (new Building(60, 3, clienttranslate("Armory"), Building::TYPE_YELLOW))
            ->setCost([STONE => 2, GLASS => 1])
            ->setCoinsPerBuildingOfType(Building::TYPE_RED, 1)
            ->setVictoryPoints(3)
            ->setListPosition([489, 357]);

        $this->buildings[61] = (new Building(61, 3, clienttranslate("Palace"), Building::TYPE_BLUE))
            ->setCost([CLAY => 1, STONE => 1, WOOD => 1, GLASS => 2])
            ->setVictoryPoints(7)
            ->setListPosition([489, 417]);

        $this->buildings[62] = (new Building(62, 3, clienttranslate("Gardens"), Building::TYPE_BLUE))
            ->setCost([CLAY => 2, WOOD => 2])
            ->setVictoryPoints(6)
            ->setLinkedBuilding(43)
            ->setListPosition([709, 571]); // Statue

        $this->buildings[63] = (new Building(63, 3, clienttranslate("Pantheon"), Building::TYPE_BLUE))
            ->setCost([CLAY => 1, WOOD => 1, PAPYRUS => 2])
            ->setVictoryPoints(6)
            ->setLinkedBuilding(42)
            ->setListPosition([709, 635]); // Temple

        $this->buildings[64] = (new Building(64, 3, clienttranslate("Town Hall"), Building::TYPE_BLUE))
            ->setCost([STONE => 3, WOOD => 2])
            ->setVictoryPoints(7)
            ->setListPosition([489, 477]);

        $this->buildings[65] = (new Building(65, 3, clienttranslate("Senate"), Building::TYPE_BLUE))
            ->setCost([CLAY => 2, STONE => 1, PAPYRUS => 1])
            ->setVictoryPoints(5)
            ->setLinkedBuilding(46)
            ->setListPosition([709, 762]); // Rostrum

        $this->buildings[66] = (new Building(66, 3, clienttranslate("Obelisk"), Building::TYPE_BLUE))
            ->setCost([STONE => 2, GLASS => 1])
            ->setVictoryPoints(5)
            ->setListPosition([489, 536]);

        //   ____       _ _     _
        //  / ___|_   _(_) | __| |___
        // | |  _| | | | | |/ _` / __|
        // | |_| | |_| | | | (_| \__ \
        //  \____|\__,_|_|_|\__,_|___/

        $this->buildings[67] = (new Building(67, 4, clienttranslate("Merchants Guild"), Building::TYPE_PURPLE))
            ->setCost([CLAY => 1, WOOD => 1, GLASS => 1, PAPYRUS => 1])
            ->setGuildRewardBuildingTypes([Building::TYPE_YELLOW])
            ->setListPosition([735, 0]);

        $this->buildings[68] = (new Building(68, 4, clienttranslate("Shipowners Guild"), Building::TYPE_PURPLE))
            ->setCost([CLAY => 1, STONE => 1, GLASS => 1, PAPYRUS => 1])
            ->setGuildRewardBuildingTypes([Building::TYPE_BROWN, Building::TYPE_GREY])
            ->setListPosition([735, 85]);

        $this->buildings[69] = (new Building(69, 4, clienttranslate("Builders Guild"), Building::TYPE_PURPLE))
            ->setCost([STONE => 2, CLAY => 1, WOOD => 1, GLASS => 1])
            ->setGuildRewardWonders(true)
            ->setListPosition([735, 170]);

        $this->buildings[70] = (new Building(70, 4, clienttranslate("Magistrates Guild"), Building::TYPE_PURPLE))
            ->setCost([WOOD => 2, CLAY => 1, PAPYRUS => 1])
            ->setGuildRewardBuildingTypes([Building::TYPE_BLUE])
            ->setListPosition([735, 255]);

        $this->buildings[71] = (new Building(71, 4, clienttranslate("Scientists Guild"), Building::TYPE_PURPLE))
            ->setCost([CLAY => 2, WOOD => 2])
            ->setGuildRewardBuildingTypes([Building::TYPE_GREEN])
            ->setListPosition([735, 340]);

        $this->buildings[72] = (new Building(72, 4, clienttranslate("Moneylenders Guild"), Building::TYPE_PURPLE))
            ->setCost([STONE => 2, WOOD => 2])
            ->setGuildRewardCoinTriplets(true)
            ->setListPosition([735, 425]);

        $this->buildings[73] = (new Building(73, 4, clienttranslate("Tacticians Guild"), Building::TYPE_PURPLE))
            ->setCost([STONE => 2, CLAY => 1, PAPYRUS => 1])
            ->setGuildRewardBuildingTypes([Building::TYPE_RED])
            ->setListPosition([735, 510]);

        $id = 74;
        // Agora cards
        for($id = 74; $id <= 75; $id++) {
            $this->buildings[$id] = (new Building($id, 5, clienttranslate("Politician (left chambers)"), Building::TYPE_AGORA_WHITE));
        }
        for($id = 75; $id <= 77; $id++) {
            $this->buildings[$id] = (new Building($id, 5, clienttranslate("Politician (center chambers)"), Building::TYPE_AGORA_WHITE));
        }
        for($id = 78; $id <= 79; $id++) {
            $this->buildings[$id] = (new Building($id, 5, clienttranslate("Politician (right chambers)"), Building::TYPE_AGORA_WHITE));
        }
        for($id = 80; $id <= 85; $id++) {
            $this->buildings[$id] = (new Building($id, 5, clienttranslate("Conspirator"), Building::TYPE_AGORA_BLACK));
        }


        //  ____                                      _____     _
        // |  _ \ _ __ ___   __ _ _ __ ___  ___ ___  |_   _|__ | | _____ _ __  ___
        // | |_) | '__/ _ \ / _` | '__/ _ \/ __/ __|   | |/ _ \| |/ / _ \ '_ \/ __|
        // |  __/| | | (_) | (_| | | |  __/\__ \__ \   | | (_) |   <  __/ | | \__ \
        // |_|   |_|  \___/ \__, |_|  \___||___/___/   |_|\___/|_|\_\___|_| |_|___/
        //                  |___/

        $this->progressTokens = new ProgressTokens();
        
        $this->progressTokens[1] = (new ProgressToken(1, clienttranslate("Agriculture")))
            ->setCoins(6)
            ->setVictoryPoints(4);

        $this->progressTokens[2] = (new ProgressToken(2, clienttranslate("Architecture")))
            ->addText(
                clienttranslate("Any future Wonders built by you will cost 2 fewer resources.<br/>BGA will calculate and choose the most advantageous resources for you.")
            );

        $this->progressTokens[3] = (new ProgressToken(3, clienttranslate("Economy")))
            ->addText(clienttranslate("You gain the money spent by your opponent when they trade for resources."));

        $this->progressTokens[4] = (new ProgressToken(4, clienttranslate("Law")))
            ->setScientificSymbol(2);

        $this->progressTokens[5] = (new ProgressToken(5, clienttranslate("Masonry")))
            ->addText(
                clienttranslate("Any future blue cards constructed by you will cost 2 fewer resources.<br/>BGA will calculate and choose the most advantageous resources for you.")
            );

        $this->progressTokens[6] = (new ProgressToken(6, clienttranslate("Mathematics")))
            ->addText(clienttranslate("At the end of the game, score 3 victory points for each Progress token in your possession (including itself)."));

        $this->progressTokens[7] = (new ProgressToken(7, clienttranslate("Philosophy")))
            ->setVictoryPoints(7);

        $this->progressTokens[8] = (new ProgressToken(8, clienttranslate("Strategy")))
            ->addText(clienttranslate("Once this token enters play, your new Military Buildings (red cards) will benefit from 1 extra Shield."));

        $this->progressTokens[9] = (new ProgressToken(9, clienttranslate("Theology")))
            ->addText(
                clienttranslate("All future Wonders constructed by you are all treated as though they have the “Play Again” effect.<br/>Wonders which already have this effect are not affected.")
            );

        $this->progressTokens[10] = (new ProgressToken(10, clienttranslate("Urbanism")))
            ->setCoins(6)
            ->addText(clienttranslate("Each time you construct a Building for free through linking (free construction condition, chain), you gain 4 coins."));

        $this->progressTokens[11] = (new ProgressToken(11, clienttranslate("Corruption")))
            ->addText(clienttranslate("From now on, you recruit all Senators (Politicians and Conspirators) for free."));

        $this->progressTokens[12] = (new ProgressToken(12, clienttranslate("Organized Crime")))
            ->addText(clienttranslate("When you Conspire, keep both Conspiracy cards drawn and place them face down in front of you."));

        // Set the link relationship in the other way, to add text to the tooltip about it.
        Building::get(9)->setLinkedBuilding(29);
        Building::get(10)->setLinkedBuilding(30);
        Building::get(11)->setLinkedBuilding(50);
        Building::get(32)->setLinkedBuilding(49);
        Building::get(33)->setLinkedBuilding(47);
        Building::get(13)->setLinkedBuilding(37);
        Building::get(15)->setLinkedBuilding(36);
        Building::get(34)->setLinkedBuilding(53);
        Building::get(35)->setLinkedBuilding(55);
        Building::get(21)->setLinkedBuilding(43);
        Building::get(43)->setLinkedBuilding(62);
        Building::get(22)->setLinkedBuilding(42);
        Building::get(42)->setLinkedBuilding(63);
        Building::get(23)->setLinkedBuilding(45);
        Building::get(46)->setLinkedBuilding(65);
        Building::get(17)->setLinkedBuilding(59);
        Building::get(38)->setLinkedBuilding(56);

        $this->buildingIdsToLinkIconId = [
            9 => 12, // Horseshoe symbol
            10 => 13, // Sword symbol
            11 => 14, // Tower symbol
            32 => 10, // Target symbol
            33 => 11, // Roman helmet symbol
            13 => 17, // Book symbol
            15 => 16, // Gear symbol
            34 => 15, // Lyre symbol
            35 => 18, // Oil lamp symbol
            21 => 3, // Theatre mask symbol
            43 => 7, // Pillar symbol
            22 => 8, // Moon symbol
            42 => 5, // Sun symbol
            23 => 6, // Water drop symbol
            46 => 7, // Greek building symbol
            17 => 1, // Amphora (vase) symbol
            38 => 2, // Barrel symbol
        ];
    }

}