<?php
/**
 * @Creator Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Author: Alexey Kutuzov <lexus27.khv@gmai.com>
 * @Project: ceive.filesystem-storage
 */

namespace Ceive\Filesystem\Storage;
use Ceive\Filesystem\FS;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class StorageComposition
 * @package Ceive\Filesystem\Storage
 */
class StorageComposition extends Storage{
	
	/** @var Storage[]  */
	protected $stores = [];
	
	/** @var string[] - relative paths  */
	protected $ignore = [];
	
	/**
	 * @param $relativePath
	 * @return bool
	 */
	protected function beforeAccess($relativePath){
		foreach($this->ignore as $path){
			if($path === $relativePath || FS::isContains($this->abs($path), $this->abs($relativePath))){
				return false;
			}
		}
		return true;
	}
	
	/**
	 * @param $pattern
	 * @param $relativePath
	 * @param $absPath
	 * @param array|null $a
	 * @return bool
	 */
	protected function _matchPattern($pattern, $relativePath, $absPath, array $a = null){
		if(!$this->beforeAccess($relativePath)){
			return false;
		}
		return parent::_matchPattern($pattern, $relativePath, $absPath, $a);
	}
	
	/**
	 * @param $path
	 * @param bool $force
	 * @return bool
	 */
	public function deleteRecursive($path, $force = false){
		if($this->beforeAccess($path)){
			return parent::deleteRecursive($path, $force);
		}
		return false;
	}
	
	
	/**
	 * @return void
	 */
	public function clean(){
		
		foreach($this->stores as $store){
			$store->clean();
		}
		
		foreach($this->adapter->nodeList('/') as $p){
			if($this->beforeAccess($p)){
				$this->deleteRecursive($p, true);
			}
		}
	}
	
	
	/**
	 * @param $key
	 * @param Storage $storage
	 * @param bool $isolateAccess
	 * @return $this
	 */
	public function addStorage($key, Storage $storage, $isolateAccess = true){
		$storage->setParent($this);
		$this->stores[$key] = $storage;
		
		if($isolateAccess){
			$this->ignore[] = $this->relative($storage->adapter->getRoot());
		}
		
		return $this;
	}
	
	/**
	 * @param $key
	 * @return Storage|null
	 */
	public function getStorage($key){
		return isset($this->stores[$key])?$this->stores[$key]:null;
	}
	
}


