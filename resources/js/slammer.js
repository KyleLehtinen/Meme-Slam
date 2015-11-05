$(function(){

	function runMiniGame() {
		$('.slammer').snabbt({
			rotation: [0,0,2*Math.PI],
			duration: 5000,
			loop: 10
		});	
	}
	

	// snabbt(('.slammer'),{
	// 	// rotation: [0,0,2*Math.PI],
	// 	duration: 10000,
	// 	loop: 20
	// });

	// var gameActive = true;

	$('.upper').on('mouseenter', function() {
		console.log("Entered upper segment!");

	});

	$('.lower').on('mouseenter', function() {
		console.log("Entered lower segment!");
	});

	$('.enter').on('mouseenter', function(e) {
		// e.stopPropagation();
		console.log("Entered slammer!");
	});

	$('.exit').on('mouseenter', function(e) {
		// e.stopPropagation();
		console.log("Exited slammerf!");
	});
});