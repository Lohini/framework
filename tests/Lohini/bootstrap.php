<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */

/**
 * Test initialization and helpers.
 *
 * @author Lopo <lopo@lohini.net>
 * @author David Grudl
 */


if (@!include __DIR__.'/../../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
	}


// configure environment
Tester\Environment::setup();
class_alias('Tester\Assert', 'Assert');
date_default_timezone_set('Europe/Bratislava');


// create temporary directory
define('TEMP_DIR', __DIR__.'/../temp/'.getmypid());
@mkdir(dirname(TEMP_DIR)); // @ - directory may already exist
Tester\Helpers::purge(TEMP_DIR);


$_SERVER=array_intersect_key($_SERVER, array_flip(['PHP_SELF', 'SCRIPT_NAME', 'SERVER_ADDR', 'SERVER_SOFTWARE', 'HTTP_HOST', 'DOCUMENT_ROOT', 'OS', 'argc', 'argv']));
$_SERVER['REQUEST_TIME']=1234567890;
$_ENV= $_GET= $_POST=[];


if (extension_loaded('xdebug')) {
	xdebug_disable();
	Tester\CodeCoverage\Collector::start(__DIR__.'/coverage.dat');
	}


function id($val) {
	return $val;
	}


class Notes
{
	static public $notes=[];

	public static function add($message)
	{
		self::$notes[]=$message;
	}

	public static function fetch()
	{
		$res=self::$notes;
		self::$notes=[];
		return $res;
	}
}


function before(\Closure $function=NULL)
{
	static $val;
	if (!func_num_args()) {
		return $val? $val() : NULL;
		}
	$val=$function;
}


function test(\Closure $function)
{
	before();
	$function();
}
