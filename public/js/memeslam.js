$(function() {
  
	var GameState = {};

	var gameViews = {
		preSearch: $('.pre-search'),
		gameSearch: $('.game-search'),
		promptAccept: $('.prompt-accept'),
		attemptJoin: $('.attempt-join'),
		joinSuccess: $('.join-successful'),
		0: $('.display-stack'),
		1: $('.slammer-game'),
		2: $('.slammer-explosion'),
		3: $('.process-slammer-game'),
		4: $('.slammer-game-results'),
		5: $('.match-results')
	};

	var matchID;
	var playerAcceptedMatch;
	var userID = $('.game-field').attr('userid');
	var betRating = $('.game-field').attr('rating');
	var joinIntervalPollTime = 2000;
	var acceptTimerValue = 10000;
	var recheckPlayersAcceptTimerValue = 5000;

	var data = {
		userID: userID,
		betRating: betRating
	};	

	//setup ajax call request headers
	$.ajaxSetup({
        beforeSend: function (xhr) {
        	var token = $('meta[name="csrf_token"]').attr('content');
            
            if (token) {
                return xhr.setRequestHeader('X-XSRF-TOKEN', token);
            }
        }
    });

	//check if user is in an existing match
	checkForActiveMatch(userID);

    //MATCHMAKING EVENTS
    $('.search-for-match').on('click', function(e){

		e.preventDefault();
		
		//change gamefield view to reflect match is being searched for
		switchGameView('gameSearch');

		$.ajax({
			url: '/api/search_for_match',
			method: 'post',
			data: data,
			dataType: 'json',
			success: function(e) {
				matchFound = e.matchFound;
				matchID = e.matchID;
				playerRoll = e.playerRoll;

				console.log("Match found???: " + e.matchFound);
				console.log("Match ID: " + matchID);
				console.log("Player Roll: " + e.playerRoll);
				
				if(matchFound) {
					promptAccept(matchID, playerRoll, acceptTimerValue);
				} else {
					checkPlayerJoin(joinIntervalPollTime,matchID);
				}
			},
			error: function(request, status, error){
				console.log("Error while searchinf for a match...");
				console.dir(error);
			}
		});
	});

	function promptAccept(matchID, playerRoll, acceptTimerValue) {
		var playerAcceptedMatch = false;
		console.log('MATCH FOUND! PLEASE ACCEPT!');

		//update view
		switchGameView('promptAccept');

		var data = {
			matchID: matchID,
			playerRoll: playerRoll
		};

		//handle for accept button
		$('body').on('click', '.accept-match-btn', function(e){
			e.preventDefault();

			playerAcceptedMatch = true;

			switchGameView('attemptJoin');
		});

		setTimeout(function() {
			console.log('Player ' + playerRoll + ' accept: ' + playerAcceptedMatch);
			if (playerAcceptedMatch) {
				$.ajax({
					url: '/api/player_accepts_match',
					method: 'post',
			        dataType: 'json',
			        data: data,
			        success: function(e) {
			        	playersMatched = e.playersMatched;

			        	if(playersMatched) {
			        		console.log("BOTH PLAYERS ACCEPT!")

			        		initializeMatch(data.matchID);
			        		
			        		// getOpponentDetail(data.matchID, data.playerRoll);

			        		// getFirstTurn(data.matchID, data.playerRoll);

			        		// switchGameView('joinSuccess');
			        		// getGameState(data.matchID);

			        		getGameState(matchID);
			        		$('body').trigger('gameLoop', data.matchID);
			        	} else {
			        		console.log("Opponent has not yet accepted...Rechecking...");
			        		setTimeout(function() {
			        			$.ajax({
									url: '/api/check_players_accepted/' + data.matchID,
									method: 'get',
							        dataType: 'json',
							        success: function(e) {
							        	
							        	var playersAcceptedMatch = e.playersAcceptedMatch;

							        	if(playersAcceptedMatch) {
							        		console.log("BOTH PLAYERS HAVE ACCEPTED THE MATCH!");
							        		
							        		initializeMatch(data.matchID);

							        		// getOpponentDetail(data.matchID, data.playerRoll);

							        		// getFirstTurn(data.matchID, data.playerRoll);

							        		// switchGameView('joinSuccess');
							        		
							        		getGameState(matchID);
							        		//call 
							        		$('body').trigger('gameLoop', data.matchID);

							        	} else {
							        		console.log("THIS IS WHERE WE WOULD DROP THE MATCH AND RESET");

							        		dropMatch(data.matchID);
							        		
							        		//reset view to starting view
							        		switchGameView('preSearch')
							        	}
							        },
							        error: function (request, status, error) {
								        console.log("Error while checking if players accepted match...")
								        console.dir(error);
								    }
	
							    });
			        		}, recheckPlayersAcceptTimerValue, playerAcceptedMatch, data);
			        	}
			        },
			        error: function(request, status, error) {
			        	console.log("Error while sending accept of match...")
			        	console.dir(error);
			        }

				});
			} else {
				console.log("PLAYER DID NOT ACCEPT THE MATCH!!!");

				switchGameView('preSearch');
			}
		}, acceptTimerValue, playerAcceptedMatch, data);
	}

	//GAME EVENTS
	$('body').on('gameLoop', function(e, matchID){
		gameLoop(matchID);
	});

	//poll for turn
	$('body').on('checkForPlayersTurn', function(e, matchID, userID){
		checkForPlayersTurn(matchID, userID);
	})

	//FUNCTION CALLS
	function gameLoop(matchID) {

		console.log("Game Loop Triggered for Match " + matchID);
		
		console.log("Checking if GameState initialized...");
		if(isEmpty(GameState)) {

			console.log("GameState is NOT initialized. Initializing...");
			getGameState(matchID);
		} else {
			console.log("GameState is initialized.");
			//check if player's turn
			if(GameState.active_player == userID) {//Player's turn - Run turn logic
				
				//logic to complete turn
				console.log("It's your turn...");



			} else {//Opponent turn - Poll for player's turn
				
				console.log("It's opponent's turn... waiting for it to be your turn.");

				//call function that polls for the match being the player's turn
				$('body').trigger('checkForPlayersTurn', matchID, userID);
			}
		}

	}

	function processTurn(matchID, userID) {
		
	}

	function stopPolling(event) {
		clearInterval(event);
	}

	function isEmpty(obj) {
	    return Object.keys(obj).length === 0;
	}

	function checkForActiveMatch(userID) {
		$.ajax({
			url: '/api/check_for_active_match/' + userID
		}).done(function(match) {
			if(match != "0") {
				console.log("You are already in an active match! Match:" + match);
				matchID = match;
				$('body').trigger('gameLoop',matchID);
			} else {
				console.log("You are NOT in an existing match.");
			}
		}).fail(function (request, status, error) {
		    console.log("Error while retrieving current game state...");
		    console.dir(error);
		});
	}

	function getGameState(matchID) {
		console.log("Retreiving Game State from server...");
		$.ajax({
			url: '/api/get_game_state/' + matchID + '/' + userID
		}).done(function(update){
			console.log("Update Received!");
			updateGameState(update);
			console.log("Game State Updated...");
			$('body').trigger('gameLoop',matchID);
		}).fail(function (request, status, error) {
		    console.log("Error while retrieving current game state...");
		    console.dir(error);
		});
	}

	//updates the GameState global Variable
	function updateGameState(objGameState) {
		GameState = objGameState[0];
	}

	function updateGameView() {
		

		switchGameView()

		//get users playing mogs from GameState
		var playingMogs = GameState.player.playing_mogs;

		//remove old list of mogs
		$('.user-mogs').children().remove();

		for(var i = 0; i < playingMogs.length; i++) {
			var id = playingMogs[i].active_id;
			var title = playingMogs[i].name + ' | ' + playingMogs[i].rating;
			var name = playingMogs[i].name;
			var rating = playingMogs[i].rating;
			var style = 'background-image: url(\/images\/mogs\/' + playingMogs[i].id + ')';
			var data = playingMogs[i].src_url;

			$('.user-mogs').append('<div id=\"' + id + '\" class=\"mog-img\" title=\"' + title + '\" name=\"' + name 
									+ '\" rating=\"' + rating + '\"style=\"' + style + '\" data=\"' + data + '\"></div>');
		}
	}

	function switchGameView(select) {

		$('.game-field').children().each(function(){
			$(this).removeAttr('hidden');
			$(this).attr('hidden', '');
		});

		gameViews[select].removeAttr('hidden');
	}

	function initializeMatch(matchID) {
		$.ajax({
			url: '/api/initialize_match/' + matchID,
			method: 'get',
	        success: function() {
	        	console.log("Match initialized!");	
	        },
	        error: function(request, status, error){
				console.log("Error while checking if opponent joined the match...");
				console.dir(error);
			}
		});
	}

	function checkPlayerJoin(joinIntervalPollTime,matchID) {
		console.log('Check Player Join Fire')
		var pollOpponentJoin = setInterval(function() {
			$.ajax({
				url: '/api/check_opponent_joined/' + matchID,
				method: 'get',
		        dataType: 'json',
		        success: function(e) {
		        	console.log(e.p2Joined);
		        	if(e.p2Joined) {
		        		stopPolling(pollOpponentJoin);
		        		promptAccept(matchID, 1, acceptTimerValue);
		        	}
		        },
		        error: function(request, status, error){
					console.log("Error while checking if opponent joined the match...");
					console.dir(error);
				}
			});
		}, joinIntervalPollTime);
	}

	function dropMatch(matchID) {
		var data = {
			matchID: matchID
		}

		$.ajax({
			url: '/api/drop_match',
			method: 'post',
	        data: data,
	        success: function(e) {
	        	console.log('Match dropped, resetting view...');
	        },
	        error: function(request, status, error){
				console.log("Error while dropping match...");
				console.dir(error);
			}
		});
	}

	

	function checkForPlayersTurn(matchID, userID) {
		var pollForTurn = setInterval(function(){
			$.ajax({
				url: '/api/get_match_turn/'+matchID+'/'+userID,
				method: 'get',
				dataType: 'json',
				success: function(isPlayersTurn) {
					if(isPlayersTurn) {
						stopPolling(pollForTurn);
						console.log("It's now your turn!");
						$('body').trigger('gameLoop', matchID);
					} else {
						console.log("It's not your turn yet... rechecking.");

					}

				},
				error: function(request, status, error){
					console.dir(error);
				}
			});
		},3000, matchID, userID);
	}
});