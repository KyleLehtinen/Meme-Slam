@extends('layout')

@section('header')
	@extends('header')
@endsection

@section('main')
	<div class="game-field" userID="{{$user->id}}" name="{{$user->name}}" rating="{{$bet_rating}}">
		<div class="pre-search" hidden>
			<h3>Your Bet Pod Rating is {{$bet_rating}}</h3>
			<h3>You will be matched against other players with a BP rating between {{$bet_rating - 300}} and {{$bet_rating + 300}}</h3>
			<button class="search-for-match">Search for Match</button>
		</div>	
		<div class="game-search" hidden>
			<h3>Searching for match...</h3>
			<div class="searching-icn"></div>
		</div>
		<div class="prompt-accept" hidden>
			<h3>A match has been found! Click 'Accept Match' below to join...<span class="accept-count-down">10</span></h3>
			<button class="accept-match-btn">Accept Match</button>
		</div>
		<div class="attempt-join" hidden>
			<h3>You accepted! Waiting for other player...</h3>
		</div>
		<div class="join-successful" hidden>
			<h3>Both players have accepted. You are playing against <span class="opponent-player"></span>!</h3>
			<h3>Randomly selecting who goes first... Done!</h3>
			<h3><span class="first-player"></span> will go first...</h3>
		</div>
		<div class="display-stack" hidden>
			<h3></h3>
			<div class="mog-stack">
				
			</div>
		</div>
		<div class="slammer-game" hidden>
			<div class="slammer-container">
				<h3>Get Ready...</h3>
				<div class="slammer" hidden>
					<div class="upper"></div>
					<div class="gates">
						<div class="enter"></div>
						<div class="exit"></div>
					</div>
					<div class="lower"></div>
				</div>
			</div>
		</div>
		<div class="slammer-explosion round-results" hidden>
			<div class="explosion-container">
				<div class="explosion"></div>
			</div>
			<div class="mog-drop-container" hidden></div>
			<div class="slammer-game-results" hidden>
				<h3>Mogs Won this round</h3>
			</div>
		</div>
		<div class="match-results" hidden>
			<h3>Game Over!</h3>
			<h3 class="winner"></h3>
			<h3 class="players-won-mogs-count"></h3>
			<h3>New Mogs have been added to your dashboard. Thanks for playing.</h3>
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
							 name="{{$mog->name}}"
							 rating="{{$mog->rating}}"
							 style="background-image: url(/images/mogs/{{$mog->id}}" 
							 data="{{$mog->src_url}}">
						</div>
					@endforeach
				</div>
			</div>
			<div class="won-mogs-container">
				<h3>Won Mogs</h3>
				<div class="won-mogs">
					@foreach($captured_mogs as $mog)
						<div id="{{$mog->active_id}}"
							 class="mog-img" 
							 title="{{{$mog->name}}} | {{$mog->rating}}" 
							 name="{{$mog->name}}"
							 rating="{{$mog->rating}}"
							 style="background-image: url(/images/mogs/{{$mog->id}}" 
							 data="{{$mog->src_url}}">
						</div>
					@endforeach
				</div>
			</div>
		</div>
	</div>
@endsection