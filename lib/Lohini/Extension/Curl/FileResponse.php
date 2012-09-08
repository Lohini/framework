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
 * @license http://www.kdyby.org/license
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class FileResponse
extends Response
{
	/** @var string */
	private $file;
	/** @var string */
	private $type;


	/**
	 * @param CurlWrapper $curl
	 * @param array $headers
	 */
	public function __construct(CurlWrapper $curl, array $headers)
	{
		parent::__construct($curl, $headers);
		$this->file=$curl->file;
	}

	/**
	 * Returns the MIME content type of a file.
	 *
	 * @return string
	 */
	public function getContentType()
	{
		if ($this->type===NULL) {
			$this->type=\Nette\Utils\MimeTypeDetector::fromFile($this->file);
			}
		return $this->type;
	}

	/**
	 * Returns the size of a file.
	 *
	 * @return int
	 */
	public function getSize()
	{
		return filesize($this->file);
	}

	/**
	 * Returns the path to a file.
	 *
	 * @return string
	 */
	public function getTemporaryFile()
	{
		return $this->file;
	}

	/**
	 * Returns the path to a file.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->file;
	}

	/**
	 * Move file to new location.
	 *
	 * @param string $dest
	 * @return FileResponse
	 * @throws \Lohini\DirectoryNotWritableException
	 * @throws \Lohini\FileNotWritableException
	 */
	public function move($dest)
	{
		if (!is_dir($destDir=dirname($dest))) {
			throw new \Lohini\DirectoryNotWritableException("Please make directory '$destDir' writable.");
			}
		if (!@rename($this->file, $dest)) {
			throw new \Lohini\FileNotWritableException("Unable to move file '$this->file' to '$dest'.");
			}
		chmod($dest, 0666);
		$this->file=$dest;
		return $this;
	}

	/**
	 * Is uploaded file GIF, PNG or JPEG?
	 *
	 * @return bool
	 */
	public function isImage()
	{
		return in_array($this->getContentType(), array('image/gif', 'image/png', 'image/jpeg'), TRUE);
	}

	/**
	 * Returns the image.
	 *
	 * @return \Nette\Image
	 */
	public function toImage()
	{
		return \Nette\Image::fromFile($this->file);
	}

	/**
	 * Returns the dimensions of an image as array.
	 *
	 * @return array
	 */
	public function getImageSize()
	{
		return @getimagesize($this->file); // @ - files smaller than 12 bytes causes read error
	}

	/**
	 * Get file contents.
	 *
	 * @return string
	 */
	public function getContents()
	{
		return file_get_contents($this->file);
	}

	/**
	 * @param CurlWrapper $curl
	 * @return array
	 * @throws \Nette\InvalidStateException
	 */
	public static function stripHeaders(CurlWrapper $curl)
	{
		$headersFile=$curl->file.'.headers';
		@fclose($curl->options['file']); // internationally @
		@fclose($curl->options['writeHeader']); // internationally @

		if (($headersHandle=@fopen($headersFile, "rb"))===FALSE) { // internationally @
			throw new \Nette\InvalidStateException("File '$headersFile' not readable.");
			}

		$curl->responseHeaders=fread($headersHandle, filesize($headersFile));
		if (!$headers=CurlWrapper::parseHeaders($curl->responseHeaders)) {
			throw new CurlException('Failed parsing of response headers');
			}
		if (!@fclose($headersHandle) || !@unlink($headersFile)) {
			throw new \Nette\InvalidStateException("File '$headersFile' can't be deleted.");
			}

		return $headers;
	}

	/**
	 * @param CurlWrapper $curl
	 * @param string $dir
	 * @return CurlWrapper
	 * @throws \Lohini\FileNotWritableException
	 */
	public static function prepareDownload(CurlWrapper $curl, $dir)
	{
		do {
			$fileName=urlencode((string)$curl->getUrl()).'.'.\Nette\Utils\Strings::random().'.tmp';
			} while (is_file($dir.'/'.$fileName));

		if (($fileHandle=@fopen($curl->file=$dir.'/'.$fileName, 'wb'))===FALSE) {
			throw new \Lohini\FileNotWritableException("File $curl->file is not writable.");
			}
		if (($headersHandle=@fopen($curl->file.'.headers', 'wb'))===FALSE) {
			throw new \Lohini\FileNotWritableException("File $curl->file is not writable.");
			}
		return $curl->setOptions(array(
			'file' => $fileHandle,
			'writeHeader' => $headersHandle,
			'binaryTransfer' => TRUE
			));
	}
}
