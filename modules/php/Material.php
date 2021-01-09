<?php


namespace SWD;


use SevenWondersDuelPantheon;

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
     * @var Divinities
     */
    public $divinities;

    /**
     * @var Conspiracies
     */
    public $conspiracies;

    /**
     * @var Decrees
     */
    public $decrees;

    /**
     * @var ProgressTokens
     */
    public $progressTokens;

    /**
     * @var OfferingTokens
     */
    public $offeringTokens;

    /**
     * @var MythologyTokens
     */
    public $mythologyTokens;

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
            ->addActionState(SevenWondersDuelPantheon::STATE_CHOOSE_DISCARDED_BUILDING_NAME)
            ->setVictoryPoints(2);

        $this->wonders[6] = (new Wonder(6, clienttranslate("The Great Library")))
            ->setCost([PAPYRUS => 1, GLASS => 1, WOOD => 3])
            ->setProgressTokenFromBox()
            ->setVictoryPoints(4);

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
            ->addText(clienttranslate('When you select this Wonder:'), false)
            ->addText(clienttranslate('Draw 2 Conspiracy cards. Choose 1 to place in front of you face down and put the other one on the top or bottom of the deck (you choose).'))
            ->addText(clienttranslate('When you construct this Wonder:'), false)
            ->setCost([PAPYRUS => 1, GLASS => 1, CLAY => 1, WOOD => 1, STONE => 1])
            ->addActionState(SevenWondersDuelPantheon::STATE_TRIGGER_UNPREPARED_CONSPIRACY_NAME)
            ->setCoins(6)
            ->setExtraTurn();

        $this->wonders[14] = (new Wonder(14, clienttranslate("Knossos")))
            ->addText(clienttranslate('When you select this Wonder:'), false)
            ->addText(clienttranslate('Place 1 Influence cube in a Chamber of your choice.'))
            ->addText(clienttranslate('When you construct this Wonder:'), false)
            ->setCost([GLASS => 2, CLAY => 1, WOOD => 1, STONE => 1])
            ->addActionState(SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_NAME)
            ->addActionState(SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME)
            ->setVictoryPoints(3);

        $this->wonders[15] = (new Wonder(15, clienttranslate("The Sanctuary")))
            ->setCost([PAPYRUS => 1, GLASS => 1, STONE => 2])
            ->setExtraTurn();

        $this->wonders[16] = (new Wonder(16, clienttranslate("The Divine Theater")))
            ->setCost([PAPYRUS => 2, GLASS => 1, WOOD => 2])
//            ->addActionState(SevenWondersDuelPantheon::STATE_TRIGGER_UNPREPARED_CONSPIRACY_NAME)
            ->setVictoryPoints(2);
        
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
            ->setCost([COINS => 3, CLAY => 1])
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
            $this->buildings[$id] = (new Building($id, 5, clienttranslate("Politician (left section)"), Building::TYPE_SENATOR))
                ->setSubType(Building::SUBTYPE_POLITICIAN)
                ->setSenateSection(1)
                ->setListPage(-1);
        }
        for($id = 76; $id <= 78; $id++) {
            $this->buildings[$id] = (new Building($id, 5, clienttranslate("Politician (center section)"), Building::TYPE_SENATOR))
                ->setSubType(Building::SUBTYPE_POLITICIAN)
                ->setSenateSection(2)
                ->setListPage(-1);
        }
        for($id = 79; $id <= 80; $id++) {
            $this->buildings[$id] = (new Building($id, 5, clienttranslate("Politician (right section)"), Building::TYPE_SENATOR))
                ->setSubType(Building::SUBTYPE_POLITICIAN)
                ->setSenateSection(3)
                ->setListPage(-1);
        }
        for($id = 74; $id <= 80; $id++) {
            $building = $this->buildings[$id];
            $building->addText(clienttranslate('Take a number of Senate actions according to the number of Blue cards in your city.'), false)
                ->addText(clienttranslate('0-1 Blue cards: 1 Senate action'))
                ->addText(clienttranslate('2-3 Blue cards: 2 Senate actions'))
                ->addText(clienttranslate('4+ Blue cards: 3 Senate actions'))
                ->addText(clienttranslate('The 2 different Senate actions are:'), false)
                ->addText(clienttranslate('Place Influence (in the section of the Politician)'))
                ->addText(clienttranslate('Move Influence (from any Chamber to an adjacent Chamber)'));
        }
        for($id = 81; $id <= 86; $id++) {
            $this->buildings[$id] = (new Building($id, 5, clienttranslate("Conspirator"), Building::TYPE_SENATOR))
                ->setSubType(Building::SUBTYPE_CONSPIRATOR)
                ->setListPage(-1)
                ->addText(clienttranslate('Take only one of the following actions:'), false)
                ->addText(clienttranslate('Place Influence (1 Influence cube in the chamber of your choice)'))
                ->addText(clienttranslate('Conspire (draw 2 Conspiracies and choose 1 to keep)'));
        }

        $this->buildings[87] = (new Building(87, 6, clienttranslate("Mesopotamian Grand Temple"), Building::TYPE_PURPLE))
            ->setCost([WOOD => 3, GLASS => 1, PAPYRUS => 1])
            ->setLinkedBuilding(-1);

        $this->buildings[88] = (new Building(88, 6, clienttranslate("Phoenician Grand Temple"), Building::TYPE_PURPLE))
            ->setCost([WOOD => 1, STONE => 1, GLASS => 1, PAPYRUS => 2])
            ->setLinkedBuilding(-2);

        $this->buildings[89] = (new Building(89, 6, clienttranslate("Greek Grand Temple"), Building::TYPE_PURPLE))
            ->setCost([STONE => 3, GLASS => 1, PAPYRUS => 1])
            ->setLinkedBuilding(-3);

        $this->buildings[90] = (new Building(90, 6, clienttranslate("Egyptian Grand Temple"), Building::TYPE_PURPLE))
            ->setCost([CLAY => 3, GLASS => 1, PAPYRUS => 1])
            ->setLinkedBuilding(-4);

        $this->buildings[91] = (new Building(91, 6, clienttranslate("Roman Grand Temple"), Building::TYPE_PURPLE))
            ->setCost([CLAY => 1, STONE => 1, GLASS => 2, PAPYRUS => 1])
            ->setLinkedBuilding(-5);

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

        $this->progressTokens[13] = (new ProgressToken(13, clienttranslate("Mysticism")))
            ->addText(clienttranslate("At the end of the game, you score 2 victory points for each Mythology token and each Offering token still in your possession."));

        $this->progressTokens[14] = (new ProgressToken(14, clienttranslate("Poliorcetics")))
            ->addText(clienttranslate("Each time you move the Conflict token forward on the track, your opponent loses a coin for each space moved."));

        $this->progressTokens[15] = (new ProgressToken(15, clienttranslate("Engineering")))
            ->addText(clienttranslate("You can construct, for 1 coin, any card which has, under its cost, a chaining symbol (white symbol), even if your city doesn’t contain the Building or the token which has that symbol."));

        //  ____  _       _       _ _   _
        // |  _ \(_)_   _(_)_ __ (_) |_(_) ___  ___
        // | | | | \ \ / / | '_ \| | __| |/ _ \/ __|
        // | |_| | |\ V /| | | | | | |_| |  __/\__ \
        // |____/|_| \_/ |_|_| |_|_|\__|_|\___||___/

        $this->divinities = new Divinities();

        $this->divinities[1] = (new Divinity(1, clienttranslate("Enki"), Divinity::TYPE_GREEN))
            ->addText(clienttranslate("When Enki is revealed, randomly draw 2 Progress tokens from those discarded at the beginning of the game. These tokens are placed face-up on Enki’s card. When you invoke Enki, choose one of these two Progress tokens and gain it. The other token is returned to the box with those discarded at the beginning of the game."))
            ->addActionState(SevenWondersDuelPantheon::STATE_CHOOSE_ENKI_PROGRESS_TOKEN_NAME);

        $this->divinities[2] = (new Divinity(2, clienttranslate("Ishtar"), Divinity::TYPE_GREEN))
            ->addText(clienttranslate("This Divinity grants the shown scientific symbol (identical to that of the Law Progress token)."))
            ->setScientificSymbol(2);

        $this->divinities[3] = (new Divinity(3, clienttranslate("Nisaba"), Divinity::TYPE_GREEN))
            ->addText(clienttranslate("Place the Snake token on an opponent’s green card. Nisaba is worth the scientific symbol shown on the card."))
            ->addActionState(SevenWondersDuelPantheon::STATE_PLACE_SNAKE_TOKEN_NAME);

        $this->divinities[4] = (new Divinity(4, clienttranslate("Astarte"), Divinity::TYPE_YELLOW))
            ->addText(clienttranslate("Place 7 coins from the bank on Astarte’s card. These are not part of your City’s Treasury, and so are thus protected against coin losses. It’s possible to spend them normally. At the end of the game, each coin still present on Astarte’s card is worth 1 victory point for you."));

        $this->divinities[5] = (new Divinity(5, clienttranslate("Baal"), Divinity::TYPE_YELLOW))
            ->addText(clienttranslate("Steal a brown or grey card built by your opponent, it is added to the cards of your City."))
            ->addActionState(SevenWondersDuelPantheon::STATE_TAKE_BUILDING_NAME);

        $this->divinities[6] = (new Divinity(6, clienttranslate("Tanit"), Divinity::TYPE_YELLOW))
            ->setCoins(12);

        $this->divinities[7] = (new Divinity(7, clienttranslate("Aphrodite"), Divinity::TYPE_BLUE))
            ->setVictoryPoints(9);

        $this->divinities[8] = (new Divinity(8, clienttranslate("Hades"), Divinity::TYPE_BLUE))
            ->addText(clienttranslate("You take all cards discarded since the beginning of the game, choose one, and construct it for free."))
            ->addActionState(SevenWondersDuelPantheon::STATE_CHOOSE_DISCARDED_BUILDING_NAME);

        $this->divinities[9] = (new Divinity(9, clienttranslate("Zeus"), Divinity::TYPE_BLUE))
            ->addText(clienttranslate("Put in the discard pile a card of your choice (face up or down) from the structure, as well as any tokens which may be present on that card."))
            ->addActionState(SevenWondersDuelPantheon::STATE_DISCARD_AGE_CARD_NAME);

        $this->divinities[10] = (new Divinity(10, clienttranslate("Anubis"), Divinity::TYPE_GREY))
            ->addText(clienttranslate("Discard a card previously used to construct a Wonder (an opposing one or one of your own). The affected player doesn’t lose the instant effects previously granted by that Wonder (shields, coins, progress tokens, constructed or discarded card, replay effect)."), false)
            ->addText(clienttranslate("It is possible to rebuild this Wonder and thus apply its effects once again."), false)
            ->addActionState(SevenWondersDuelPantheon::STATE_DECONSTRUCT_WONDER_NAME);

        $this->divinities[11] = (new Divinity(11, clienttranslate("Isis"), Divinity::TYPE_GREY))
            ->addText(clienttranslate("Choose a card from the discard pile and construct one of your Wonders for free using that card."))
            ->addActionState(SevenWondersDuelPantheon::STATE_CONSTRUCT_WONDER_WITH_DISCARDED_BUILDING_NAME);

        $this->divinities[12] = (new Divinity(12, clienttranslate("Ra"), Divinity::TYPE_GREY))
            ->addText(clienttranslate("Steal an opponent’s Wonder which has not yet been constructed; it is added to your own Wonders."))
            ->addActionState(SevenWondersDuelPantheon::STATE_TAKE_UNCONSTRUCTED_WONDER_NAME);

        $this->divinities[13] = (new Divinity(13, clienttranslate("Mars"), Divinity::TYPE_RED))
            ->setMilitary(2);

        $this->divinities[14] = (new Divinity(14, clienttranslate("Minerva"), Divinity::TYPE_RED))
            ->addText(clienttranslate("Place the Minerva pawn on any space of the Military Track. If the Conflict pawn would enter the space which contains the Minerva pawn, it instead stops moving and its movement ends. Then discard the Minerva pawn."))
            ->addActionState(SevenWondersDuelPantheon::STATE_PLACE_MINERVA_TOKEN_NAME);

        $this->divinities[15] = (new Divinity(15, clienttranslate("Neptune"), Divinity::TYPE_RED))
            ->addText(clienttranslate("Choose and discard a Military token without applying its effect. Then choose and apply the effect of another Military token (which is then discarded)."))
            ->addActionState(SevenWondersDuelPantheon::STATE_DISCARD_MILITARY_TOKEN_NAME)
            ->addActionState(SevenWondersDuelPantheon::STATE_APPLY_MILITARY_TOKEN_NAME);

        $this->divinities[16] = (new Divinity(16, clienttranslate("Gate"), Divinity::TYPE_GATE))
            ->addText(clienttranslate("The activation cost of the Gate corresponds to twice the normal activation cost of its space (BGA calculates and previews this)."), false)
            ->addText(clienttranslate("Reveal the top Divinity card from each Mythology deck. Then choose one of the revealed Divinities and activate it for free. Finally, place the other Divinities face down on their respective decks."))
            ->addActionState(SevenWondersDuelPantheon::STATE_CHOOSE_DIVINITY_FROM_TOP_CARDS_NAME);

        $this->mythologyTokens = new MythologyTokens();
        for($divinityNr = 1; $divinityNr <= 5; $divinityNr++) {
            for($pairIndex = 0; $pairIndex <= 1; $pairIndex++) {
                $type = null;
                $id = ($pairIndex * 5) + $divinityNr;
                switch ($id) {
                    case 1:
                    case 2:
                        $type = Divinity::TYPE_GREEN;
                        break;
                    case 3:
                    case 4:
                        $type = Divinity::TYPE_YELLOW;
                        break;
                    case 5:
                    case 6:
                        $type = Divinity::TYPE_BLUE;
                        break;
                    case 7:
                    case 8:
                        $type = Divinity::TYPE_GREY;
                        break;
                    case 9:
                    case 10:
                        $type = Divinity::TYPE_RED;
                        break;
                }
                $this->mythologyTokens[$id] = (new MythologyToken($id, $type));
            }
        }

        $this->offeringTokens = new OfferingTokens();
        $this->offeringTokens[1] = (new OfferingToken(1, 2));
        $this->offeringTokens[2] = (new OfferingToken(2, 3));
        $this->offeringTokens[3] = (new OfferingToken(3, 4));

        //   ____                      _                _
        //  / ___|___  _ __  ___ _ __ (_)_ __ __ _  ___(_) ___  ___
        // | |   / _ \| '_ \/ __| '_ \| | '__/ _` |/ __| |/ _ \/ __|
        // | |__| (_) | | | \__ \ |_) | | | | (_| | (__| |  __/\__ \
        //  \____\___/|_| |_|___/ .__/|_|_|  \__,_|\___|_|\___||___/
        //                      |_|

        $this->conspiracies = new Conspiracies();

        $this->conspiracies[1] = (new Conspiracy(1, clienttranslate("Extortion")))
            ->addText(clienttranslate("Take 1 unconstructed Wonder card of your choice from your opponent and add it to your City."))
            ->addActionState(SevenWondersDuelPantheon::STATE_TAKE_UNCONSTRUCTED_WONDER_NAME)
            ->addActionState(SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME);

        $this->conspiracies[2] = (new Conspiracy(2, clienttranslate("Blackmail")))
            ->addText(clienttranslate("Take half of your opponent’s Coins (rounded up) and add them to your Treasure."))
            ->addActionState(SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME);

        $this->conspiracies[3] = (new Conspiracy(3, clienttranslate("Expropriation")))
            ->addText(clienttranslate("Place 1 Blue card of your choice constructed by your opponent in the discard."))
            ->addActionState(SevenWondersDuelPantheon::STATE_CHOOSE_OPPONENT_BUILDING_NAME)
            ->addActionState(SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME);

        $this->conspiracies[4] = (new Conspiracy(4, clienttranslate("Swindle")))
            ->addText(clienttranslate("Place 1 Yellow card of your choice constructed by your opponent in the discard."))
            ->addActionState(SevenWondersDuelPantheon::STATE_CHOOSE_OPPONENT_BUILDING_NAME)
            ->addActionState(SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME);

        $this->conspiracies[5] = (new Conspiracy(5, clienttranslate("Obscurantism")))
            ->addText(clienttranslate("Choose 1 Progress on the board or that your opponent has or out of game and place it face down on this Conspiracy. No one can use it during this game."))
            ->addActionState(SevenWondersDuelPantheon::STATE_LOCK_PROGRESS_TOKEN_NAME);

        $this->conspiracies[6] = (new Conspiracy(6, clienttranslate("Coup")))
            ->setMilitary(2);

        $this->conspiracies[7] = (new Conspiracy(7, clienttranslate("Property Fraud")))
            ->addText(clienttranslate("Take 1 Building card placed at the end of the structure and build it for free. Senator cards cannot be chosen."))
            ->addActionState(SevenWondersDuelPantheon::STATE_CONSTRUCT_LAST_ROW_BUILDING_NAME);

        $this->conspiracies[8] = (new Conspiracy(8, clienttranslate("Treason")))
            ->addText(clienttranslate("In Age I, take the 3 cards removed from Age I."))
            ->addText(clienttranslate("In Age II, take the 6 cards removed from Ages I and II (3 per Age)."))
            ->addText(clienttranslate("In Age III, take the 9 cards removed from Ages I, II and III (3 per Age)."))
            ->addText(clienttranslate("From these cards, choose 1 to play for free."), false)
            ->addActionState(SevenWondersDuelPantheon::STATE_CONSTRUCT_BUILDING_FROM_BOX_NAME);

        $this->conspiracies[9] = (new Conspiracy(9, clienttranslate("Political Maneuver")))
            ->addActionState(SevenWondersDuelPantheon::STATE_PLACE_INFLUENCE_NAME)
            ->addActionState(SevenWondersDuelPantheon::STATE_REMOVE_INFLUENCE_NAME)
            ->addActionState(SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME);

        $this->conspiracies[10] = (new Conspiracy(10, clienttranslate("Espionage")))
            ->addText(clienttranslate("Take all the Progress tokens removed at the beginning of the game and choose 1 to play."))
            ->addActionState(SevenWondersDuelPantheon::STATE_CHOOSE_PROGRESS_TOKEN_FROM_BOX_NAME);

        $this->conspiracies[11] = (new Conspiracy(11, clienttranslate("Turn Of Events")))
            ->addText(clienttranslate("Place 1 available card in the structure in the discard. You can immediately repeat this action a second time."))
            ->addActionState(SevenWondersDuelPantheon::STATE_DISCARD_AVAILABLE_CARD_NAME)
            ->addActionState(SevenWondersDuelPantheon::STATE_DISCARD_AVAILABLE_CARD_NAME)
            ->addActionState(SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME);

        $this->conspiracies[12] = (new Conspiracy(12, clienttranslate("Embezzlement")))
            ->addText(clienttranslate("Gain as many Coins as Influence cubes you have in the Senate."))
            ->addText(clienttranslate("Your opponent loses as many Coins as Influence cubes they have in the Senate."))
            ->setVisualCoinPosition([-0.176, 0.111])
            ->setVisualOpponentCoinLossPosition([0.671, 0.611]);

        $this->conspiracies[13] = (new Conspiracy(13, clienttranslate("Foreclosure")))
            ->addText(clienttranslate("Take 1 Brown or Grey card of your choice from your opponent and add it to your City."))
            ->addActionState(SevenWondersDuelPantheon::STATE_TAKE_BUILDING_NAME);

        $this->conspiracies[14] = (new Conspiracy(14, clienttranslate("Coercion")))
            ->addText(clienttranslate("Take 1 Blue or Green card of your choice from your opponent and add it to your City. In exchange, give them 1 of your cards of the same color."))
            ->addActionState(SevenWondersDuelPantheon::STATE_SWAP_BUILDING_NAME)
            ->addActionState(SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME);

        $this->conspiracies[15] = (new Conspiracy(15, clienttranslate("Insider Influence")))
            ->addText(clienttranslate("Choose 1 Decree in the Senate and place it in a Chamber of your choice, under the existing Decree."))
            ->addActionState(SevenWondersDuelPantheon::STATE_MOVE_DECREE_NAME)
            ->addActionState(SevenWondersDuelPantheon::STATE_MOVE_INFLUENCE_NAME);

        $this->conspiracies[16] = (new Conspiracy(16, clienttranslate("Sabotage")))
            ->addText(clienttranslate("Choose 1 Wonder constructed by your opponent and return it to the box. It will no longer be used for this game and the lasting effects of this Wonder are lost (lasting effects are Victory Points and Resource Choices)."))
            ->addActionState(SevenWondersDuelPantheon::STATE_DESTROY_CONSTRUCTED_WONDER_NAME);

        //  ____
        // |  _ \  ___  ___ _ __ ___  ___  ___
        // | | | |/ _ \/ __| '__/ _ \/ _ \/ __|
        // | |_| |  __/ (__| | |  __/  __/\__ \
        // |____/ \___|\___|_|  \___|\___||___/

        $this->decrees = new Decrees();

        for ($i = 1; $i <= 4; $i++) {
            $this->decrees[$i] = (new Decree($i, ""))
                ->addText(clienttranslate('Each time you or your opponent constructs a ${color} card, take as many Coins from the bank as the current Age (1, 2, or 3 Coins).'), true, [
                    'color' => $i == 1 ? clienttranslate('Blue') : ($i == 2 ? clienttranslate('Green') : ($i == 3 ? clienttranslate('Yellow') : clienttranslate('Red')))
                ]);
        }
        for ($i = 5; $i <= 7; $i++) {
            $this->decrees[$i] = (new Decree($i, ""))
                ->addText(clienttranslate('Ignore 1 cost symbol (choose either a resource or Coins) when constructing a ${color} card.'), true, [
                    'color' => $i == 5 ? clienttranslate('Yellow') : ($i == 6 ? clienttranslate('Red') : clienttranslate('Green'))
                ]);
        }

        $this->decrees[8] = (new Decree(8, ""))
            ->addText(clienttranslate("Pay 1 resource less (of your choice) when constructing Wonders."));

        $this->decrees[9] = (new Decree(9, ""))
            ->addText(clienttranslate("Gain 1 shield and immediately move the Conflict pawn one space towards your opponent's capital."), false)
            ->addText(clienttranslate("When you lose control of this Decree, you lose this shield and move the Conflict pawn one space towards your capital."), false)
            ->addText(clienttranslate("If your opponent steals control of this Decree from you, the Conflict pawn moves 2 spaces towards your capital."), false);

        for ($i = 10; $i <= 11; $i++) {
            $this->decrees[$i] = (new Decree($i, ""))
                ->addText(
                    $i == 10 ? clienttranslate("Pay 1 Coin less for each Raw material (Brown) resource that you buy from the bank.")
                        : clienttranslate("Pay 1 Coin less for each Manufactured good (Grey) resource that you buy from the bank.")
                    , false)
                ->addText(clienttranslate("You cannot obtain resources for free from this effect; the minimum cost you pay is always 1 Coin."), false);
        }

        $this->decrees[12] = (new Decree(12, ""))
            ->addText(clienttranslate("When determining the number of Senate actions you have, add 2 to the total number of Blue cards you have."));

        $this->decrees[13] = (new Decree(13, ""))
            ->addText(clienttranslate("When discarding a card to take Coins from the bank, gain 2 extra Coins."));

        $this->decrees[14] = (new Decree(14, ""))
            ->addText(clienttranslate("Each time you or your opponent constructs a Wonder take as many Coins from the bank as the current Age (1, 2, or 3 Coins)."));

        $this->decrees[15] = (new Decree(15, ""))
            ->addText(clienttranslate("When constructing a Building, benefit from chains on cards constructed by your opponent as if you constructed them yourself."));

        $this->decrees[16] = (new Decree(16, ""))
            ->addText(clienttranslate("When recruiting a Conspirator, immediately play another turn."));
    }

}