$(function(){
	
	var gameState = {
		player: {
			id: null,
			name: null,
			playingPogs: null,
			capturedPogs: null,
		},
		opponent: null
	}

	getGameState();

	function getGameState() {
		userID = $('.game-field').attr('userID');
		name = $('.game-field').attr('name');
		betRating = $('.game-field').attr('rating');
		playingMogs = getMogsFromPage($('.user-mogs').children());
		capturedMogs = getMogsFromPage($('.won-mogs').children());


		playingMogs
	}

	function getMogsFromPage(arrMogs) {

		var mogs = [];

		arrMogs.each(function(){
			var mog = {
				id : $(this).attr('id'),
				title : $(this).attr('name'),
				rating : $(this).attr('rating'),
				imgUrl : $(this).attr('style')
			}
			mogs.push(mog);
		});

		return mogs;
	}
});