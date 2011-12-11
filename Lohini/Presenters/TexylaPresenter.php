<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Presenters;
/**
 * Texyla presenter
 *
 * @author Jan Marek
 * @license MIT
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Application\Responses,
	Nette\Utils\Strings,
	Nette\Image,
	Nette\Environment,
	Nette\Http\IResponse;

/**
 */
class TexylaPresenter
extends BasePresenter
{
	/** @var string */
	private $baseFolderPath;
	/** @var string */
	private $tempDir;


	protected function startup()
	{
		parent::startup();
		$texy=$this->context->texy->texy;
		$this->baseFolderPath= $texy->imageModule->fileRoot= $this->context->params['varDir'].'/storage/texyla';
		$texy->imageModule->root= $this->link('file');
		$this->tempDir=$this->baseFolderPath.'/thumbnails';
	}

	public function actionPreview()
	{
		$this->sendResponse(
				new Responses\TextResponse(
						$this->context->texy->texy->process(
								$this->getHttpRequest()->getPost('texy')
								)
						)
				);
	}

	/**
	 * @param string $msg
	 */
	private function sendError($msg)
	{
		$this->sendResponse(new Responses\JsonResponse(array('error' => $msg), 'text/plain'));
	}

	/**
	 * @param string $path
	 * @return string
	 */
	protected function thumbnailFileName($path)
	{
		$path=realpath($path);
		return 'texylapreview-'.md5("$path|".filemtime($path)).'.jpg';
	}

	/**
	 * @param string $folder
	 */
	public function actionListFiles()
	{
		if (!$this->user->isLoggedIn()) {
			$this->sendError('Anonymous listing disabled');
			return;
			}

		$files=array();

		foreach ($this->context->sqldb->getRepository('LE:Upload')->findByUser($this->user->id) as $fileInfo) {
			$fileName=$fileInfo->filename;

			// image
			if (@getImageSize("$this->baseFolderPath/".$fileInfo->filename)) {
				$thumbFileName=$this->thumbnailFileName("$this->baseFolderPath/".$fileInfo->filename);
				$thumbnailKey=$this->link('thumbnail', $fileName);

				$files[]=array(
					'type' => 'image',
					'name' => $fileInfo->name,
					'insertUrl' => $fileName,
					'description' => $fileInfo->name,
					'thumbnailKey' => $thumbnailKey
					);
				}
			// other file
			else {
				$files[]=array(
					'type' => 'file',
					'name' => $fileInfo->name,
					'insertUrl' => $this->link('file', $fileName),
					'description' => $fileInfo->name
					);
				}
			}

		// send response
		$this->sendResponse(new Responses\JsonResponse(array('list' => $files)));
	}

	/**
	 * @param string $key
	 */
	public function actionThumbnail($id)
	{
		try {
			if ($id===NULL || ($ent=$this->context->sqldb->getRepository('LE:Upload')->findOneByFilename($id))===NULL) {
				$this->terminate(/*IResponse::S404_NOT_FOUND*/); // everything exist, but empty :)
				}
			$path="$this->baseFolderPath/$ent->filename";
			if (file_exists("$this->tempDir/".$this->thumbnailFileName($path))) {
				header('Content-Type: '.image_type_to_mime_type(IMAGETYPE_JPEG));
				readfile("$this->tempDir/".$this->thumbnailFileName($path));
				$this->terminate();
				}
			$image=Image::fromFile($path)->resize(60, 40);
			$image->save("$this->tempDir/".$this->thumbnailFileName($path));
			@chmod($path, 0666);
			$image->send();
			}
		catch (\Exception $e) {
			Image::fromString(Image::EMPTY_GIF)->send(Image::GIF);
			}

		$this->terminate();
	}

	public function actionUpload()
	{
		// file
		$file=$this->getHttpRequest()->getFile('file');

		// check
		if ($file===NULL || !$file->isOk()) {
			$this->sendError('Upload error.');
			}

		// move
		$data['name']=$file->name;
		$data['filename']= $data['etag']= md5($file->name).'-'.dechex($file->size).'-'.dechex($this->user->id);
		if (file_exists("$this->baseFolderPath/".$data['filename'])) {
			$this->sendError('File already exist');
			}
		$date['created']=new \DateTime;
		$date['user']=$this->user->id;
		$data['size']=$file->size;
		$data['mimetype']=$file->contentType;

		if (@$file->move("$this->baseFolderPath/".$data['filename'])) {
			@chmod("$this->baseFolderPath/".$data['filename'], 0666);

			$sqldb=$this->context->sqldb;
			$srv=$sqldb->getModelService('Lohini\Database\Models\Entities\Upload');
			$entu=$srv->create($data);

			$this->payload->filename=$this->link('file', $data['filename']);
			$this->payload->type= $file->isImage()? 'image' : 'file';

			$this->sendResponse(new Responses\JsonResponse($this->payload, 'text/plain'));
			}
		else {
			$this->sendError('Move failed.');
			}
	}

	/**
	 */
	public function actionMkDir()
	{
		$this->sendError("Unable to create directory");
	}

	/**
	 * @param string $folder
	 * @param string $name
	 */
	public function actionDelete($name)
	{
		if (!$this->user->isLoggedIn()) {
			$this->sendError('Unable to delete file.');
			}
		$sqldb=$this->context->sqldb;
		$repo=$sqldb->getRepository('LE:Upload');
		if (($ent=$this->findNameOwned($name))===NULL
			|| !file_exists($fname="$this->baseFolderPath/$ent->filename")
			) {
			$this->sendError('File does not exist.');
			}

		if (is_file($fname)) {
			if (unlink($fname)) {
				$repo->delete($ent);
				$sqldb->entityManager->flush();
				$this->sendResponse(new Responses\JsonResponse(array('deleted' => TRUE)));
				}
			else {
				$this->sendError('Unable to delete file.');
				}
			}
	}

	/**
	 * @param string $folder
	 * @param string $oldname
	 * @param string $newname
	 */
	public function actionRename($oldname, $newname)
	{
		if (!$this->user->isLoggedIn()) {
			$this->sendError('Unable to rename file.');
			}
		$sqldb=$this->context->sqldb;
		if (($ent=$this->findNameOwned($oldname))===NULL
			|| !file_exists($oldpath="$this->baseFolderPath/$ent->filename")
			) {
			$this->sendError('File does not exist.');
			}
		
		$fname=md5($newname).'-'.dechex($ent->size).'-'.dechex($this->user->id);
		$newpath="$this->baseFolderPath/$fname";
		if (rename($oldpath, $newpath)) {
			$ent->name=$newname;
			$ent->filename=$fname;
			$sqldb->entityManager->flush();
			$this->sendResponse(new Responses\JsonResponse(array('deleted' => TRUE)));
			}
		else {
			$this->sendError('Unable to rename file.');
			}
	}

	/**
	 * @param string $id
	 */
	public function actionFile($id)
	{
		$sqldb=$this->context->sqldb;
		if ($id===NULL || ($ent=$sqldb->getRepository('LE:Upload')->findOneByFilename($id))===NULL) {
			$this->terminate();
			}
		$ent->cntDownload++;
		$sqldb->entityManager->flush();
		$sh=$this->getHttpResponse();
		$sh->setHeader('Etag', $ent->etag);
		$sh->setExpiration(IResponse::PERMANENT);
		$inm=$this->getHttpRequest()->getHeader('if-none-match');
		if ($inm && $inm==$ent->etag) {
			$sh->setCode(IResponse::S304_NOT_MODIFIED);
			$this->terminate();
			}
		$sh->setContentType($ent->mimetype);
		$sh->setHeader('Content-Length', $ent->size);
		readfile("$this->baseFolderPath/".$ent->filename);
		$this->terminate();
	}

	/**
	 *
	 * @param \Lohini\Database\Models\Entities\Upload $name
	 * @return string
	 */
	protected function findNameOwned($name)
	{
		if (!$this->user->isLoggedIn()) {
			return NULL;
			}
		$repo=$this->context->sqldb->getRepository('LE:Upload');
		return $repo->findOneBy(array('name' => $name, 'user' => $this->user->id));
	}
}
