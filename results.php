<?php require_once('assets/includes/sonicflow.php'); ?>
<?php include('header.html'); ?>
<?php
	$provider = $_GET['provider'];

	$search_song = mb_ereg_replace('\\s\\s+', "", trim($_GET['search_song']));
	$search_album = mb_ereg_replace('\\s\\s+', "", trim($_GET['search_album']));
	$search_artist = mb_ereg_replace('\\s\\s+', "", trim($_GET['search_artist']));
	$search = $search_song . ' ' . $search_album . ' ' . $search_artist;
	$search = mb_ereg_replace('\\s\\s+', "", $search);

	$searchResults;
	$providerName;

	switch ($provider) {
		case 'sonicflow':
			$searchResults = getSonicFlowResults($search_song, $search_album, $search_artist);
			$providerName = "SonicFlow";

			// If nothing is in SonicFlow, then default to Grooveshark
			if (count($searchResults) > 0) {
				break;
			} else {
				$provider = "grooveshark";
			}

		case 'grooveshark':
			$searchResults = getGroovesharkResults($search);
			$providerName = "Grooveshark";
			break;

		default:
			$searchResults = NULL;
			break;
	}

	if (is_null($searchResults)) {
?>
			<div class="errorMessage">
				<p>Something went wrong. Please hit the back button and try
				again.</p>
			</div>
<?php
	} else {
		$numResults = count($searchResults);
?>
			<p><span id="resultCount"><?php echo $numResults; ?></span>
			results in <?php echo $providerName; ?> for
			"<span id="searchPhrase"><?php echo $search; ?></span>":</p>
			<div class="results">
				<ul class="songs">
<?php
			foreach ($searchResults as $s) {
?>
					<li onclick="submit(<?php echo $s->id; ?>);">
						<img class="albumArt" src="<?php echo getArtLoc($s->albumId); ?>" alt="Song Album" />
						<div class="songTitle"><?php echo $s->title; ?></div>
						<div class="songArtist"><?php echo $s->artist; ?></div>
						<div class="songAlbum"><?php echo $s->album; ?></div>
						<form id="<?php echo $s->id;?>" action="addsong.php" method="post">
							<input type="hidden" name="id" value="<?php echo $s->id; ?>" />
						</form>
					</li>
<?php
			}
?>
				</ul>
			</div>
<?php
	}
	if ($provider == 'sonicflow') {
?>
			<form action="results.php" method="get">
				<input type="submit" value="Song not listed" />
				<input type="hidden" name="searchString" value="<?php echo $search ?>" />
				<input type="hidden" name="provider" value="grooveshark" />
			<form>
<?php
	} 
?>
<?php include('footer.html'); ?>
