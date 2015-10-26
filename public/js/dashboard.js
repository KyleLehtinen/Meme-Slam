$(function(){

	var requiredBetMogs = 20;

	//Support Jquery UI events - Prevents adding more than requiredBetMogs
	function sortableReceiveLogic(event,ui) {
		if ($('.bet-pod-mogs').children().length > requiredBetMogs) {
            $(ui.sender).sortable('cancel');
        }

        updateMogBetStatus();
	}

	//Support Jquery UI events - AJAX to update which users mogs are in bet container
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

	//Supports Jquery UI Events - used to recalculate pet pod rating
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

	//Returns count of mogs in bet container
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

	//Events for adding/removing mogs from Bet Pod
	$(".inv-mogs").sortable({
		connectWith: ".bet-pod-mogs",
		receive: sortableReceiveLogic,
		stop: updateBetPodRating
		
	});

	$(".bet-pod-mogs").sortable({
		connectWith: ".inv-mogs",
		receive: sortableReceiveLogic,
		stop: updateBetPodRating
	});
});