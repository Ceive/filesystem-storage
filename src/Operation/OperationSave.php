<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */

namespace Ceive\Filesystem\Storage\Operation;

use Ceive\Filesystem\Exception;
use Ceive\Filesystem\FS;
use Ceive\Filesystem\Storage\Operation;
use Ceive\Filesystem\Storage\Tmp\StorageTmp;

/**
 * Работа с предзагрузкой
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class OperationSave
 * @package Ceive\Filesystem\StorageLinear\Operation
 */
class OperationSave extends OperationTmpRequires{
	
	protected $tmp;
	
	protected $destination;
	
	protected $force = false;
	protected $copy  = false;
	
	/**
	 * @throws \Exception
	 */
	public function execute(){
		$tmpStorage = $this->getTmpStorage();
		if($tmpStorage->has($this->tmp)){
            $tmpStorage->fileMetadata($this->tmp);
			$destination = $this->storage->abs($this->destination);

			if($this->copy){
                $tmpStorage->copyOut($this->tmp, $destination, $this->force);
            }else{
                $tmpStorage->moveOut($this->tmp, $destination, $this->force);
            }

			return $this->destination;
		}else{
			throw Exception::notExists($this->tmp, "Temporal file by '{$this->tmp}' is not found");
		}
	}
	
}


