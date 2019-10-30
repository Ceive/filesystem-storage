<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */

namespace Ceive\Filesystem\Storage\Operation;

use Ceive\Filesystem\File\UploadedFile;
use Ceive\Filesystem\FileInterface;
use Ceive\Filesystem\FS;
use Ceive\Filesystem\Storage\Operation;
use Ceive\Filesystem\Storage\Tmp\StorageTmp;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class OperationUploadTmp
 * @package Ceive\Filesystem\StorageLinear\Operation
 */
class OperationUploadTmp extends OperationTmpRequires{
	
	/** @var  UploadedFile */
	protected $file;


	public function validate(){
		
	}
	
	public function apply(){
		
	}
	
	
	public function onError(){
		
	}
	
	public function onSuccess(){
		
	}

    /**
     * @return mixed
     * @throws \Ceive\Filesystem\Exception
     */
	public function execute(){
		
		$tmpStorage = $this->getTmpStorage();
		
		$key = $tmpStorage->moveFrom($this->file, [
			FileInterface::ATTR_BASENAME  => $this->file->getBasename(),
			FileInterface::ATTR_EXTENSION => $this->file->getExtension(),
			FileInterface::ATTR_MIME_TYPE => $this->file->getMimeType()
		]);

		$result = new Result();
        $result->key        = $key;
        $result->extension  = $this->file->getExtension();
        $result->mime       = $this->file->getMimeType();
        $result->basename   = $this->file->getBasename();

		return $result;
	}
	
}


