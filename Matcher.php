<?php 
/*
FRONT - Step 1:
{User selects to find match
	set matchFound to false
	Display queue timer
	Send request for match with player's betRating to BACK
	Set matchFound to value returned from BACK
}

BACK - Step 1:
{On request call RequestMatch (betRating)
	If a qualifying match exists given betRating
		join that match and insert to player 2 position
		return true to FRONT
	else 
		create new record and insert to player 1 position
		Return false to FRONT
}

FRONT - Step 1 (cont.)
{
	Set matchFound to value returned from BACK

	If server response is false
		While matchFound is false
			Reset matchFound to value returned from call to server
			-sleep for 5 seconds???
		End While

	If matchFound is true
		Call checkPlayerAccept
}

FRONT - STEP 2
{checkPlayerAccept
	display prompt to accept match on 10 second timer
		- On accept send request to BACK that player accepts match

}
*/