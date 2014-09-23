<?php

/* Groups the queries and the search into the same class so we don't have
 * to make the queries global for access in the user sort callback function.
 *
 * Currently, this sort operates on three search query parameters - title,
 * album, and artist. The order of this list determines the order that the
 * sort uses each parameter. The number and order of parameters may change in
 * the future.
 *
 * This sort defines three match groups; exact matches come first, followed by
 * matches that start with the query, followed by all other matches. The songs
 * are sorted by arranging them in the given match groups using the first
 * parameter of the query that the user gave (i.e. if title is blank but album
 * is set, the songs are first sorted by album).
 *
 * Next, each of the match group sections is further sorted by the next
 * parameter of the search that the user gave. that the user gave (i.e. if
 * title and artist are both set, the songs are sorted by title into the three
 * match sections, then within each match section the songs are further sorted
 * by artist into three more match sections based on the artist name. This is
 * repeated for each parameter of the search that is given.
 *
 * Within each match group of the last search parameter provided, order is not
 * defined.
 *
 * TODO: Sort by popularity with each match group of last search parameter.
 */ 
class SongSorter {
	private $titleQuery;
	private $albumQuery;
	private $artistQuery;

	// Creates new ResultSorter. Pass in the search queries;
	// set $title/$album/$artist to null or empty string if
	// user did not include that parameter in the search.
	public function __construct($title, $album, $artist) {
		$this->titleQuery = !empty($title) ? $title : null;
		$this->albumQuery = !empty($album) ? $album : null;
		$this->artistQuery = !empty($artist) ? $artist : null;
	}

	// Performs the sort. Sort is performed in-place on the given array.
	// $songs - The array containing Song instances.
	public function sort($songs) {
		usort($songs, array(this, 'compare_songs');
	}

	// returns less than 0 int if $s1 is less than $s2, 0 if $s1 = $s2 and
	// greater
	// than 0 int if $s1 is greater than $s2.
	// $s1 and $s2 are the Song instances that are being compared.
	//
	// current order of comparison is song title, song album, song artist.
	// Parameter is only used if it is non-null; see class description for
	// more details;
	private function compare_songs($s1, $s2) {
		$eq = 0;
		if ($this->titleQuery != null) {
			$eq = $this->equivclass_strcmp($s1->title, $s2->title, $this->titleQuery);
		}
		if ($eq == 0 && $this->albumQuery != null) {
			$eq = $this->equivclass_strcmp($s1->album, $s2->album, $this->albumQuery);
		}
		if ($eq == 0 && $this->artistQuery != null) {
			$eq = $this->equivclass_strcmp($s1->artist, $s2->artist, $this->artistQuery);
		}
		return $eq;
	}

	// Compares two strings based on a custom ordering for this sorter.
	// The equivalence classes are, from lowest-valued to highest-valued:
	// 0. strings that contain the exact query
	// 1. strings that contain the query followed by anything else
	// 2. all other strings
	//
	// Comparison is case-insensitive.
	//
	// $str1, $str2 - The strings to compare.
	// $query - the query to use for determining the strings' equivalence
	// classes.
	// Returns an integer less than 0 if $str1 is in a lower equivalence
	// class than $str2, 0 if $str1 and $str2 are in the same equivalence
	// class, or an integer greater than 0 if $str1 is in a higher
	// equivalence class than $str2.
	private function equivclass_strcmp($str1, $str2, $query) {
		return $this->equivclass($str1, $query) - $this->equivclass($str2, $query);
	}

	// Gets a string's equivalence class based on the search query.
	// The equivalence classes are, from lowest-valued to highest-valued:
	// 0 - strings that contain the exact query
	// 1 - strings that contain the query followed by anything else
	// 2 - all other strings
	// The query is case-insensitive.
	//
	// Returns the integer corresponding to the equivalence class of the
	// string.
	private function equivclass($str, $query) {
		$eqclass = -1;
		$str = mb_strtoupper($str);
		$query = mb_strtoupper($query);
		if ($str === $query) {
			$eqclass = 0;
		} else if (mb_strpos($str, $query) === 0) {
			$eqclass = 1;
		} else {
			$eqclass = 2;
		}
		return $eqclass;
	}
}
