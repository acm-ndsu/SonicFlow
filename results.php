<?php include('assets/includes/sonicflow.php'); ?>
<?php include('header.html'); ?>
<?php
	$provider = $_GET['provider'];
	
	var $searchResults;
	
	switch ($provider) {
		case 'sonicflow':
			$searchResults = getSonicFlowResults();
			break;
			
		case 'grooveshark':
			$searchResults = getGroovesharkResults();
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
	}
?>
<?php include('footer.html'); ?>
