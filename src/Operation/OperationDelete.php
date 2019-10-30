<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */

namespace Ceive\Filesystem\Storage\Operation;

use Ceive\Filesystem\FS;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class OperationDelete
 * @package Ceive\Filesystem\StorageLinear\Operation
 */
class OperationDelete extends OperationExistingAware{
	
	protected $strategy;
	
	protected $force;
	
	/**
	 *
	 */
	public function execute(){
		$path = $this->getAbsolutePath($this->path);
		if(file_exists($path)){
			FS::deleteRecursive($path, $this->force);
		}
	}
}


