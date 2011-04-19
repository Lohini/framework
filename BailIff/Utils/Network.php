<?php // vim: ts=4 sw=4 ai:
namespace BailIff\Utils;

use Nette\ArgumentOutOfRangeException;

/**
 * BailIff Network
 *
 * @author Lopo <lopo@losys.eu>
 */
final class Network
{
	/**
	 * computes range of IP addresses from IPv4 CIDR notation {@link http://en.wikipedia.org/wiki/CIDR_notation}
	 * @param string CIDR
	 * @return array((long)min, (long)max)
	 * @throws \ArgumentOutOfRangeException
	 */
	public static function CIDR2LongRange($net)
	{
		$ip=explode('.', trim(strtok($net, '/'), '.'));
		$bits=strtok('/');
		if ($bits!==FALSE && ($bits<1 || $bits>31)) {
			throw new ArgumentOutOfRangeException('address prefix size must be between 1 and 31');
			}
		$prefix=max(($bits!==FALSE? $bits : count($ip)<<3), (4-count($ip))<<3);
		$long=0;
		for ($i=0; $i<4; $i++) {
			$long+= isset($ip[$i])? $ip[$i]<<((3-$i)<<3) : 0;
			}
		return array(($long&((pow(2, $prefix)-1)<<(32-$prefix)))+1, ($long|(pow(2, 32-$prefix)-1))-1);
	}

	/**
	 * check if $net contains $host
	 * @param string $host IP
	 * @param array|string $net
	 */
	public static function HostInCIDR($host, $net)
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
}
