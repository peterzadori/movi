<?php

namespace movi\Media\Storages;

use movi\Media\File;
use movi\Media\Image;
use movi\Media\IMediaStorage;
use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\Utils\Strings;

class ImageStorage extends FileStorage
{

	public function load($filename)
	{
		$file = $this->getAbsolutePath() . '/' . $filename;

		if (!file_exists($file)) {
			return NULL;
		} else {
			return new Image($filename, $file);
		}
	}


	public function save(FileUpload $file)
	{
		if ($file->isImage()) {
			return parent::save($file);
		}
	}

}