$(function(){
	
	var matchID;
	var playerAcceptedMatch;
	var userID = $('.game-field').attr('data');
	var betRating = $('.game-field').attr('rating');
	var joinIntervalPollTime = 2000;
	var acceptTimerValue = 10000;
	var recheckPlayersAcceptTimerValue = 5000;

	function updateGameView(select) {
		var gameViews = {
			preSearch: $('.pre-search'),
			gameSearch: $('.game-search'),
			promptAccept: $('.prompt-accept'),
			attemptJoin: $('.attempt-join'),
			displayFirstPlayer: $('.display-first-player')
		};

		$('.game-field').children().each(function(){
			$(this).removeAttr('hidden');
			$(this).attr('hidden', '');
		});

		gameViews[select].removeAttr('hidden');
	}

	var data = {
		userID: userID,
		betRating: betRating
	};	

	function stopPolling(event) {
		clearInterval(event);
	}

	function promptAccept(matchID, playerRoll, acceptTimerValue) {
		var playerAcceptedMatch = false;
		console.log('MATCH FOUND! PLEASE ACCEPT!');

		//update view
		updateGameView('promptAccept');
		// $('.game-search').attr('hidden','');
		// $('.prompt-accept').removeAttr('hidden');

		var data = {
			matchID: matchID,
			playerRoll: playerRoll
		};

		//handle for accept button
		$('body').on('click', '.accept-match-btn', function(e){
			e.preventDefault();

			playerAcceptedMatch = true;

			updateGameView('attemptJoin');
			// $('.prompt-accept').attr('hidden','');
			// $('.attempt-join').removeAttr('hidden');
		});

		setTimeout(function() {
			console.log('Player ' + playerRoll + ' accept: ' + playerAcceptedMatch);
			if (playerAcceptedMatch) {
				$.ajax({
					url: '/api/player_accepts_match',
					method: 'post',
					beforeSend: function (xhr) {
			        	var token = $('meta[name="csrf_token"]').attr('content');
			            
			            if (token) {
			                return xhr.setRequestHeader('X-XSRF-TOKEN', token);
			            }
			        },
			        dataType: 'json',
			        data: data,
			        success: function(e) {
			        	playersMatched = e.playersMatched;

			        	if(playersMatched) {
			        		console.log("BOTH PLAYERS ACCEPT! ON TO THE COIN FLIP!")
			        		updateGameView('displayFirstPlayer');
			        	} else {
			        		setTimeout(function() {
			        			$.ajax({
									url: '/api/check_players_accepted/' + data.matchID,
									method: 'get',
									beforeSend: function (xhr) {
							        	var token = $('meta[name="csrf_token"]').attr('content');
							            
							            if (token) {
							                return xhr.setRequestHeader('X-XSRF-TOKEN', token);
							            }
							        },
							        dataType: 'json',
							        success: function(e) {
							        	

							        	var playersAcceptedMatch = e.playersAcceptedMatch;

							        	if(playersAcceptedMatch) {
							        		console.log("BOTH PLAYERS HAVE ACCEPTED THE MATCH! ON TO THE COIN FLIP!");
							        		updateGameView('displayFirstPlayer');
							        	} else {
							        		console.log("THIS IS WHERE WE WOULD DROP THE MATCH AND RESET");

							        		dropMatch(data.matchID);
							        		
							        		//reset view to starting view
							        		updateGameView('preSearch')
							        	}
							        },
							        error: function(xhr, status, error) {
  										var err = eval("(" + xhr.responseText + ")");
									  	alert(err.Message);
									}
	
							    });
			        		}, recheckPlayersAcceptTimerValue, playerAcceptedMatch, data);
			        	}
			        },
			        error: function() {
			        	console.log("An error occurred while trying to post that the player accepted the match!");
			        }

				});
			} else {
				console.log("PLAYER DID NOT ACCEPT THE MATCH!!!");

				updateGameView('preSearch');
			}
		}, acceptTimerValue, playerAcceptedMatch, data);
	}

	function checkPlayerJoin(joinIntervalPollTime,matchID) {
		console.log('Check Player Join Fire')
		var pollOpponentJoin = setInterval(function() {
			$.ajax({
				url: '/api/check_opponent_joined/' + matchID,
				method: 'get',
				beforeSend: function (xhr) {
		        	var token = $('meta[name="csrf_token"]').attr('content');
		            
		            if (token) {
		                return xhr.setRequestHeader('X-XSRF-TOKEN', token);
		            }
		        },
		        dataType: 'json',
		        success: function(e) {
		        	console.log(e.p2Joined);
		        	if(e.p2Joined) {
		        		stopPolling(pollOpponentJoin);
		        		promptAccept(matchID, 1, acceptTimerValue);
		        	}
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
			beforeSend: function (xhr) {
	        	var token = $('meta[name="csrf_token"]').attr('content');
	            
	            if (token) {
	                return xhr.setRequestHeader('X-XSRF-TOKEN', token);
	            }
	        },
	        data: data,
	        success: function(e) {
	        	console.log('Match dropped, resetting view...');
	        }
		});
	}

	$('.search-for-match').on('click', function(e){

		e.preventDefault();
		
		//change gamefield view to reflect match is being searched for
		updateGameView('gameSearch');
		// $('.pre-search').attr('hidden','');
		// $('.game-search').removeAttr('hidden');

		$.ajax({
			url: '/api/search_for_match',
			method: 'post',
			beforeSend: function (xhr) {
	        	var token = $('meta[name="csrf_token"]').attr('content');
	            
	            if (token) {
	                return xhr.setRequestHeader('X-XSRF-TOKEN', token);
	            }
	        },
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
			error: function(){
				console.log("An error occurred attempting to start the match!");
			}
		});
	});
});