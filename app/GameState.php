<?php

namespace App;
use DB;

class GameState
{
	public $player;
	public $opponent;
	public $match_state;
	public $active_player;
	public $round_result_mogs;
	public $last_update;

	public function __construct($player, $opponent, $match_state, $active_player, $round_result_mogs, $last_update) {
		$this->player = $player;
		$this->opponent = $opponent;
		$this->match_state = $match_state;
		$this->active_player = $active_player;
		$this->round_result_mogs = $round_result_mogs;
		$this->last_update = $last_update;
	}

	public static function getGameState($match_id, $player_id) {

		//get match
		$match = Matches::find($match_id);

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

		//get mogs for results animation screen
		$round_result_mogs = PlayField::getResultsAnimationMogs($match_id);

		//get last update timestamp
		$last_update = $match->updated_at;
		$last_update = substr($last_update, 0, 19);

		//create new game_state object
		$game_state = new GameState($player, $opponent, $match_state, $active_player, $round_result_mogs, $last_update);

		return $game_state;
	}
}