<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */

namespace Ceive\Filesystem\Storage\Operation;

use Ceive\Filesystem\FS;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class OperationRename
 * @package Ceive\Filesystem\StorageLinear\Operation
 */
class OperationRename extends OperationExistingAware{
	
	/** @var  string */
	protected $new_name;
	
	protected $force = false;
	
	public function execute(){
		$path = $this->getAbsolutePath($this->path);
		if(file_exists($path)){
			$new_path = dirname($path) . DIRECTORY_SEPARATOR . $this->new_name;
			FS::moveRecursive($path, $new_path, $this->force);
		}
	}
}


