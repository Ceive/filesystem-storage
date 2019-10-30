<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */

namespace Ceive\Filesystem\Storage\Operation;

use Ceive\Filesystem\File\UploadedFile;
use Ceive\Filesystem\Storage\Operation;

/**
 * должен иметь доступ к
 * Презагрузкам и к Нативному Предварительному Хранилищу
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class OperationUpload
 * @package Ceive\Filesystem\StorageLinear\Operation
 */
class OperationUpload extends Operation{
	
	/** @var  UploadedFile */
	protected $file;
	
	protected $destination;

    /**
     * @return bool
     * @throws \Ceive\Filesystem\Exception
     */
	public function execute(){
		$destinationPath = $this->storage->getAdapter()->internal($this->destination);
		$this->file->moveTo($destinationPath, true);
		return true;
	}
	
	/**
	 * @return string
	 */
	public function getDestinationPath(){
		return $this->getAbsolutePath($this->destination);
	}
	
}


