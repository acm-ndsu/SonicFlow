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

function unmaximize() {
    location.reload();
}

function maximize() {
	decreaseSize($("#header").height(),$("#footer").height());
	window.scrollTo(0,120-$("#header").height());
	$("body").css("overflow", "hidden");

	var height = window.innerHeight - $("#header").height() - $("#currentSong").height() - 60;
	if (height > 400)
	{
	    $("#currentArt").width(height);
	    $("#currentArt").height(height);
	}

	$("#fullScreen").attr("onclick", "unmaximize()");
}

function decreaseSize(head) {
	if (head > 0) {
		$("#header").height(head-2);
		$(".content").css("padding-top",Math.min($("#currentSong").css("padding-top") + 1,40));
		$("#topNav").css("z-index",-1);
	}

	$("#footer").css("display", "none");

	if (head > 0) {
		setTimeout(maximize,1);
	}
}

/**
 * Updates the elements on the now playing page based on the specified song.
 * @param song A JSON element that represents a song.
 *
 */
function updatePlaying(song) {
	$("#songId").html(song.id);
	$(".songTitle").html(song.title);
	$(".songArtist").html(song.artist);
	$(".songAlbum").html(song.album);
	$("#currentArt").attr("src",song.arturl);
}
