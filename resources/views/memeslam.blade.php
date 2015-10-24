@extends('layout')

@section('header')
	@extends('header')
@endsection

@section('main')
	<div class="game-field">

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