<?php // vim: set ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
// no namespace

use Nette\Environment as NEnvironment;

/**
 * Translates the given string.
 * @param string $message
 * @return string
 */
function __($message)
{
	return NEnvironment::getService('translator')->translate($message);
}

/**
 * Translates the given string with plural.
 * @param string $single
 * @param string $plural
 * @param int $number plural form (positive number)
 * @return string
 */
function _n($single, $plural, $number)
{
	return NEnvironment::getService('translator')->translate($single, array($plural, $number));
}

/**
 * Translates the given string with vsprintf.
 * @param string $message
 * @param array $args for vsprintf
 * @return string
 */
function _x($message, array $args)
{
	return NEnvironment::getService('translator')->translate($message, NULL, $args);
}

/**
 * Translates the given string with plural and vsprintf.
 * @param string $single
 * @param string $plural
 * @param int $number plural form (positive number)
 * @return string
 */
function _nx($single, $plural, $number, array $args)
{
	return NEnvironment::getService('translator')->translate($single, array($plural, $number), $args);
}