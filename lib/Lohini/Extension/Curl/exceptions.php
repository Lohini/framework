<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Curl;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */


/**
 */
interface Exception
{
}


/**
 */
class InvalidUrlException extends \InvalidArgumentException implements Exception
{
}

/**
 */
class MissingCertificateException extends \RuntimeException implements Exception
{
}

/**
 */
class CurlException
extends \RuntimeException
implements Exception
{
	/** @var Request */
	private $request;
	/** @var Response */
	private $response;


	/**
	 * @param string $message
	 * @param Request $request
	 * @param Response $response
	 */
	public function __construct($message, Request $request=NULL, Response $response=NULL)
	{
		parent::__construct($message);
		$this->request=$request;
		if ($this->response=$response) {
			$this->code=$response->headers['Status-Code'];
			}
	}

	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @return Response
	 */
	public function getResponse()
	{
		return $this->response;
	}
}


/**
 */
class FailedRequestException
extends CurlException
{
	/** @var mixed */
	private $info;


	/**
	 * @param CurlWrapper $curl
	 */
	public function __construct(CurlWrapper $curl)
	{
		parent::__construct($curl->error);
		$this->code=$curl->errorNumber;
		$this->info=$curl->info;
	}

	/**
	 * @see curl_getinfo()
	 * @return mixed
	 */
	public function getInfo()
	{
		return $this->info;
	}
}


/**
 */
class BadStatusException
extends CurlException
{
}
