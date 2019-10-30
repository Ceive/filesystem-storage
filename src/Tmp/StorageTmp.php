<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */

namespace Ceive\Filesystem\Storage\Tmp;


use Ceive\Filesystem\Exception;
use Ceive\Filesystem\FS;
use Ceive\Filesystem\Storage\StorageLinear;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class StorageTmp
 * @package Ceive\Filesystem\Storage\Storage\Tmp
 */
class StorageTmp extends StorageLinear{
	
	/** @var  integer */
	protected $lifetime;
	
	/**
	 * @param null $lifetime
	 * @return $this
	 */
	public function setLifetime($lifetime = null){
		$this->lifetime = $lifetime;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getLifetime(){
		return $this->lifetime;
	}
	
	/**
	 * @param bool $byMetadata
	 * @return void
	 */
	public function gc($byMetadata = true){
		if(($lifetime = $this->getLifetime())){
			$now = time();
			if($byMetadata){
				$this->_gcMetadata($lifetime, $now);
			}else{
				$this->_gcPhysic($lifetime, $now);
			}
		}
	}
	
	/**
	 * @param null $dirname
	 * @param null $lifetime
	 * @param null $now
	 */
	protected function _gcPhysic($lifetime = null, $now = null, $dirname = null){
		
		if($lifetime === null) $lifetime = $this->getLifetime();
		if($now === null) $now = time();
		
		foreach($this->traverse(true, $dirname) as $path){
			if($this->is_dir($path)){
				$this->_gcPhysic($lifetime, $now, $path);
			}else{
				$cTime = $this->getCreateTime($path);
				if(($cTime + $lifetime) <= $now){
					
					if($this->file_exists($path)){
						$this->unlink($path);
					}
					
					if($key = $this->fileKeyByPath($path)){
						unset($this->filesMetadata[$key]);// delete from origin state
					}
					
					$path = dirname($path);
					// remove each empty folder
					while($path && $this->isContains($path) && empty($this->nodeList($path))){
						$this->rmdir($path);
						$path = dirname($path);
					}
					
				}
			}
		}
	}
	
	/**
	 * @param null $lifetime
	 * @param null $now
	 */
	protected function _gcMetadata($lifetime = null, $now = null){
		
		if($lifetime === null) $lifetime = $this->getLifetime();
		if($now === null) $now = time();
		
		foreach($this->filesMetadata as $hash => $data){
			if($now > ($data['store_time'] + $lifetime)){
				$path = $data['store_as'];
				// remove file
				if($this->file_exists($path)){
					$this->unlink($path);
				}
				
				unset($this->filesMetadata[$hash]);// delete from origin state
				
				$path = dirname($path);
				
				// remove each empty folder
				while($path && empty($this->nodeList($path))){
					$this->rmdir($path);
					$path = dirname($path);
				}
				
			}
		}
	}


    /**
     * @param $key
     * @param $destination
     * @param bool $force
     * @throws Exception\ExceptionNotExists
     * @throws Exception
     */
	public function moveOut($key, $destination, $force = false){
		$path = $this->_injectDirectorySeparators($key);
		if($this->file_exists($path)){
			FS::moveRecursive($path, $destination , $force?FS::S_REPLACE:FS::S_REJECT);
		}else{
			throw Exception::notExists($key, 'Not existing tmp by key "'.$key.'"');
		}
	}

    /**
     * @param $key
     * @param $destination
     * @param bool $force
     * @throws Exception\ExceptionNotExists
     * @throws Exception
     */
	public function copyOut($key, $destination, $force = false){
		$path = $this->_injectDirectorySeparators($key);
		if($this->file_exists($path)){
			FS::moveRecursive($path, $destination, $force);
			unset($this->filesMetadata[$key]);
		}else{
			throw Exception::notExists($key, 'Not existing tmp by key "'.$key.'"');
		}
	}
}


