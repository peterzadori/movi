<?php

namespace movi\Media;

use Nette\Http\FileUpload;

interface IMediaStorage
{

	public function setMedia(Media $media);

	public function load($filename);

	public function save(FileUpload $file);

	public function setStorageDir($dir);

	public function setNamespace($namespace);

}