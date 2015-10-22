$(function(){

	//controls mog selection from bet pod and inventory for hero area
	$('body').on('click','.mog-img', function(e){
		var title = $(this).attr('title');
		var style = $(this).attr('style');
		var url = $(this).attr('data');

		$('.selected-mog').attr('style',style);
		$('.selected-mog-name').text(title.substring(0,(title.indexOf('|') - 1)));
		$('.selected-mog-url > a').attr('href',url);
	});
});