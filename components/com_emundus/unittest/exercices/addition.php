<?php

class Calculatrice {
	function addition($a, $b) {
		return $a + $b;
	}

	function additionCorrigé($a, $b) {
		$somme = 0;

		if (is_int($a) && is_int($b)) {
			$somme = $a + $b;
		}

		return $somme;
	}
}