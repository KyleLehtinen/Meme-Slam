@extends('layout')

@section('header')
	@extends('header')
@endsection

@section('main')
	@if(!empty($recent_mogs))
	<div class="recent-mogs-backdrop">
		<div class="recent-mogs-container">
			<h2>You have new mogs, check'em out!</h2>
			<div class="recent-mogs">
				@foreach($recent_mogs as $recent_mog)
					<div class="mog-img" style="background-image: url(/images/mogs/{{$recent_mog}}"></div>
				@endforeach
			</div>
			<button>Cool!</button>
		</div>
	</div>
	@endif
	<div class="mog-viewport-container">
		<div class="user-stats" data="{{{$user->id}}}">
			<h3 class="username">User: {{{$user->name}}}</h3>
			<h3>Games Played: {{$user->game_count}}</h3>
			<h3>Games Won: {{$user->total_wins}}</h3>
			<h3>Collection Rating: {{$collection_rating}}</h3>
		</div>
		<div class="mog-viewport">
			<div class="top-collection-rating-container">
				<h5>Top Collections</h5>	
				<div class="collection-rating">
					@foreach($top_collections as $key=>$collection)
						<div class="collection-record">
							<div class="col-name">{{{$collection}}}</div>
							<div class="col-rating">{{{$key}}}</div> 
						</div>
					@endforeach
				</div>
			</div>
			<div class="mog-detail-view">
				@if($mogs[0]->rating >= 900)
					<div class="selected-mog legendary" style="background-image: url(/images/mogs/{{$mogs[0]->id}}"></div>
				@elseif($mogs[0]->rating < 900 && $mogs[0]->rating >= 600)
					<div class="selected-mog rare" style="background-image: url(/images/mogs/{{$mogs[0]->id}}"></div>
				@else
					<div class="selected-mog" style="background-image: url(/images/mogs/{{$mogs[0]->id}}"></div>
				@endif
			</div>
			<div class="mog-details-container">
				<div class="selected-mog-name">
					{{$mogs[0]->name}}
				</div>
				<div class="selected-mog-rating">
					@if($mogs[0]->rating >= 900)
						{{'Rating: ' . $mogs[0]->rating . ' Legendary'}}
					@elseif($mogs[0]->rating < 900 && $mogs[0]->rating >= 600)
						{{'Rating: ' . $mogs[0]->rating . ' Rare'}}
					@else
						{{'Rating: ' . $mogs[0]->rating . ' Common'}}
					@endif
				</div>
				<div class="selected-mog-url">
					<a href="{{$mogs[0]->src_url}}" target=”_blank”>Learn More at KnowYourMeme</a>
				</div>
			</div>
		</div>
	</div>
	<div class="inventory-container">
		<div class="inv-search-container" >
			<input class="search-field" type="text" name="smog" placeholder="Search inventory" hidden>
			<label hidden>
				<input type="checkbox" name="toggleDupes">Hide Duplicates
			</label>
		</div>
		<div class="mog-main-container">
			<aside class="bet-pod-container">
				<div>
					<h4>Bet Rating: {{$bet_rating}} {{$bet_count}}/20</h4>
				</div>
				<div class="bet-pod">
					<div class="bet-pod-mogs connectedSortable">
						@foreach($mogs as $mog)
							@if($mog->on_bet)
								@if($mog->rating >= 900)
									<div id="{{$mog->active_id}}"
										class="mog-img legendary" 
										title="{{{$mog->name}}} | {{$mog->rating}}" 
										style="background-image: url(/images/mogs/{{$mog->id}}" 
										data="{{$mog->src_url}}"
										rating="{{$mog->rating}}"
										owner="{{$user->id}}">
									</div>
								@elseif($mog->rating < 900 && $mog->rating >= 600)	
									<div id="{{$mog->active_id}}"
										class="mog-img rare" 
										title="{{{$mog->name}}} | {{$mog->rating}}" 
										style="background-image: url(/images/mogs/{{$mog->id}}" 
										data="{{$mog->src_url}}"
										rating="{{$mog->rating}}"
										owner="{{$user->id}}">
									</div>
								@else
									<div id="{{$mog->active_id}}"
										class="mog-img" 
										title="{{{$mog->name}}} | {{$mog->rating}}" 
										style="background-image: url(/images/mogs/{{$mog->id}}" 
										data="{{$mog->src_url}}"
										rating="{{$mog->rating}}"
										owner="{{$user->id}}">
									</div>
								@endif
							@endif
						@endforeach
					</div>	
				</div>
			</aside>
			<div class="mog-inventory">
				<div>
					<h4>Mog Inventory Count: {{count($mogs)}}</h4>
				</div>
				<div class="mog-inv-container">
					<div class="inv-mogs connectedSortable">
						@foreach($mogs as $mog)
							@if(!$mog->on_bet)
								@if($mog->rating >= 900)
									<div id="{{$mog->active_id}}"
										class="mog-img legendary" 
										title="{{{$mog->name}}} | {{$mog->rating}}" 
										style="background-image: url(/images/mogs/{{$mog->id}}" 
										data="{{$mog->src_url}}"
										rating="{{$mog->rating}}"
										owner="{{$user->id}}">
									</div>
								@elseif($mog->rating < 900 && $mog->rating >= 600)	
									<div id="{{$mog->active_id}}"
										class="mog-img rare" 
										title="{{{$mog->name}}} | {{$mog->rating}}" 
										style="background-image: url(/images/mogs/{{$mog->id}}" 
										data="{{$mog->src_url}}"
										rating="{{$mog->rating}}"
										owner="{{$user->id}}">
									</div>
								@else
									<div id="{{$mog->active_id}}"
										class="mog-img" 
										title="{{{$mog->name}}} | {{$mog->rating}}" 
										style="background-image: url(/images/mogs/{{$mog->id}}" 
										data="{{$mog->src_url}}"
										rating="{{$mog->rating}}"
										owner="{{$user->id}}">
									</div>
								@endif
							@endif
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection