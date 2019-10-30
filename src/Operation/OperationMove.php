<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */

namespace Ceive\Filesystem\Storage\Operation;

use Ceive\Filesystem\FS;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class OperationMove
 * @package Ceive\Filesystem\StorageLinear\Operation
 */
class OperationMove extends OperationExistingAware{
	
	protected $destination;
	
	protected $strategy;
	
	public function initializeProperties(array $properties){
		parent::initializeProperties($properties);
		$this->strategy = $this->normalizeOperationStrategy($this->strategy);
	}
	
	public function execute(){
		$path = $this->getAbsolutePath($this->path);
		$destination = $this->getDestinationPath();
		if(file_exists($path)){
			FS::moveRecursive($path, $destination, $this->strategy);
		}
	}
	
	public function getDestinationPath(){
		return $this->getAbsolutePath($this->destination);
	}
	
}


