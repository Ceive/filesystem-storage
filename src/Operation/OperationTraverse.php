<?php
/**
 * @Creator Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Author: Alexey Kutuzov <lexus27.khv@gmai.com>
 * @Project: ceive.filesystem-storage
 */

namespace Ceive\Filesystem\Storage\Operation;


use Ceive\Filesystem\Storage\Operation;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class OperationTraverse
 * @package Ceive\Filesystem\Storage\Operation
 *
 * @MIND:
 * @TODO: Collection
 * @TODO represent as objects
 */
abstract class OperationTraverse extends OperationExistingAware{
	
	public $pattern;
	
	public function execute(){
		$s = $this->storage;
		
		if(!$s->is_dir($this->path)){
			
		}
		$a = [];
		if($nodes = $s->traverse(true, $this->path, $this->pattern)){
			foreach($nodes as $path){
				if($s->file_exists($path)){
					// TODO: filesystem entity properties interface (DSI)
					$a[] = [
						'path' 		=> $path,
						'basename' 	=> basename($path),
						'size' 		=> $s->is_dir($path)? null : $s->filesize($path),
						'type' 		=> $s->is_dir($path)?'dir' : 'file'
					];
				}
			}
		}
		
		
		
		
	}
	
}


