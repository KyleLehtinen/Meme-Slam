$(function(){
	
	var matchID;
	var userID = $('.game-field').attr('data');
	var betRating = $('.game-field').attr('rating');
	var playerAcceptMatch = false;
	var joinIntervalPollTime = 2000;

	var data = {
		userID: userID,
		betRating: betRating
	};

	

	function stopPolling(event) {
		clearInterval(event);
	}

	function promptAccept() {
		console.log('MATCH FOUND! PLEASE ACCEPT!');
		//update view
		// $('.game-search').attr('hidden','');
		// $('.prompt-accept').removeAttr('hidden');
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
		        		promptAccept();
		        		stopPolling(pollOpponentJoin);
		        	}
		        }
			});
		}, joinIntervalPollTime);
	}

	// $('.accept-match-btn').on('click', function(e, data){
	// 	e.preventDefault();

	// 	$.ajax({
	// 		url: '/api/search_for_match',
	// 		method: 'post',
	// 		beforeSend: function (xhr) {
	//         	var token = $('meta[name="csrf_token"]').attr('content');
	            
	//             if (token) {
	//                 return xhr.setRequestHeader('X-XSRF-TOKEN', token);
	//             }
	//         }
	// 	})
	// });

	$('.search-for-match').on('click', function(e, matchID){

		e.preventDefault();
		
		//change gamefield view to reflect match is being searched for
		$('.pre-search').attr('hidden','');
		$('.game-search').removeAttr('hidden');

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
			success: function(e, matchID) {
				matchID = e.matchID;
				console.log("Match found???: " + e.matchFound);
				console.log("Match ID: " + matchID);
				console.log("Player Roll: " + e.playerRoll);
				
				if(e.matchFound) {
					promptAccept();
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