$(function(){

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
		connectWith: ".bet-pod-mogs"
		// ,
		// drop: function(){
		// 		var id = $(this).attr('id');
		// 		var url = ''

		// 		$.ajax({
		// 			type: 'POST',
		// 			url: 'home/' + id + '/toggle_bet'
		// 		});
		// 	}
	});

	$(".bet-pod-mogs").sortable({
		connectWith: ".mog-inv-container"
	});

	// $( ".mog-inv-container, .bet-pod-mogs" ).sortable({
	// 	accept: '.mog-img',
	// 	helper: "clone", 
	// 	opacity: 0.5,
	// 	cursor: "crosshair",
	// 	connectWith: ".connectedSortable",
	// 	drop: function(event,ui) {
	// 		$(this).
	// 	}
	// });

	// $( "#sort1,#sort2" ).disableSelection();

	// $('.mog-inv-container, .bet-pod-mogs').droppable({
	// 	accept: '.mog-img',
	// 	drop: function(event,ui) {
	// 		$(this).detach().appendTo('.bet-pod-mogs');
	// 	}
	// }).sortable({
	// 	connectWith: '.connectedSortable'
	// }).disableSelection();

	// $('.mog-img').draggable({
	// 	containment: '.mog-main-container',
	// 	cursor: 'pointer',
	// 	connectToSortable: '.mog-inv-container, .bet-pod-mogs',
	// });
});