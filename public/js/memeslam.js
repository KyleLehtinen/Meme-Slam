$(function(){
	
	//get needed variables to send in ajax
	var userID = $('.game-field').attr('data');
	var betRating = $('.game-field').attr('rating');

	var data = {
		userID: userID,
		betRating: betRating
	};

	function promptAccept(data) {
		
		//update view
		$('.game-search').attr('hidden','');
		$('.prompt-accept').removeAttr('hidden');

		//timer call likely here...

		$('.accept-match-btn').on('click', function(e, data){
			e.preventDefault();

			$.ajax({
				url: '/api/search_for_match',
				method: 'post',
				beforeSend: function (xhr) {
		        	var token = $('meta[name="csrf_token"]').attr('content');
		            
		            if (token) {
		                return xhr.setRequestHeader('X-XSRF-TOKEN', token);
		            }
		        },
			})
		});
	}

	$('.search-for-match').on('click', function(e){

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
			success: function(e) {
				console.log("Match found???: " + e.matchFound);
				console.log("Match ID: " + e.matchID)
				
				if(e.result) {
					//prompt for player accept
				}
			},
			error: function(){
				console.log("An error occurred attempting to start the match!");
			}
		});
	});
});