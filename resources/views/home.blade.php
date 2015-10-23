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
			<h3>Collection Rating: {{$collectionRating}}</h3>
		</div>
		<div class="mog-viewport">
			<div class="mog-detail-view">
				<div class="selected-mog" style="background-image: url(/images/mogs/{{$mogs[0]->id}}"></div>
			</div>
			<div class="mog-details-container">
				<div class="selected-mog-name">
					{{'Mog Name: ' . $mogs[0]->name}}
				</div>
				<div class="selected-mog-rating">
					{{'Rating: ' . $mogs[0]->rating}}
				</div>
				<div class="selected-mog-url">
					<a href="{{$mogs[0]->src_url}}">Learn More at KnowYourMeme</a>
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
		<div class="mog-main-container">
			<aside class="bet-pod-container">
				<h3>Bet Pod Rating: 14.1</h3>
				<div class="bet-pod">
					<div class="bet-pod-mogs connectedSortable">
						
					</div>	
				</div>
			</aside>
			<div class="mog-inventory">
				<h3>Your Mogs: {{count($mogs)}}</h3>
				<div class="mog-inv-container connectedSortable">
					@foreach($mogs as $mog)
						<div id="{{$mog->active_id}}"
							 class="mog-img" 
							 title="{{{$mog->name}}} | {{$mog->rating}}" 
							 style="background-image: url(/images/mogs/{{$mog->id}}" 
							 data="{{$mog->src_url}}">
						</div>
					@endforeach
				</div>
			</div>
		</div>
	</div>
@endsection