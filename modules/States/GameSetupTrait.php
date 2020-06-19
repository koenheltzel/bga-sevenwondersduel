<?php


namespace SWD\States;


use SWD\Material;

trait GameSetupTrait
{

    public function stGameSetup() {
        $players = $this->loadPlayersBasicInfos();

        // Set up two 4-wonders selection pools, rest of the wonders go back to the box.
        $this->wonderDeck->shuffle('deck');
        $this->wonderDeck->createCards(Material::get()->wonders->getDeckCards());
        $this->wonderDeck->pickCardsForLocation(4, 'deck', 'selection1');
        $this->wonderDeck->shuffle('selection1'); // Ensures we have defined card_location_arg
        $this->wonderDeck->pickCardsForLocation(4, 'deck', 'selection2');
        $this->wonderDeck->shuffle('selection2'); // Ensures we have defined card_location_arg
        $this->wonderDeck->moveAllCardsInLocation('deck', 'box');

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

        // Set up progress tokens
        $this->progressTokenDeck->shuffle('deck');
        $this->progressTokenDeck->createCards(Material::get()->progressTokens->getDeckCards());
        $this->progressTokenDeck->pickCardsForLocation(5, 'deck', 'board');
        $this->progressTokenDeck->shuffle('board'); // Ensures we have defined card_location_arg
        // Return the remaining Progress Tokens to the box.
        $this->progressTokenDeck->moveAllCardsInLocation('deck', 'box');

        // TODO: Remove, this will be done by player interaction:
        $playerIds = array_keys($players);
        $this->wonderDeck->moveAllCardsInLocation('selection1', 'player' . $playerIds[0]);
        $this->wonderDeck->shuffle('player' . $playerIds[0]); // Ensures we have defined card_location_arg
        $this->wonderDeck->moveAllCardsInLocation('selection2', 'player' . $playerIds[1]);
        $this->wonderDeck->shuffle('player' . $playerIds[1]); // Ensures we have defined card_location_arg

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();
    }

}