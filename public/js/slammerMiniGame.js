
function slammerMiniGame(matchID, userID) {
	
	console.log("Slammer Mini Game started...");

	var entered = false;
	var failed = false;
	var resultMessage;

	$('.slammer-container h3').attr('hidden','');
	$('.slammer').removeAttr('hidden');

	$('.slammer').snabbt({
		rotation: [0,0,2*Math.PI],
		duration: 5000
	});

	$('body').on('mouseenter', '.upper, .lower', function() {
		failed = true;
		$('.slammer').fadeOut(400,function(){
			$(this).attr('hidden','');
			$('.slammer-container h3').text("Too Bad!").removeAttr('hidden');
			processRound(resultMessage);
		});
	});

	$('body').on('mouseenter', '.enter', function(e) {
		entered = true;
	});

	$('body').on('mouseenter', '.exit', function(e) {
		if(entered){
			$('.slammer').fadeOut(300,function(){
				$(this).attr('hidden','');
				getResultMessage();
				$('.slammer-container h3').text(resultMessage).removeAttr('hidden');
				processRound(resultMessage);
			});
		} else {
			failed = true;
			$('.slammer').fadeOut(300,function(){
				$(this).attr('hidden','');
				$('.slammer-container h3').text("Too Bad!").removeAttr('hidden');
				processRound(resultMessage);
			});
		}
	});

	//process outcome of minigame here...
	function processRound() {
		if(failed){
			console.log("Player failed Slammer Game.");
		} else {
			console.log("Player Passed Slammer Game! Result " + slammerTime);
		}
	}

	//check calculation and get result message
	function getResultMessage() {
		if(slammerTime <= 200) {
			resultMessage = "PERFECT!";
		} else if (slammerTime > 200 && slammerTime <= 900) {
			resultMessage = "MARVELOUS!";
		} else if (slammerTime > 900 && slammerTime <= 1100) {
			resultMessage = "Great!";
		} else if (slammerTime > 1100 && slammerTime <= 1600) {
			resultMessage = "Good.";
		} else if (slammerTime > 1600 && slammerTime <= 2500) {
			resultMessage = "Fair...";
		} else {
			resultMessage = "Poor...";
		}
	}
}
