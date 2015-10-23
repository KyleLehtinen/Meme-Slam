$(function(){

	//used to recalculate pet pod rating
	var calcBetPodRating = function() {
		var newBetPodRating = 0;

		if ($('.bet-pod-mogs').has('.mog-img')) {
			$('.bet-pod-mogs .mog-img').each(function(){
				newBetPodRating += Number($(this).attr('title').substring($(this).attr('title').indexOf('|') + 1));
			});
			$('.bet-pod-container > h3').text('Bet Rating: ' + newBetPodRating);
		} else {
			$('.bet-pod-container > h3').text('Bet Rating: 0');
		}
		
	}

	calcBetPodRating();

	


	// mogs.each(function(){
	// 	console.log($(this).attr('title').substring($(this).attr('title').indexOf('|') + 1));
	// });



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
		receive: calcBetPodRating
	});

	$(".bet-pod-mogs").sortable({
		connectWith: ".mog-inv-container",
		receive: calcBetPodRating
	});
});