<?php


namespace SWD\States;


use SWD\Material;
use SWD\Player;

trait GameSetupTrait
{

    public function enterStateGameSetup() {
        $agora = $this->getGameStateValue(self::OPTION_AGORA);
        $pantheon = $this->getGameStateValue(self::OPTION_PANTHEON);

        // Set up two 4-wonders selection pools, rest of the wonders go back to the box.
        $this->wonderDeck->createCards(Material::get()->wonders->getDeckCards(1, 12));
        if ($agora) {
            if ($this->getGameStateValue(self::OPTION_AGORA_WONDERS)) {
                // Guarantee the inclusion of the 2 Agora wonders by shuffling the 12 base game wonders and moving 6 of them to the box.
                $this->wonderDeck->shuffle('deck');
                $this->wonderDeck->pickCardsForLocation(6, 'deck', 'box');
            }
            $this->wonderDeck->createCards(Material::get()->wonders->getDeckCards(13, 14));
        }
        $this->wonderDeck->shuffle('deck');
        $this->wonderDeck->pickCardsForLocation(4, 'deck', 'selection1');
        $this->wonderDeck->shuffle('selection1'); // Ensures we have defined card_location_arg
        $this->wonderDeck->pickCardsForLocation(4, 'deck', 'selection2');
        $this->wonderDeck->shuffle('selection2'); // Ensures we have defined card_location_arg
        $this->wonderDeck->moveAllCardsInLocation('deck', 'box');
        // Make the card ids match our material ids. This saves us a lot of headaches tracking both card ids and wonder ids.
        self::DbQuery( "UPDATE wonder SET card_id = card_type_arg + 1000, card_type_arg = 0" );
        self::DbQuery( "UPDATE wonder SET card_id = card_id - 1000" );

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
        if ($agora) {
            if ($this->getGameStateValue(self::OPTION_AGORA_PROGRESS_TOKENS)) {
                // Guarantee the inclusion of the 2 Agora progress tokens by shuffling the 10 base game tokens and moving 7 of them to the box.
                $this->progressTokenDeck->shuffle('deck');
                $this->progressTokenDeck->pickCardsForLocation(7, 'deck', 'box');
            }
            $this->progressTokenDeck->createCards(Material::get()->progressTokens->getDeckCards(11, 12));
        }
        $this->progressTokenDeck->shuffle('deck');
        $this->progressTokenDeck->pickCardsForLocation(5, 'deck', 'board');
        $this->progressTokenDeck->shuffle('board'); // Ensures we have defined card_location_arg
        // Return the remaining Progress Tokens to the box.
        $this->progressTokenDeck->moveAllCardsInLocation('deck', 'box');
        // Make the card ids match our material ids. This saves us a lot of headaches tracking both card ids and progress token ids.
        self::DbQuery( "UPDATE progress_token SET card_id = card_type_arg + 1000, card_type_arg = 0" );
        self::DbQuery( "UPDATE progress_token SET card_id = card_id - 1000" );


        if ($agora) {
            // Set up decrees
            $this->decreeDeck->createCards(Material::get()->decrees->getDeckCards());
            // Make the card ids match our material ids. This saves us a lot of headaches tracking both card ids and decree ids.
            self::DbQuery( "UPDATE decree SET card_id = card_type_arg + 1000, card_type_arg = 0" );
            self::DbQuery( "UPDATE decree SET card_id = card_id - 1000" );
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
            // Make the card ids match our material ids. This saves us a lot of headaches tracking both card ids and decree ids.
            self::DbQuery( "UPDATE conspiracy SET card_id = card_type_arg + 1000, card_type_arg = 0" );
            self::DbQuery( "UPDATE conspiracy SET card_id = card_id - 1000" );

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

}