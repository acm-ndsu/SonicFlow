
function showQueue() {
	document.getElementById('page').src = "queue.html";	
}

function showNowPlaying() {
	document.getElementById('page').src = "playing.html";
}

function showSearch() {
	document.getElementById('page').src = "search.html";
}

function submit(id) {
	document.getElementById(id).submit();
}

