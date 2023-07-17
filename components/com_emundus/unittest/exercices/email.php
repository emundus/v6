<?php


function oldCorrectEmail($email): bool
{
	$is_correct = true;

	if (empty($email)) {
		$is_correct = false;
	} else {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$is_correct = false;
		} else {
			$domain = substr($email, strpos($email, '@') + 1);

			if (!checkdnsrr($domain)) {
				$is_correct = false;
			}
		}
	}

	return $is_correct;
}

/**
 * Check given email is not empty, has a valid format, and email dns exists
 * @param $email
 * @return bool
 */
function correctEmail($email): bool
{
	$is_correct = true;

	if (empty($email)) {
		$is_correct = false;
	} else {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$is_correct = false;
		} else {
			$domain = substr($email, strpos($email, '@') + 1);

			if (!checkdnsrr($domain)) {
				// TODO: loop to check for multiple subdomains
				$primary_domain = substr($domain, strpos($domain, '.') + 1);
				if (!checkdnsrr($primary_domain)) {
					$is_correct = false;
				}
			}
		}
	}

	return $is_correct;
}