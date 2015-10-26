@extends('layout')

@section('header')
	@extends('header')
@endsection

@section('main')
	<div class="game-field" id="{{$user->id}}">
		<div class="pre-search" hidden>
			<h3>Your Bet Pod Rating is {{$bet_rating}}</h3>
			<h3>You will be matched against other players with a BP rating between {{$bet_rating - 300}} and {{$bet_rating + 300}}</h3>
			<button class="search-for-match">Search for Match</button>
		</div>	
		<div class="game-search">
			<h3>Searching for match...</h3>
			<div class="searching-icn"></div>
		</div>

	</div>
	<div class="game-details-container">
		<div class="game-details">
			<div class="user-mogs-container">
				<h3>Betting Mogs</h3>
				<div class="user-mogs">
					@foreach($bet_mogs as $mog)
						<div id="{{$mog->active_id}}"
							 class="mog-img" 
							 title="{{{$mog->name}}} | {{$mog->rating}}" 
							 style="background-image: url(/images/mogs/{{$mog->id}}" 
							 data="{{$mog->src_url}}">
						</div>
					@endforeach
				</div>
			</div>
			<div class="won-mogs-container">
				<h3>Won Mogs</h3>
				<div class="won-mogs">
					
				</div>
			</div>
		</div>
	</div>
@endsection