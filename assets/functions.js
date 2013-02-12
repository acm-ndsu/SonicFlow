
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

function maximize() {
	decreaseSize($("#header").height(),$("#footer").height());
	window.scrollTo(0,120-$("#header").height());
	$("body").css("overflow","hidden");
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
