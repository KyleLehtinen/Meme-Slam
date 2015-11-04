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
		3: $('.match-results')
	};

	var matchID;
	var lastState;
	var playerAcceptedMatch;
	var ggDetail;
	var userID = $('.game-field').attr('userid');
	var betRating = $('.game-field').attr('rating');
	var joinIntervalPollTime = 2000;
	var acceptTimerValue = 5000;
	var recheckPlayersAcceptTimerValue = 3000;

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
	$('body').on('slammerMiniGame', function(e, matchID) {
		console.log("Executing Slammer Game...");
		slammerMiniGame(matchID);
	});

	$('body').on('updateMatchState', function(e, matchID, stateData){
		state = GameState.match_state;
		updateMatchState(matchID, state, stateData);
	});

	$('body').on('showRoundResults', function(e) {
		showRoundResults();
	});

//////////FUNCTION CALLS/////////////
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

				console.log("It's your turn...");
				//trigger process event
				$('body').trigger('processTurn', matchID, userID);
			} else {//Opponent turn - Poll for player's turn
				console.log("It's opponent's turn... Checking for state change...");

				opponentViewUpdate();
			}
		}
	}

	//Used to govern opponent player's view.
	function opponentViewUpdate() {
		console.log("Entered OpponentViewUpdate Function...");
		var newState = GameState.match_state

		if (newState == '0' || newState == '1') {//display stack
			//first time through
			if(lastState != '0' && lastState != '1'){
				console.log("Opponent View changed to Stack...");

				lastState = newState;
				$('.display-stack > h3').text("It's " + GameState.opponent.name + "'s turn! Waiting for opponent...");

				//load in stack of mogs for view
				var stackCount = GameState.player.playing_mogs.length +
									GameState.opponent.playing_mogs.length;
				renderStack(stackCount);
				switchGameView(0);
				console.log("Stack rendered and displayed.");
			} 

			lastState = newState;
			//call function that polls for the match being the player's turn
			console.log("Opponent View: Triggering pollForUpdate...");
			$('body').trigger('pollForUpdate', matchID);

		} else if (newState == '2') {//display explosion and round result
			console.log("Opponent View is showing results view...");
			
			//first time through
			if(lastState != '2') {
				console.log("Opponent has not seen results, setting flag and setting to show...");
				lastState = newState;
				switchGameView(2);
				console.log("Calling showRoundResults for opponent...");
				$('body').trigger('showRoundResults');
			} else {//second time through
				console.log("Opponent has seen results, switching to show stack and resetting outcome viewed, polling for round results viewed...");

				$('.display-stack > h3').text("Opponent's turn is now over...");
				//load in stack of mogs for view
				var stackCount = GameState.player.playing_mogs.length +
									GameState.opponent.playing_mogs.length;
				renderStack(stackCount);
				switchGameView(0);
				console.log("Stack rendered and displayed.");

				//Wait for match state to change
				$('body').trigger('pollForUpdate', matchID);
			}
		} else {//display match result
			if(lastState != '3') {
				lastState = newState;
				console.log("Opponent View is set to show end of match...");
				getGameOverDetail(matchID);
				setTimeout(function(){
					$('.players-won-mogs-count').text(ggDetail.p1_name + '\'s Mogs: ' + ggDetail.match_detail.p1_mog_count +
														'	' + ggDetail.p2_name + '\'s Mogs: ' + ggDetail.match_detail.p2_mog_count);
					// if(parseInt(ggDetail.p1_mog_count,10) > parseInt(ggDetail.p2_mog_count,10)) {
					// 	$('.winner').text(ggDetail.p1_name + ' wins!');
					// } else if (parseInt(ggDetail.p1_mog_count,10) < parseInt(ggDetail.p2_mog_count,10)){
					// 	$('.winner').text(ggDetail.p2_name + ' wins!');
					// } else {
					// 	$('.winner').text("Tie.");
					// }
					
					switchGameView(3);	
				},2000,ggDetail);
			}
		} 	
	}

	function getGameOverDetail(matchID) {
		$.ajax({
			url: '/api/get_game_over_detail/' + matchID,
		}).done(function(response){
			console.log("Game Over Details received!");
			ggDetail = response;
		}).fail(function (request, status, error) {
		    console.log("Error getting game over detail...");
		    console.dir(error);
		});
	}

	//main method that processes the turn by calling other events and advancing the game state
	function processTurn(matchID, userID) {
		var newState = GameState.match_state;

		if(newState == '0') { //Display stack
			
			if(lastState != '0') {
				lastState = newState;

				console.log("Game State is 0, displaying stack...");
				$('.display-stack > h3').text("It's your turn!");
				//load in stack of mogs for view
				var stackCount = GameState.player.playing_mogs.length +
									GameState.opponent.playing_mogs.length;
				renderStack(stackCount);
				switchGameView(0);

				setTimeout(function(){
					$('body').trigger('updateMatchState', matchID);
				}, 2000, matchID);
			}	
		} else if (newState == '1') { //Slammer mini game 
			
			if(lastState != '1') {
				lastState = newState;
				$('.slammer-container h3').text('Get Ready...');
				console.log("Game State is 1, displaying slammer...");
				switchGameView(1);
			
				setTimeout(function(){
					$('body').trigger('slammerMiniGame',[matchID]);
				},3000);

			} 
		} else if (newState == '2') { //slammer explosion and result animation 
			console.log("Game State is 2, showing results...");

			if(lastState != '2') {//first time player seeing results
				lastState = newState;
				console.log("First time through seeing match results...");
				switchGameView(2);
				$('body').trigger('showRoundResults');
			} else { //catch case where other player has not yet seen the update
				console.log("Other user has not seen view, resetting outcomeViewed and polling for players viewed round results...");
				//show stack and display text
				$('.display-stack > h3').text("Your turn is now over...");
				//load in stack of mogs for view
				var stackCount = GameState.player.playing_mogs.length +
									GameState.opponent.playing_mogs.length;
				renderStack(stackCount);
				switchGameView(0);
				setTimeout(function(){
					$('body').trigger('pollForUpdate', matchID);
				},3000, matchID);
			}
		} else { //end of game
			if(lastState != '3') {
				lastState = newState;
				console.log("Opponent View is set to show end of match...");
				getGameOverDetail(matchID);
				setTimeout(function(){
					$('.players-won-mogs-count').text(ggDetail.p1_name + '\'s Mogs: ' + ggDetail.match_detail.p1_mog_count +
														'|' + ggDetail.p2_name + '\'s Mogs: ' + ggDetail.match_detail.p2_mog_count);
					if(parseInt(ggDetail.p1_mog_count,10) > parseInt(ggDetail.p2_mog_count,10)) {
						$('.winner').text(ggDetail.p1_name + ' wins!');
					} else if (parseInt(ggDetail.p1_mog_count,10) < parseInt(ggDetail.p2_mog_count,10)){
						$('.winner').text(ggDetail.p2_name + ' wins!');
					} else {
						$('.winner').text("Tie.");
					}
					
					switchGameView(3);	
				},2000,ggDetail);
			}
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
			updateMogs();
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
					if(rating >= 900) {
						$('.user-mogs').append('<div id=\"' + id + '\" class=\"mog-img legendary\" title=\"' + title + '\" name=\"' + name 
										+ '\" rating=\"' + rating + '\"style=\"' + style + '\" data=\"' + data + '\"></div>');
					} else if (rating < 900 && rating >= 600) {
						$('.user-mogs').append('<div id=\"' + id + '\" class=\"mog-img rare\" title=\"' + title + '\" name=\"' + name 
										+ '\" rating=\"' + rating + '\"style=\"' + style + '\" data=\"' + data + '\"></div>');
					} else {
						$('.user-mogs').append('<div id=\"' + id + '\" class=\"mog-img\" title=\"' + title + '\" name=\"' + name 
										+ '\" rating=\"' + rating + '\"style=\"' + style + '\" data=\"' + data + '\"></div>');
					}
					
				} else {
					if(rating >= 900) {
						$('.won-mogs').append('<div id=\"' + id + '\" class=\"mog-img legendary\" title=\"' + title + '\" name=\"' + name 
										+ '\" rating=\"' + rating + '\"style=\"' + style + '\" data=\"' + data + '\"></div>');
					} else if (rating < 900 && rating >= 600) {
						$('.won-mogs').append('<div id=\"' + id + '\" class=\"mog-img rare\" title=\"' + title + '\" name=\"' + name 
										+ '\" rating=\"' + rating + '\"style=\"' + style + '\" data=\"' + data + '\"></div>');
					} else {
						$('.won-mogs').append('<div id=\"' + id + '\" class=\"mog-img\" title=\"' + title + '\" name=\"' + name 
										+ '\" rating=\"' + rating + '\"style=\"' + style + '\" data=\"' + data + '\"></div>');
					}
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
		$('.mog-stack').children().remove();
		for(var i = 0; i < count; i++) {
			$('.mog-stack').append(
				'<div class=\"stack-item\" style=\"left: '+Math.floor((Math.random() * 5) + 1)+'px; bottom: '+(10 + (i + Math.floor((Math.random() * 3) + 1)))+'px;\"></div>'
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

	//function that plays explosion animation and round results
	function showRoundResults(){
		$('.slammer-explosion').removeAttr('hidden');
		$('.explosion').snabbt({
			scale: [1.8,1.8],
			duration: 100,
			complete: function(){
				console.log("animation done");
				$('.explosion-container').fadeOut(400, function(){
					//reset container properties for next round
					$('.explosion-container').attr('hidden','');
					$('.explosion-container').removeAttr('style');
					$('.explosion').removeAttr('style');
					
					//call render results
					$('.mog-drop-container').removeAttr('hidden');
					renderRoundResultMogs();
				})
				
			}
		})

		var mogDropContainer = $('.mog-drop-container');

		//hold view to show fallen mogs then switch to results view
		setTimeout(function(){
			console.log("Drop animation done... Moving on to Won Mogs...");
			showRoundsWonMogs(mogDropContainer);
		},5000,mogDropContainer);
	}

	//show won mogs
	function showRoundsWonMogs(prevContainer){
		
		//clear results view before rev
		$('.slammer-game-results').children().remove();

		if(GameState.round_result_mogs.length < 1) {
			$('.slammer-game-results').append('<h3>No Mogs won this round</h3>');
		} else {
			$('.slammer-game-results').append('<h3>Mogs Won this round:</h3>');

			for(var i = 0; i < GameState.round_result_mogs.length; i++) {
				$('.slammer-game-results').append('<div class=\"mog-img\" style=\"background-image: url(\'/images/mogs/'+GameState.round_result_mogs[i].id+'\');\"></div>');
			}
		}

		//fade out old view and reset properties
		prevContainer.fadeOut(400,function(){
			//clean up mog drop screen
			console.log("Cleaning up last view...");
			prevContainer.attr('hidden','');
			prevContainer.removeAttr('style');
			prevContainer.children().remove();

			//update results view and reveal
			console.log("Revealing results...");
			$('.slammer-game-results').removeAttr('hidden');
			setTimeout(function(){
				$('.slammer-game-results').attr('hidden','');
				console.log("Calling updateMatchState since user should have seen view now...");
				$('body').trigger('updateMatchState',[matchID,userID]);
			},5000,matchID, userID);
		});
	}

	//function used to render mog drop during round results
	function renderRoundResultMogs(count) {
		var maxHeight = 400;
		var maxWidth = $('.round-results').parent().width() - 155;
		var countNotFlipped = (GameState.player.playing_mogs.length + GameState.opponent.playing_mogs.length);
		var countFlipped = GameState.round_result_mogs.length;

		console.log("Getting flipped mogs...");
		console.log("Rendering flipped: " + countFlipped);
		for(var i = 0; i < countFlipped; i++) {
			$('.mog-drop-container').append('<div class=\"stack-itm-contr\" style=\"left: '+Math.floor((Math.random() * (maxWidth)))+'px; top: -400px\"><div class=\"drop-item '+i+'\" style=\"background-image: url(\'/images/mogs/'+GameState.round_result_mogs[i].id+'\')\"></div></div>');
		
			$('.drop-item.'+i).snabbt({
				delay: 400,
				duration: 400,
				position: [0,(Math.floor((Math.random() * (maxHeight)) + 750)),0],
				complete: function(){
					console.log("Mog " + i + " added!");
				}
			}).snabbt('attention',{
				rotation: [0,0,Math.PI/2],
				springConstant: 1.9,
				springDeceleration: 0.9
			});
		} 

		console.log("Rendering non-flipped: " + countNotFlipped);
		for(var i = 0; i < countNotFlipped; i++) {
			$('.mog-drop-container').append('<div class=\"stack-itm-contr\" style=\"left: '+Math.floor((Math.random() * (maxWidth)))+'px; top: -400px\"><div class=\"drop-item '+i+'\" style=\"background-image: url(\'/images/memeslam.png\')\"></div></div>');	
			
			$('.drop-item.'+i).snabbt({
				delay: 400,
				duration: 400,
				position: [0,(Math.floor((Math.random() * (maxHeight)) + 750)),0],
				complete: function(){
					console.log("Mog " + i + " added!");
				}
			}).snabbt('attention',{
				rotation: [0,0,Math.PI/2],
				springConstant: 1.9,
				springDeceleration: 0.9
			});
		}

		console.log("Mogs Dropping...");
	}

	function slammerMiniGame(matchID) {
	
		console.log("Slammer Mini Game started... ");

		var entered = false;
		var failed = false;
		var passed = false;
		var resultMessage;
		var slamTime;

		$('.slammer').remove();

		$('.slammer-container').append('<div class=\"slammer\" style=\"transform: rotate('+Math.floor((Math.random() * 360))+'deg)\" hidden><div class=\"upper\"></div><div class=\"gates\"><div class=\"enter\"></div><div class=\"exit\"></div></div><div class=\"lower\"></div></div>');

		$('.slammer-container h3').attr('hidden','');
		
		$('.slammer').removeAttr('hidden');

		$('.slammer').snabbt({
			rotation: [0,0,2*Math.PI],
			duration: 5000,
			complete: function(){
				processRound(matchID);
			}
		});

		$('body').on('mouseenter', '.upper, .lower', function() {
			failed = true;
			$('.slammer').fadeOut(400);
		});

		$('body').on('mouseenter', '.enter', function(e) {
			entered = true;
		});

		$('body').on('mouseenter', '.exit', function(e) {
			if(entered){
				slamTime = slammerTime
				$('.slammer').fadeOut(300,function(){
					passed = true;
				});
			} else {
				failed = true;
				$('.slammer').fadeOut(300);
			}
		});

		//process outcome of minigame here...
		function processRound(matchID) {

			var result = 0;

			if(failed){
				console.log("Player failed Slammer Game.");
				$('.slammer-container h3').text("Too Bad!").removeAttr('hidden');
			} else {
				getResultMessage();
				$('.slammer-container h3').text(resultMessage).removeAttr('hidden');
			}
			$('.slammer').attr('hidden','');
			$('.slammer').removeAttr('style');

			console.log("Player Passed Slammer Game! Result " + slamTime);
			result = Math.floor(slamTime);

			setTimeout(function(){
				$('body').trigger('updateMatchState', [matchID, result]);
			}, 2000, matchID, result);
		}

		//check calculation and get result message
		function getResultMessage() {
			if(slamTime <= 200) {
				resultMessage = "PERFECT!";
			} else if (slamTime > 200 && slamTime <= 900) {
				resultMessage = "MARVELOUS!";
			} else if (slamTime > 900 && slamTime <= 1100) {
				resultMessage = "Great!";
			} else if (slamTime > 1100 && slamTime <= 1600) {
				resultMessage = "Good.";
			} else if (slamTime > 1600 && slamTime <= 2500) {
				resultMessage = "Fair...";
			} else {
				resultMessage = "Poor...";
			}
		}
	}
});