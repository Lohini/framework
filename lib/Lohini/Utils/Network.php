<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 */
namespace Lohini\Utils;

/**
 * @author Lopo <lopo@lohini.net>
 */
final class Network
{
	/**
	 * computes range of IP addresses from IPv4 CIDR notation {@link http://en.wikipedia.org/wiki/CIDR_notation}
	 *
	 * @param string CIDR
	 * @return array ((long)min, (long)max)
	 * @throws \Nette\ArgumentOutOfRangeException
	 */
	public static function CIDR2LongRange($net)
	{
		$ip=explode('.', trim(strtok($net, '/'), '.'));
		$bits=strtok('/');
		if ($bits!==FALSE && ($bits<1 || $bits>32)) {
			throw new \Nette\ArgumentOutOfRangeException('Address prefix size must be between 1 and 32');
			}
		$prefix=max(
				$bits!==FALSE? $bits : count($ip)<<3,
				(4-count($ip))<<3
				);
		$long=0;
		for ($i=0; $i<4; $i++) {
			$long+= isset($ip[$i])? $ip[$i]<<((3-$i)<<3) : 0;
			}
		return $prefix==32
			? [$long, $long]
			: [
				$long&(((1<<$prefix)-1)<<(32-$prefix)),
				$long|((1<<(32-$prefix))-1)
				];
	}

	/**
	 * check if $net contains $host
	 *
	 * @param string $host IP
	 * @param array|string $net
	 */
	public static function hostInCIDR($host, $net)
	{
		if ($host==$net) {
			return TRUE;
			}
		$ip=ip2long($host);
		if (is_array($net)) {
			foreach ($net as $n) {
				if ($host==$n) {
					return TRUE;
					}
				$range=self::CIDR2LongRange($n);
				if ($ip>=$range[0] && $ip<=$range[1]) {
					return TRUE;
					}
				}
			return FALSE;
			}
		$range=self::CIDR2LongRange($net);
		return ($ip>=$range[0] && $ip<=$range[1]);
	}

	/**
	 * @return string
	 */
	public static function getRemoteIP()
	{
		if (PHP_SAPI=='cli') {
			return '127.0.0.1';
			}
		$sra=$_SERVER['REMOTE_ADDR'];
		// Find the user's IP address. (but don't let it give you 'unknown'!)
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])
			&& !empty($_SERVER['HTTP_CLIENT_IP'])
			&& (!preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP'])
				|| preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']))
			) {
			// We have both forwarded for AND client IP... check the first forwarded for as the block - only switch if it's better that way.
			if (strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.')!=strtok($_SERVER['HTTP_CLIENT_IP'], '.')
				&& '.'.strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.')==strrchr($_SERVER['HTTP_CLIENT_IP'], '.')
				&& (!preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR'])
					|| preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']))
				) {
				$sra=implode('.', array_reverse(explode('.', $_SERVER['HTTP_CLIENT_IP'])));
				}
			else {
				$sra=$_SERVER['HTTP_CLIENT_IP'];
				}
			}
		if (!empty($_SERVER['HTTP_CLIENT_IP'])
			&& (!preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP'])
				|| preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $sra))
			) {
			// Since they are in different blocks, it's probably reversed.
			if (strtok($sra, '.')!=strtok($_SERVER['HTTP_CLIENT_IP'], '.')) {
				return implode('.', array_reverse(explode('.', $_SERVER['HTTP_CLIENT_IP'])));
				}
			else {
				return $_SERVER['HTTP_CLIENT_IP'];
				}
			}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// If there are commas, get the last one.. probably.
			if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')!==FALSE) {
				$ips=array_reverse(explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']));

				// Go through each IP...
				foreach ($ips as $i => $ip) {
					// Make sure it's in a valid range...
					if (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $ip)
						&& !preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $sra)
						) {
						continue;
						}
					// Otherwise, we've got an IP!
					return trim($ip);
					}
				}
			// Otherwise just use the only one.
			elseif (!preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR'])
					|| preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $sra)
				) {
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
				}
			}
		elseif (!isset($_SERVER['REMOTE_ADDR'])) {
			return '';
			}
		return $sra;
	}
}
