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
    alert("blarg");
}

function maximize() {
	decreaseSize($("#header").height(),$("#footer").height());
	window.scrollTo(0,120-$("#header").height());
	$("body").css("overflow", "hidden");

	screenH = screen.height - 200;
	if (screenH > 400)
	{
	    $("#currentArt").css("width", screnH);
	    $("#currentArt").css("height", screenH);
	}

	$("#fullScreen").click(unmaximize());
}

function decreaseSize(head,foot) {
	if (head > 0) {
		$("#header").height(head-2);
		$(".content").css("padding-top",Math.min($("#currentSong").css("padding-top") + 1,40));
		$("#topNav").css("z-index",-1);
	}
	if (foot > 10) {
		$("#footer").height(foot-1);
		$("#gsImg").height($("#gsImg").height()-1);
		
	}
	if (head > 0 || foot > 10) {
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
