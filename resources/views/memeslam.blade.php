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
					<?php 
						// $html = '';
						// for($i = 0; $i < 30; $i++) {
						// 	$html .= '<div class="mog" title="'.$mogs[$i]->name.' | '.$mogs[$i]->rating.'" style="background-image: url('.$mogs[$i]->img.'" data="'.$mogs[$i]->srcUrl.'"></div>';
						// }
						// echo $html;
					?>
				</div>
			</div>
			<div class="won-mogs-container">
				<h3>Won Mogs</h3>
				<div class="won-mogs">
					<?php 
						// $html = '';
						// for($i = 0; $i < 11; $i++) {
						// 	$html .= '<div class="mog" title="'.$mogs[$i]->name.' | '.$mogs[$i]->rating.'" style="background-image: url('.$mogs[$i]->img.'" data="'.$mogs[$i]->srcUrl.'"></div>';
						// }
						// echo $html;
					?>
				</div>
			</div>
		</div>
	</div>
@endsection