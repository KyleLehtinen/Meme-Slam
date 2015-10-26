$(function(){
	$('.search-for-match').on('click', function(){
		var userID = $('.game-field').attr('data');
		var betRating = $('.game-field').attr('rating');


		var data = {
			userID: userID,
			betRating: betRating
		}

		$.ajax({
			url: '/api/update_bet_status',
			beforeSend: function (xhr) {
	        	var token = $('meta[name="csrf_token"]').attr('content');
	            
	            if (token) {
	                return xhr.setRequestHeader('X-XSRF-TOKEN', token);
	            }
	        },
			data: data,
			method: 'post',
			error: function(){
				console.log("An error occurred attempting to start the match!");
			}
		});
	});
});