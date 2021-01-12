<?php


namespace SWD\States;


use SevenWondersDuelPantheon;
use SWD\Material;
use SWD\Player;

trait GameSetupTrait
{

    public function enterStateGameSetup() {
        $agora = $this->getGameStateValue(self::OPTION_AGORA);
        $pantheon = $this->getGameStateValue(self::OPTION_PANTHEON);

        // Set up two 4-wonders selection pools, rest of the wonders go back to the box.
        $this->wonderDeck->createCards(Material::get()->wonders->getDeckCards(1, 12));
        if ($agora) $this->wonderDeck->createCards(Material::get()->wonders->getDeckCards(13, 14));
        if ($pantheon) $this->wonderDeck->createCards(Material::get()->wonders->getDeckCards(15, 16));
        $this->matchDeckIds("wonder");

        $selectWonders = 8;
        if ($agora && $this->getGameStateValue(self::OPTION_AGORA_WONDERS)) {
            $selectWonders -= 2;
            $this->wonderDeck->moveCards([13, 14], 'selection');
        }
        if ($pantheon && $this->getGameStateValue(self::OPTION_PANTHEON_WONDERS)) {
            $selectWonders -= 2;
            $this->wonderDeck->moveCards([15, 16], 'selection');
        }
        $this->wonderDeck->shuffle('deck');
        $this->wonderDeck->pickCardsForLocation($selectWonders, 'deck', 'selection');
        $this->wonderDeck->shuffle('selection');

        $this->wonderDeck->pickCardsForLocation(4, 'selection', 'selection1');
        $this->wonderDeck->shuffle('selection1'); // Ensures we have defined card_location_arg
        $this->wonderDeck->pickCardsForLocation(4, 'selection', 'selection2');
        $this->wonderDeck->shuffle('selection2'); // Ensures we have defined card_location_arg
        $this->wonderDeck->moveAllCardsInLocation('deck', 'box');

        if ($agora) {
            // Prepare senator cards
            $this->buildingDeck->createCards(Material::get()->buildings->filterByAge(5)->getDeckCards(), "senators" );
            $this->buildingDeck->shuffle("senators");
        }

        // Set up card piles for the 3 ages.
        // "Return to the box, without looking at them, 3 cards from each Age deck.
        for ($age = 1; $age <= 3; $age++) {
            $this->buildingDeck->createCards(Material::get()->buildings->filterByAge($age)->getDeckCards(), "age{$age}" );
            $this->buildingDeck->shuffle("age{$age}");
            $this->buildingDeck->pickCardsForLocation(3, "age{$age}", 'box');
            if ($age == 3) {
                if ($pantheon) {
                    // Then randomly draw 3 Grand Temple cards and add them to the Age 3 deck.
                    $this->buildingDeck->createCards(Material::get()->buildings->getDeckCards(87, 91), 'grandtemples' );
                    $this->buildingDeck->shuffle( 'grandtemples' );
                    $this->buildingDeck->pickCardsForLocation(3, 'grandtemples', 'age3');
                    $this->buildingDeck->shuffle( 'age3' );
                    // Return the remaining Guilds to the box.
                    $this->buildingDeck->moveAllCardsInLocation( 'grandtemples', 'box');
                }
                else {
                    // Then randomly draw 3 Guild cards and add them to the Age 3 deck.
                    $this->buildingDeck->createCards(Material::get()->buildings->filterByAge(4)->getDeckCards(), 'guilds' );
                    $this->buildingDeck->shuffle( 'guilds' );
                    $this->buildingDeck->pickCardsForLocation(3, 'guilds', 'age3');
                    $this->buildingDeck->shuffle( 'age3' );
                    // Return the remaining Guilds to the box.
                    $this->buildingDeck->moveAllCardsInLocation( 'guilds', 'box');
                }
            }
            if ($agora) {
                // Add 5 (Age 1 & 2) or 3 (Age 3) senator cards.
                $this->buildingDeck->pickCardsForLocation($age < 3 ? 5 : 3, "senators", "age{$age}");
                $this->buildingDeck->shuffle("age{$age}");
            }
        }

        // Make the card ids match our material ids. This saves us a lot of headaches tracking both card ids and building ids.
        self::DbQuery( "UPDATE building SET card_id = card_type_arg + 1000, card_type_arg = 0" );
        self::DbQuery( "UPDATE building SET card_id = card_id - 1000" );

        // Set up progress tokens
        $this->progressTokenDeck->createCards(Material::get()->progressTokens->getDeckCards(1, 10));
        if ($agora) $this->progressTokenDeck->createCards(Material::get()->progressTokens->getDeckCards(11, 12));
        if ($pantheon) $this->progressTokenDeck->createCards(Material::get()->progressTokens->getDeckCards(13, 14));
        $this->matchDeckIds("progress_token");

        $selectProgressTokens = 5;
        if ($agora && $this->getGameStateValue(self::OPTION_AGORA_PROGRESS_TOKENS)) {
            $selectProgressTokens -= 2;
            $this->progressTokenDeck->moveCards([11, 12], 'board');
        }
        if ($pantheon && $this->getGameStateValue(self::OPTION_PANTHEON_PROGRESS_TOKENS)) {
            $selectProgressTokens -= 2;
            $this->progressTokenDeck->moveCards([13, 14], 'board');
        }
        $this->progressTokenDeck->shuffle('deck');
        $this->progressTokenDeck->pickCardsForLocation($selectProgressTokens, 'deck', 'board');
        $this->progressTokenDeck->shuffle('board'); // Ensures we have defined card_location_arg
        // Return the remaining Progress Tokens to the box.
        $this->progressTokenDeck->moveAllCardsInLocation('deck', 'box');


        if ($pantheon) {
            $this->divinityDeck->createCards(Material::get()->divinities->getDeckCards());
            // Make the card ids match our material ids. This saves us a lot of headaches tracking both card ids and progress token ids.
            self::DbQuery( "UPDATE divinity SET card_id = card_type_arg + 1000, card_type_arg = 0" );
            self::DbQuery( "UPDATE divinity SET card_id = card_id - 1000" );

            for($type = 1; $type <= 5; $type++) {
                $cards = $this->divinityDeck->getCardsOfType($type);
                $location = "mythology{$type}";
                foreach($cards as $card) {
                    $this->divinityDeck->insertCardOnExtremePosition($card['id'], $location, true);
                }
                $this->divinityDeck->shuffle($location);
            }

            $this->mythologyTokenDeck->createCards(Material::get()->mythologyTokens->getDeckCards());
            $this->mythologyTokenDeck->shuffle('deck');
            $this->mythologyTokenDeck->pickCardsForLocation(5, 'deck', 'board');
            $this->mythologyTokenDeck->shuffle('board'); // Ensures we have defined card_location_arg
            // Return the remaining Mythology Tokens to the box.
            $this->mythologyTokenDeck->moveAllCardsInLocation('deck', 'box');
            $this->matchDeckIds("mythology_token");
            
            $this->offeringTokenDeck->createCards(Material::get()->offeringTokens->getDeckCards());
            $this->offeringTokenDeck->moveAllCardsInLocation('deck', 'board');
            $this->offeringTokenDeck->shuffle('board'); // Ensures we have defined card_location_arg
            // Return the remaining Offering Tokens to the box.
            $this->matchDeckIds("offering_token");
        }
        
        if ($agora) {
            // Set up decrees
            $this->decreeDeck->createCards(Material::get()->decrees->getDeckCards());
            $this->matchDeckIds("decree");
            if (0) {
                // TODO: remove these lines which defines a static set of decrees.
                $i = 0;
                $this->decreeDeck->insertCard(13, 'board', $i++);
                $this->decreeDeck->insertCard(16, 'board', $i++);
                $this->decreeDeck->insertCard(9, 'board', $i++);
                $this->decreeDeck->insertCard(15, 'board', $i++);
                $this->decreeDeck->insertCard(1, 'board', $i++);
                $this->decreeDeck->insertCard(14, 'board', $i++);
            }
            else {
                $this->decreeDeck->shuffle('deck');
                $this->decreeDeck->pickCardsForLocation(6, 'deck', 'board');
                $this->decreeDeck->shuffle('board'); // Ensures we have defined card_location_arg
            }

            // Return the remaining Decrees to the box.
            $this->decreeDeck->moveAllCardsInLocation('deck', 'box');
            // Add decrees stack position suffix
            self::DbQuery( "UPDATE decree SET card_location_arg = CONCAT(card_location_arg + 1, '1') WHERE card_location = 'board'" );
            // Set decrees visibility
            self::DbQuery( "UPDATE decree SET card_type_arg = 1 WHERE card_location_arg IN (11, 31 , 51)" ); // Reveal 3 out of 6 decrees.

            // Set up conspiracies
            $this->conspiracyDeck->createCards(Material::get()->conspiracies->getDeckCards());
            $this->conspiracyDeck->shuffle('deck');
            $this->matchDeckIds("conspiracy");

            // TODO: remove these lines which put certain conspiracies on top.
            if (0) {
                $this->conspiracyDeck->insertCardOnExtremePosition(11, 'deck', true);
                $this->conspiracyDeck->insertCardOnExtremePosition(14, 'deck', true);
                $this->conspiracyDeck->insertCardOnExtremePosition(13, 'deck', true);
                $this->conspiracyDeck->insertCardOnExtremePosition(7, 'deck', true);
            }

            // Set up Influence cubes
            $this->influenceCubeDeck->createCards([['type' => Player::me()->id, 'nbr' => 12, 'type_arg' => 0]], Player::me()->id, 0);
            $this->influenceCubeDeck->createCards([['type' => Player::opponent()->id, 'nbr' => 12, 'type_arg' => 0]], Player::opponent()->id, 0);
        }

        // TODO: Remove, this will be done by player interaction:
//        $playerIds = array_keys($players);
//        $this->wonderDeck->moveAllCardsInLocation('selection1', 'player' . $playerIds[0]);
//        $this->wonderDeck->shuffle('player' . $playerIds[0]); // Ensures we have defined card_location_arg
//        $this->wonderDeck->moveAllCardsInLocation('selection2', 'player' . $playerIds[1]);
//        $this->wonderDeck->shuffle('player' . $playerIds[1]); // Ensures we have defined card_location_arg

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();
    }

    private function matchDeckIds($deckName) {
        self::DbQuery( "UPDATE {$deckName} SET card_id = card_type_arg + 1000, card_type_arg = 0" );
        self::DbQuery( "UPDATE {$deckName} SET card_id = card_id - 1000" );
    }

}