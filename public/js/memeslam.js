$(function() {
	
	var GameState = {};

	var gameViews = {
		preSearch: $('.pre-search'),
		gameSearch: $('.game-search'),
		promptAccept: $('.prompt-accept'),
		attemptJoin: $('.attempt-join'),
		joinSuccess: $('.join-successful'),
		dispMiniGame: $('.slammer-game')
	};


	var matchID;
	var playerAcceptedMatch;
	var p1Turn;
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

	function stopPolling(event) {
		clearInterval(event);
	}

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

			        		switchGameView('joinSuccess');
			        		getGameState(data.matchID);
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

							        		switchGameView('joinSuccess');
							        		getGameState(data.matchID);

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

	function getGameState(matchID) {
		$.ajax({
			url: '/api/get_game_state/' + matchID + '/' + userID
		}).done(function(update){
			updateGameState(update);
		}).fail(function (request, status, error) {
		        console.log("Error while retrieving current game state...");
		        console.dir(error);
		});
	}

	function updateGameState(objGameState) {
		GameState = objGameState[0];

		updateGameView();
	}

	function updateGameView() {
		
		var refreshedList = '';

		//get users playing mogs from GameState
		var playingMogs = GameState.player.playing_mogs;

		//remove old list of mogs
		$('.user-mogs').children().remove();

		for(var i = 0; i < playingMogs.length; i++) {
			var id = playingMogs[i].active_id;
			var title = playingMogs[i].name + ' | ' + playingMogs[i].rating;
			var name = playingMogs[i].name;
			var rating = playingMogs[i].rating;
			var style = 'background-image: url(\/images\/mogs\/' + playingMogs[i].active_id + ')';
			var data = playingMogs[i].src_url;

			$('.user-mogs').append('<div id=\"' + id + '\" class=\"mog-img\" title=\"' + title + '\" name=\"' + name 
									+ '\" rating=\"' + rating + '\"style=\"' + style + '\" data=\"' + data + '\"></div>');

			// refreshedList += htmlEntities('<div id="'+$(this).active_id+'"
			// 					class="mog-img"
			// 					title="'+$(this).name+' | '+$(this).rating+'"
			// 					name="'+$(this).name+'"
			// 					rating="'$(this).rating+'"
			// 					style="background-image: url(/images/mogs/'+$(this).active_id+'"
			// 					data="'+$(this).src_url+'"
			// 					</div>');
		}

		$('.user-mogs').append(refreshedList);

	}

	function htmlEntities(str) {
	    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
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

	function getOpponentDetail(matchID, requestor) {
		$.ajax({
			url: '/api/get_match_players/' + matchID + '/' + requestor ,
			method: 'get',
			dataType: 'json',
			success: function(e) {
				$('.opponent-player').text(e.opponent);
			},
			error: function(request, status, error){
				console.dir(error);
			}
		});
	}

	function getFirstTurn(matchID, playerRoll) {
		$.ajax({
			url: '/api/get_match_turn/' + matchID,
			method: 'get',
			dataType: 'json',
			success: function(p1Turn) {
				if(playerRoll == 1) {
					if(p1Turn){
						$('.first-player').text('You');	
					} else {
						$('.first-player').text('Opponent');
					}
				} else {
					if(p1Turn) {
						$('.first-player').text('Opponent');
					} else {
						$('.first-player').text('You');	
					}
				}
			},
			error: function(request, status, error){
				console.dir(error);
			}
		});
	}

	function getCurrentTurn(matchID) {
		$.ajax({
			url: '/api/get_match_turn/' + matchID,
			method: 'get',
			dataType: 'json',
			success: function(e) {
				p1Turn = e;
			},
			error: function(request, status, error){
				console.dir(error);
			}
		});

		return p1Turn;
	}
});