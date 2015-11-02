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
		3: $('.slammer-game-results'),
		4: $('.match-results')
	};

	var matchID;
	var lastState;
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

    //
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

			        		// getGameState(matchID);

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
							        		
							        		// getGameState(matchID);
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

//////////////GAME EVENTS///////////////
	$('body').on('gameLoop', function(e, matchID){
		gameLoop(matchID);
	});

	//poll for turn
	$('body').on('pollForUpdate', function(e, matchID){
		pollForUpdate(matchID);
	});

	//fire process turn function
	$('body').on('processTurn', function(e, matchID, userID) {
		processTurn(matchID, userID);
	});

	//updates game view and calls slammer mini game
	$('body').on('slammerMiniGame', function(e, matchID, userID) {
		slammerMiniGame(matchID, userID);
	});

//////////FUNCTION CALLS/////////////
	function slammerMiniGame(matchID, userID) {
		
		console.log("Slammer Mini Game started...");

		var entered = false;
		var failed = false;

		$('.slammer-container h3').attr('hidden','');
		$('.slammer').removeAttr('hidden');

		$('.slammer').snabbt({
			rotation: [0,0,2*Math.PI],
			duration: 5000
		});

		$('body').on('mouseenter', '.upper, .lower', function() {
			failed = true;
			$('.slammer').fadeOut(400,function(){
				
				$(this).attr('hidden','');
			});
			processRound();
		});

		$('body').on('mouseenter', '.enter', function(e) {
			entered = true;
		});

		$('body').on('mouseenter', '.exit', function(e) {
			if(entered){
				
				var resultMessage;

				getResultMessage();

				$('.slammer').fadeOut(300,function(){
					$(this).attr('hidden','');
				});
				$('.slammer-container h3').text(resultMessage).removeAttr('hidden');
				processRound();
			} else {
				failed = true;
				$('.slammer').fadeOut(300,function(){
					$(this).attr('hidden','');
				});
				processRound();
			}
		});

		//process outcome of minigame here...
		function processRound() {
			if(failed){
				console.log(resultMessage + " You failed the slammer game!");
			} else {
				console.log(resultMessage);
			}
		}

		//check calculation and get result message
		function getResultMessage() {
			if(slammerTime <= 200) {
				resultMessage = "PERFECT!";
			} else if (slammerTime > 200 && slammerTime <= 600) {
				resultMessage = "MARVELOUS!";
			} else if (slammerTime > 600 && slammerTime <= 1100) {
				resultMessage = "Great!";
			} else if (slammerTime > 1100 && slammerTime <= 1600) {
				resultMessage = "Good.";
			} else if (slammerTime > 1600 && slammerTime <= 2500) {
				resultMessage = "Fair...";
			} else if (slammerTime > 2500 && slammerTime <= 5000) {
				resultMessage = "Poor...";
			} else {
				resultMessage = "Too Bad!";
			}
		}
	}

	//Routes logic based on GameState, retrieves GameState if client is not updated, 
	//triggers turn processing or polling depending on GameState
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
				//trigger process event
				$('body').trigger('processTurn', matchID, userID);
			} else {//Opponent turn - Poll for player's turn
				console.log("It's opponent's turn... Checking for state change...");

				if(lastState != GameState.match_state) {
					opponentViewUpdate();
				}
				//call function that polls for the match being the player's turn
				$('body').trigger('pollForUpdate', matchID);
			}
		}
	}

	//Used to govern opponent player's view.
	function opponentViewUpdate() {

		var newState = GameState.match_state

		if(typeof lastState === 'undefined') { //check if lastState is initialized
			lastState = GameState.match_state;
		} 

		if ((lastState != '0' || lastState != '1') && (newState == '0' || newState == '1')) {//display stack
			
			$('.display-stack > h3').text("It's currently " + GameState.opponent.name + "'s turn! Waiting for opponent...");

			//load in stack of mogs for view
			var stackCount = GameState.player.playing_mogs.length +
								GameState.opponent.playing_mogs.length;
			renderStack(stackCount);
			switchGameView(0);

		} else if (lastState != '2' && newState == '2') {//display explosion
			switchGameView(2);
		} else if (lastState != '3' && newState == '3') {//display mini game result
			switchGameView(3);
		} else { //display end game results
			switchGameView(4);
		}

		lastState = newState;
	}

	//main method that processes the turn by calling other events and advancing the game state
	function processTurn(matchID, userID) {

		if(GameState.match_state == '0') { //Mog Stack displayed to Slammer Mini Game 
			
			$('.display-stack > h3').text("It's your turn! Get ready...");
			//load in stack of mogs for view
			var stackCount = GameState.player.playing_mogs.length +
								GameState.opponent.playing_mogs.length;
			renderStack(stackCount);
			switchGameView(0);
			setTimeout(function(){
				updateMatchState(matchID, 0);
			}, 2000, matchID);
			
		} else if (GameState.match_state == '1') { //Slammer mini game to slammer explosion animation
			
			switchGameView(1);
			setTimeout(function(){
				$('body').trigger('slammerMiniGame');
			},3000);
	
		} else if (GameState.match_state == '2') { //slammer explosion animation to results processing
			
		} else if (GameState.match_state == '3') { //Results processing to results output
			
		} else if (GameState.match_state == '4') { //checks for game over: if game over shows results, else cycles back
			
		} else {

		}
	}

	//function used to push update to server
	function updateMatchState(matchID, state, stateData) {
		
		stateData = stateData || 0;

		var data = {
			matchID: matchID,
			currentState: state,
			stateData: stateData
		}

		console.log("Pushing update to the server...");
		
		$.ajax({
			url: '/api/update_match_state',
			method: 'post',
			data: data
		}).done(function(){
			console.log("Server Updated!");
			getGameState(matchID);
		}).fail(function (request, status, error) {
		    console.log("Error sending update to server...");
		    console.dir(error);
		});
	}

	//support function to terminate polling
	function stopPolling(event) {
		clearInterval(event);
	}

	//support function to check if an object is empty
	function isEmpty(obj) {
	    return Object.keys(obj).length === 0;
	}

	//polls server on script load to see if player is in an active match
	function checkForActiveMatch(userID) {
		if(/^(.*)\/meme_slam\//.test(window.location.href)){
			$.ajax({
				url: '/api/check_for_active_match/' + userID
			}).done(function(match) {
				if(!(match == '0' || match == 'undefined')) {
					console.log("You are already in an active match! Match:" + match);
					matchID = match;
					$('body').trigger('gameLoop',matchID);
				} else {
					console.log("You are NOT in an existing match.");
					switchGameView('preSearch');
				}
			}).fail(function (request, status, error) {
			    console.log("Error while retrieving current game state...");
			    console.dir(error);
			});
		}
	}

	//polls server for gamestate and updates local GameState, fired after each progression of the game
	function getGameState(matchID) {
		console.log("Retreiving Game State from server...");
		$.ajax({
			url: '/api/get_game_state/' + matchID + '/' + userID
		}).done(function(update){
			console.log("Update Received!");
			updateGameState(update);
			$('body').trigger('gameLoop',matchID);
		}).fail(function (request, status, error) {
		    console.log("Error while retrieving current game state...");
		    console.dir(error);
		});
	}

	//updates the GameState global Variable
	function updateGameState(objGameState) {
		GameState = objGameState[0];
		console.log("Game State Updated...");
	}

	//updates the displayed playing and captured mogs according to gamestate
	function updateMogs() {

		//get users playing and captured mogs from GameState
		var playingMogs = GameState.player.playing_mogs;
		var capturedMogs = GameState.player.captured_mogs;

		//array containing 
		var updatedMogs = [playingMogs,capturedMogs];

		//remove old list of mogs
		$('.user-mogs').children().remove();
		$('.won-mogs').children().remove();	

		//loops through playing and captured mogs to build html and append it
		for(var j = 0; j < updatedMogs.length; j++) {
			for(var i = 0; i < updatedMogs[j].length; i++) {
				var id = updatedMogs[j][i].active_id;
				var title = updatedMogs[j][i].name + ' | ' + updatedMogs[j][i].rating;
				var name = updatedMogs[j][i].name;
				var rating = updatedMogs[j][i].rating;
				var style = 'background-image: url(\/images\/mogs\/' + updatedMogs[j][i].id + ')';
				var data = updatedMogs[j][i].src_url;

				if(j == 0) {
					$('.user-mogs').append('<div id=\"' + id + '\" class=\"mog-img\" title=\"' + title + '\" name=\"' + name 
										+ '\" rating=\"' + rating + '\"style=\"' + style + '\" data=\"' + data + '\"></div>');
				} else {
					$('.won-mogs').append('<div id=\"' + id + '\" class=\"mog-img\" title=\"' + title + '\" name=\"' + name 
										+ '\" rating=\"' + rating + '\"style=\"' + style + '\" data=\"' + data + '\"></div>');
				}
				
			}
		}
	}

	//toggles hidden attribute of html to switch view
	function switchGameView(select) {

		$('.game-field').children().each(function(){
			$(this).removeAttr('hidden');
			$(this).attr('hidden', '');
		});

		gameViews[select].removeAttr('hidden');
	}

	function renderStack(count) {
		for(var i = 0; i < count; i++) {
			$('.mog-stack').append(
				'<div class=\"stack-item\" style=\"left: '+Math.floor((Math.random() * 5) + 1)+'px; bottom: '+(10 + (i + 1))+'px;\"></div>'
			);
		}
	}

	//api call following successful match to call server to initialize the match
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

	//ajax call to check if a player joined the match
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

	//drops match if matchmaking fails
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

	//polls server till it is the players turn
	function pollForUpdate(matchID) {
		
		var lastUpdate = GameState.last_update;

		var checkServer = setInterval(function(){
			$.ajax({
				url: '/api/check_for_update/'+matchID+'/'+lastUpdate,
				method: 'get',
				dataType: 'json',
				success: function(newUpdate) {
					if(newUpdate) {
						stopPolling(checkServer);
						console.log("Game State has changed! Getting new Game State");
						getGameState(matchID);
					} else {
						console.log("Game State has not changed... rechecking.");
					}
				},
				error: function(request, status, error){
					console.dir(error);
				}
			});
		},3000, matchID, userID);
	}
});