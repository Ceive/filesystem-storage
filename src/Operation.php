<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */

namespace Ceive\Filesystem\Storage;

use Ceive\Filesystem\FS;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class Operation
 * @package Ceive\Filesystem\StorageLinear
 */
abstract class Operation{
	
	
	/** @var  Storage */
	protected $storage;
	
	
	/**
	 * Operation constructor.
	 * @param $storage
	 * @param array $properties
	 */
	public function __construct(Storage $storage, array $properties){
		$this->setStorage($storage);
		$this->initializeProperties($properties);
	}
	
	/**
	 * @param Storage $storage
	 * @return $this
	 */
	public function setStorage(Storage $storage){
		$this->storage = $storage;
		return $this;
	}
	
	/**
	 * @param array $properties
	 */
	public function initializeProperties(array $properties){
		foreach(get_object_vars($this) as $p => $v){
			if(substr($p,0,1)==='_'){
				continue;
			}
			if(array_key_exists($p,$properties)){
				$this->{$p} = $properties[$p];
			}
		}
	}
	
	abstract public function execute();
	
	/**
	 * @param $path
	 * @return string
	 */
	protected function getAbsolutePath($path){
		return $this->storage->abs($path);
	}
	
	
	/**
	 * @param $strategy
	 * @return array
	 */
	public function normalizeOperationStrategy($strategy){
		list($f,$d) = FS::getStrategy($strategy);
		return [$this->_convertFileOperationStrategy($f), $this->_convertDirOperationStrategy($d)];
	}
	
	/**
	 * @param $strategy
	 * @return mixed|string
	 */
	protected function _convertFileOperationStrategy($strategy){
		$i = array_search(Storage::$_strategies_file, $strategy, true);
		if($i !== false){
			return $i;
		}else{
			return FS::DEFAULT_OP_STRATEGY_FILE;
		}
	}
	
	/**
	 * @param $strategy
	 * @return mixed|string
	 */
	protected function _convertDirOperationStrategy($strategy){
		$i = array_search(Storage::$_strategies_dir, $strategy, true);
		if($i !== false){
			return $i;
		}else{
			return FS::DEFAULT_OP_STRATEGY_DIR;
		}
	}
	
}


