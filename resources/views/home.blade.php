@extends('layout')

@section('header')
	@extends('header')
@endsection

@section('main')
<body>
		<div class="mog-viewport-container">
		<div class="user-stats">
			<h3>Games Played: {{$user->game_count}}</h3>
			<h3>Games Won: {{$user->total_wins}}</h3>
			<h3>Collection Rating: {{$user->collection_rating}}</h3>
		</div>
		<div class="mog-viewport">
			<div class="mog-detail-view">
				<div class="selected-mog" style="background-image: url('database/img/mogs/1-1')"></div>
			</div>
			<div class="mog-details-container">
				<div class="selected-mog-name">
					
				</div>
				<div class="selected-mog-rating">
					
				</div>
				<div class="mog-url">
					<a href="">Learn More at KnowYourMeme</a>
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
					@foreach($mogs as $mog)
						<div class="mog-img" 
							 title="{{{$mog->name}}} | {{$mog->rating}}" 
							 style="background-image: url(/images/mogs/{{$mog->id}}" 
							 data="{{$mog->src_url}}">
						</div>
					@endforeach
				</div>
			</div>
		</div>
	</div>
	<script>
	$(function(){

		//controls mog selection from bet pod and inventory for hero area
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
@endsection