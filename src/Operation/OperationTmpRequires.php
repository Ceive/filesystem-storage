<?php
/**
 * @Creator Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Author: Alexey Kutuzov <lexus27.khv@gmai.com>
 * @Project: ceive.filesystem-storage
 */

namespace Ceive\Filesystem\Storage\Operation;


use Ceive\Filesystem\Storage\Operation;
use Ceive\Filesystem\Storage\Storage;
use Ceive\Filesystem\Storage\Tmp\StorageTmp;
use Ceive\Filesystem\Storage\Tmp\StorageTmpAwareInterface;

abstract class OperationTmpRequires extends Operation implements StorageTmpAwareInterface{
	
	/** @var  StorageTmp|StorageTmpAwareInterface */
	protected $storage;
	
	/**
	 * @param Storage $storage
	 * @return $this
	 */
	public function setStorage(Storage $storage){
		
		if(!$storage instanceof StorageTmpAwareInterface || !$storage instanceof StorageTmp){
			throw new \InvalidArgumentException('Parameter $storage, must be instance of"' . StorageTmpAwareInterface::class . '" OR "' . StorageTmp::class . '"');
		}
		
		parent::setStorage($storage);
		
		return $this;
	}
	
	/**
	 * @return Storage|StorageTmp
	 */
	public function getTmpStorage(){
		return $this->storage instanceof StorageTmp?$this->storage: $this->storage->getTmpStorage() ;
	}
	
}


