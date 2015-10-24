$(function(){

	var requiredBetMogs = 20;

	function updateMogBetStatus() {
		var betMogs = new Array();
		var token = $('meta[name="csrf_token"]').attr('content');

		$('.bet-pod-mogs').children().each(function(){
			betMogs.push(Number($(this).attr('id')));
		});

		var data = {
			mogs: betMogs,
			ownerID: $('.user-stats').attr('data')
		};

		$.ajax({
			// _token: token,
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
				console.log("an error occurred sending the bet status update!");
			}
		});
	}

	//used to recalculate pet pod rating
	function updateBetPodRating() {
		
		var newBetPodRating = 0;

		if ($('.bet-pod-mogs').has('.mog-img')) {
			$('.bet-pod-mogs .mog-img').each(function(){
				var thisRating = Number($(this).attr('title').substring($(this).attr('title').indexOf('|') + 1));
				var thisID = $(this).attr('id');

				newBetPodRating += thisRating;
			});
			$('.bet-pod-container > h4').text('Bet Rating: ' + newBetPodRating 
				+ ' ' + getBetMogCount() + '/' + requiredBetMogs);
		} else {
			$('.bet-pod-container > h4').text('Bet Rating: 0');
		}
	}

	function getBetMogCount() {

		var betMogCount = 0;

		if ($('.bet-pod-mogs').has('.mog-img')) {
			$('.bet-pod-mogs .mog-img').each(function(){
				betMogCount++;
			});
		}

		return betMogCount;
	}

	//controls mog selection from bet pod and inventory for hero area
	$('body').on('click','.mog-img', function(e){
		var title_rating = $(this).attr('title');
		var style = $(this).attr('style');
		var url = $(this).attr('data');

		$('.selected-mog').attr('style',style);
		$('.selected-mog-name').text('Mog Name: ' + title_rating.substring(0,(title_rating.indexOf('|') - 1)));
		$('.selected-mog-rating').text('Rating: ' + title_rating.substring(title_rating.indexOf('|') + 1));
		$('.selected-mog-url > a').attr('href',url);
	});

	//logic for adding/removing mogs from Bet Pod
	$(".mog-inv-container").sortable({
		connectWith: ".bet-pod-mogs",
		receive: updateMogBetStatus,
		stop: updateBetPodRating
		
	});

	// $('.mog-inv-container').on('sortupdate', function(event, ui) {console.log("ajax call!");});
	// $('.bet-pod-mogs').on('sortupdate', function(event, ui) {console.log("ajax call!");});

	$(".bet-pod-mogs").sortable({
		connectWith: ".mog-inv-container",
		receive: updateMogBetStatus,
		stop: updateBetPodRating
	});
});