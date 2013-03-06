<?php

// Contains functions for repairing database poblems

function fixBadId($id) {
	removeSongFromQueue($id);
}

?>
