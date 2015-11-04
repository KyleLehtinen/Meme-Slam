$(function(){
	function slammerMiniGame(matchID) {
	
		console.log("Slammer Mini Game started... ");

		var entered = false;
		var failed = false;
		var passed = false;
		var resultMessage;

		$('.slammer-container h3').attr('hidden','');
		
		$('.slammer').removeAttr('hidden');

		$('.slammer').snabbt({
			rotation: [0,0,2*Math.PI],
			duration: 5000,
			complete: function(){
				processRound(matchID);
			}
		});

		$('body').on('mouseenter', '.upper, .lower', function() {
			failed = true;
			$('.slammer').fadeOut(400);
		});

		$('body').on('mouseenter', '.enter', function(e) {
			entered = true;
		});

		$('body').on('mouseenter', '.exit', function(e) {
			if(entered){
				$('.slammer').fadeOut(300,function(){
					passed = true;
				});
			} else {
				failed = true;
				$('.slammer').fadeOut(300);
			}
		});

		//process outcome of minigame here...
		function processRound(matchID) {

			var result = 0;

			if(failed){
				$('.slammer-container h3').text("Too Bad!").removeAttr('hidden');
			} else {
				getResultMessage();
				$('.slammer-container h3').text(resultMessage).removeAttr('hidden');
			}
			$('.slammer').attr('hidden','');
			$('.slammer').removeAttr('style');

			if(failed){
				console.log("Player failed Slammer Game.");
			} else {
				console.log("Player Passed Slammer Game! Result " + slammerTime);
				result = Math.floor(slammerTime);
			}

			setTimeout(function(){
				$('body').trigger('updateMatchState', [matchID, result]);
			}, 2000, matchID, result);
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
})


