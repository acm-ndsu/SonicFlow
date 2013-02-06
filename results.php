<?php include('assets/includes/sonicflow.php'); ?>
<?php include('header.html'); ?>
<?php
	$provider = $_GET['provider'];
	$search = $_GET['searchString'];

	$searchResults;
	$providerName;

	switch ($provider) {
		case 'sonicflow':
			$searchResults = getSonicFlowResults($search);
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
			<ul class="songs">
<?php
			foreach ($searchResults as $s) {
?>
				<li>
					<img class="albumArt" src="<?php echo getArtLoc($s->albumId); ?>" alt="Song Album" />
					<div class="songTitle"><?php echo $s->title; ?></div>
					<div class="songArtist"><?php echo $s->artist; ?></div>
					<div class="songAlbum"><?php echo $s->album; ?></div>
					<form action="addsong.php" method="get">
						<input type="hidden" name="id" value="<?php echo $s->id; ?>" />
						<input type="submit" value="Add to Queue" />
					</form>
				</li>
<?php
			}
?>
			</ul>
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
