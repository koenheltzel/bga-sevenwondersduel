<?php


namespace SWD\States;


use SWD\Material;

trait GameSetupTrait
{

    public function enterStateGameSetup() {
        // Set up two 4-wonders selection pools, rest of the wonders go back to the box.
        $this->wonderDeck->createCards(Material::get()->wonders->getDeckCards());
        $this->wonderDeck->shuffle('deck');
        $this->wonderDeck->pickCardsForLocation(4, 'deck', 'selection1');
        $this->wonderDeck->shuffle('selection1'); // Ensures we have defined card_location_arg
        $this->wonderDeck->pickCardsForLocation(4, 'deck', 'selection2');
        $this->wonderDeck->shuffle('selection2'); // Ensures we have defined card_location_arg
        $this->wonderDeck->moveAllCardsInLocation('deck', 'box');
        // Make the card ids match our material ids. This saves us a lot of headaches tracking both card ids and wonder ids.
        self::DbQuery( "UPDATE wonder SET card_id = card_type_arg + 1000, card_type_arg = 0" );
        self::DbQuery( "UPDATE wonder SET card_id = card_id - 1000" );

        // Set up card piles for the 3 ages.
        // "Return to the box, without looking at them, 3 cards from each Age deck.
        for ($age = 1; $age <= 3; $age++) {
            $this->buildingDeck->createCards(Material::get()->buildings->filterByAge($age)->getDeckCards(), "age{$age}" );
            $this->buildingDeck->shuffle("age{$age}");
            $this->buildingDeck->pickCardsForLocation(3, "age{$age}", 'box');
        }
        // Then randomly draw 3 Guild cards and add them to the Age 3 deck.
        $this->buildingDeck->createCards(Material::get()->buildings->filterByAge(4)->getDeckCards(), 'guilds' );
        $this->buildingDeck->shuffle( 'guilds' );
        $this->buildingDeck->pickCardsForLocation(3, 'guilds', 'age3');
        $this->buildingDeck->shuffle( 'age3' );
        // Return the remaining Guilds to the box.
        $this->buildingDeck->moveAllCardsInLocation( 'guilds', 'box');
        // Make the card ids match our material ids. This saves us a lot of headaches tracking both card ids and building ids.
        self::DbQuery( "UPDATE building SET card_id = card_type_arg + 1000, card_type_arg = 0" );
        self::DbQuery( "UPDATE building SET card_id = card_id - 1000" );

        // Set up progress tokens
        $this->progressTokenDeck->createCards(Material::get()->progressTokens->getDeckCards());
        $this->progressTokenDeck->shuffle('deck');
        $this->progressTokenDeck->pickCardsForLocation(5, 'deck', 'board');
        $this->progressTokenDeck->shuffle('board'); // Ensures we have defined card_location_arg
        $this->progressTokenDeck->pickCardsForLocation(3, 'deck', 'wonder6'); // Preselect 3 progress tokens for Wonder The Great Library. This can only happen once during the game so it doesn't matter if we do it now.
        $this->progressTokenDeck->shuffle('wonder6'); // Ensures we have defined card_location_arg
        // Return the remaining Progress Tokens to the box.
        $this->progressTokenDeck->moveAllCardsInLocation('deck', 'box');
        // Make the card ids match our material ids. This saves us a lot of headaches tracking both card ids and progress token ids.
        self::DbQuery( "UPDATE progress_token SET card_id = card_type_arg + 1000, card_type_arg = 0" );
        self::DbQuery( "UPDATE progress_token SET card_id = card_id - 1000" );

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