$(function(){
	$('.search-for-match').on('click', function(e){

		e.preventDefault();
		
		//change gamefield view to reflect match is being searched for
		$('.pre-search').attr('hidden','');
		$('.game-search').removeAttr('hidden');

		//get needed variables to send in ajax
		var userID = $('.game-field').attr('data');
		var betRating = $('.game-field').attr('rating');

		var data = {
			userID: userID,
			betRating: betRating
		}

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
				console.log("Match found???: " + e.result);
			},
			error: function(){
				console.log("An error occurred attempting to start the match!");
			}
		});
	});
});