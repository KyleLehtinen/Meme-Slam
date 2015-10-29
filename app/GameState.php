<?php

namespace App;
use DB;

class GameState
{
	public $player;
	public $opponent;
	public $game_state;
	public $active_player;

	public function __construct($player, $opponent, $game_state, $active_player) {
		$this->player = $player;
		$this->opponenet = $opponent;
		$this->game_state = $game_state;
		$this->active_player = $active_player;
	}

	public static function getGameState($match_id, $player_id) {

		//get match
		$match = Matches::find($match_id);
		// $match = Matches::getMatch($match_id);

		//get player's opponent id
		$opponent_id = Matches::getOpponentID($match_id, $player_id);

		//get the player's state
		$player = Player::getPlayerState($match_id, $player_id);

		//get the opponent player's state
		$opponent = Player::getPlayerState($match_id, $opponent_id);

		//get the game's current state
		$match_state = $match->match_state;

		//get the current active player
		$active_player = $match->active_player_id;
		// echo "creating game_state object....";
		$game_state = new GameState($player, $opponent, $match_state, $active_player);
		// echo "done! Returning...";

		return $game_state;

	}
}