$(function(){
	$('.mog-inv-container, .bet-pod-mogs').sortable({
		connectWith: '.connectedSortable'
	}).disableSelection();
	//controls mog selection from bet pod and inventory for hero area
	$('body').on('click','.mog-img', function(e){
		var title_rating = $(this).attr('title');
		var style = $(this).attr('style');
		var url = $(this).attr('data');

		$('.selected-mog').attr('style',style);
		$('.selected-mog-name').text(title_rating.substring(0,(title_rating.indexOf('|') - 1)));
		$('.selected-mog-rating').text(title_rating.substring(title_rating.indexOf('|') + 1));
		$('.selected-mog-url > a').attr('href',url);
	});

	//logic for adding/removing mogs from Bet Pod
	$('.mog-img').draggable({
		containment: '.mog-main-container',
		cursor: 'pointer',
		connectToSortable: '.mog-inv-container, .bet-pod-mogs',
	});

	// // Getter
	// var appendTo = $( ".mog-img" ).draggable( "option", "appendTo" );
 
	// // Setter
	// $( ".mog-img" ).draggable( "option", "appendTo", ".bet-pod-mogs" );

	// // $( '.bet-pod-mogs' ).droppable({
	// //   accept: '.mog-img'
	// // });
});