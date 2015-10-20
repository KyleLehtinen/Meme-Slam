@extends('layout')

@section('header')
	@extends('header')
@endsection

@section('main')
<body>
	
	<div class="mog-viewport-container">
		<div class="user-stats">
			<h3>Games Played: 21</h3>
			<h3>Games Won: 12</h3>
			<h3>Collectoin Rating: 256</h3>
		</div>
		<div class="mog-viewport">
			<div class="mog-detail-view">
				<div class="selected-mog" style="background-image: url('database/img/mogs/1-1')"></div>
			</div>
			<div class="mog-details-container">
				<div class="selected-mog-name">
					Slender Man
				</div>
				<div class="selected-mog-rating">
					Rating: 7
				</div>
				<div class="mog-url">
					<a href="http://knowyourmeme.com/memes/slender-man">View at KnowYourMeme</a>
				</div>
			</div>
		</div>
	</div>
	<div class="inventory-container">
		<div class="inv-search-container">
			<input class="search-field" type="text" name="smog" placeholder="Search inventory">
			<label>
				<input type="checkbox" name="toggleDupes">Hide Duplicates
			</label>
		</div>
		<div class="inv-main-container">
			<aside class="bet-pod-container">
				<h3>Bet Pod Rating: 14.1</h3>
				<div class="bet-pod">
					<div class="bet-pod-mogs">
						{{"bet mogs here"}}
					</div>	
				</div>
			</aside>
			<div class="mog-inventory">
				<h3>Your Mog Inventory: 312</h3>
				<div class="mog-inv-container">
					{{"inv mogs here"}}
				</div>
			</div>
		</div>
	</div>
	<script>
	$(function(){
		$('body').on('click','.mog-img', function(e){
			var title = $(this).attr('title');
			var style = $(this).attr('style');
			var url = $(this).attr('data');

			$('.selected-mog').attr('style',style);
			$('.selected-mog-name').text(title.substring(0,(title.indexOf('|') - 1)));
			$('.mog-url > a').attr('href',url);
		});
	});
	</script>
	<!-- <div class="mog-viewport">
			<div class="mog-detail-view">
				<div class="selected-mog" style="background-image: url('database/img/mogs/1-1')"></div>
			</div>
			<div class="mog-details-container">
				<div class="selected-mog-name">
					Slender Man
				</div>
				<div class="selected-mog-rating">
					Rating: 7
				</div>
				<div class="mog-url">
					<a href="http://knowyourmeme.com/memes/slender-man">View at KnowYourMeme</a>
				</div>
			</div>
		</div> -->
@endsection