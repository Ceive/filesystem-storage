<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */

namespace Ceive\Filesystem\Storage;

use Ceive\Filesystem\File;
use Ceive\Filesystem\FS;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class StorageLinear
 * @package Ceive\Filesystem\StorageLinear
 */
class StorageLinear extends Storage implements StorageInterface{
	
	/** @var array  */
	protected $filesMetadata = [];
	
	/**
	 * @param $origin
	 * @return $this
	 */
	public function setFilesMetadata($origin){
		$this->filesMetadata = $origin;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getFilesMetadata(){
		return $this->filesMetadata;
	}
	
	
	
	
	
	/**
	 * actualize
	 */
	public function actualize(){
		foreach($this->filesMetadata as $name => $info){
			if(!$this->file_exists($info['store_as'])){
				unset($this->filesMetadata[$name]);
			}
		}
	}
	
	/**
	 * clean a storage
	 */
	public function clean(){
		$this->filesMetadata = [];
		parent::clean();
	}


    /**
     * @param $path - absolute path in local filesystem
     * @param array $origin_properties
     * @return bool
     * @throws \Ceive\Filesystem\Exception
     * @throws \Ceive\Filesystem\Exception\ExceptionAlreadyExists
     * @throws \Ceive\Filesystem\Exception\ExceptionNotExists
     * @TODO: Storage могут использовать только Adapter/Local
     * @TODO адаптировать с внешними источниками файлов помимо локальных
     */
	public function copyFrom($path, $origin_properties = []){
		if($path instanceof File){
			$file = $path;
			$path = $file->getPath();
		}
		$hash = $this->_hashFile($path);
		if($relative = $this->_storeMetadata($hash, $path, $origin_properties, 'copyRecursive')){
			$destination = $this->_pave($relative);

            if($this->file_exists($relative) && $this->_hashFile($relative) === $hash){
                return $hash;
            }

			if(isset($file)){
				$file->copyTo($destination);
			}else{
				FS::copyRecursive($path, $destination, true);
			}
		}
		return $hash;
	}

    /**
     * @param $path
     * @param array $original_properties
     * @return bool
     * @throws \Ceive\Filesystem\Exception
     */
	public function moveFrom($path, $original_properties = []){
		if($path instanceof File){
			$file = $path;
			$path = $file->getPath();
		}
		
		$hash = $this->_hashFile($path);
		if($relative = $this->_storeMetadata($hash, $path, $original_properties, 'moveRecursive')){
			$destination = $this->_pave($relative);

			if($this->file_exists($relative) && $this->_hashFile($relative) === $hash){
			    return $hash;
            }

			if(isset($file)){
				$file->moveTo($destination);
			}else{
				FS::moveRecursive($path, $destination, true);
			}
		}
		return $hash;
	}
	
	
	
	
	
	
	/**
	 * @param $hash
	 * @param $path
	 * @param $original_properties
	 * @param $type
	 * @return bool|string
	 *
	 * Добавление в метаданные
	 *
	 */
	protected function _storeMetadata($hash, $path, $original_properties, $type){
		if(!isset($this->filesMetadata[$hash])){
			$relative = $this->_injectDirectorySeparators( $hash );
			if($this->file_exists($relative)){
				$this->filesMetadata[$hash] = [
					'store_time'    => $this->getAccessTime($relative),
					'store_as'      => $relative,
					'type'          => $type,
					'path'          => $path,
					'path_info'     => array_replace([
						'mime_type'     => mime_content_type($path),
					],pathinfo($path)),
					'properties'    => $original_properties,
				];
			}else{
				$this->filesMetadata[$hash] = [
					'store_time'    => time(),
					'store_as'      => $relative,
					'type'          => $type,
					'path'          => $path,
					'source'        => array_replace([
						'mime_type'     => mime_content_type($path),
					],pathinfo($path)),
					'properties'    => $original_properties,
				];
				return $relative;
			}
		}
		return false;
	}
	
	/**
	 * @param $path
	 * @return string
	 */
	protected function _hashFile($path){
		return hash_file('md5',$path) . "." . pathinfo($path,PATHINFO_EXTENSION);
	}
	
	/**
	 * @param string $string    sdgdgdfgdfgdfgdggsdgdf
	 * @return string           sdgdg/df/gdfgd/fgdggs/dgdf
	 */
	protected function _injectDirectorySeparators($string){
		$levels = str_split(substr(crc32($string),-4));
		$l = strlen($string);
		foreach($levels as $pos){
			if($pos < $l){
				$string = substr_replace($string, DIRECTORY_SEPARATOR, $pos, 0);
			}
		}
		return trim(preg_replace('@[/\\\\]+@',DIRECTORY_SEPARATOR, $string),'\\/');
	}
	
	
	
	
	/**
	 * Absolute paths array
	 * @param $toRelative
	 * @param $dirname
	 * @param $pattern
	 * @return array Absolute paths
	 */
	protected function _traverse($toRelative, $dirname, $pattern){
		$a = [];
		foreach($this->filesMetadata as $key => $metadata){
			$path = $metadata['store_as'];
			$abs = $this->abs($metadata['store_as']);
			if(is_callable($pattern)){
				if(call_user_func($pattern, $abs, $a)){
					$_ = $this->is_dir($path)? ($abs.$this->adapter->ds()) : $abs ;
					$a[] = $toRelative? $this->re($_) : $_ ;
				}
			}elseif($pattern==='*' || fnmatch($pattern,basename($abs))){
				$_ = $this->is_dir($path)? ($abs.$this->adapter->ds()) : $abs ;
				$a[] = $toRelative? $this->re($_) : $_ ;
			}
		}
		return $a;
	}
	
	/**
	 * @param $relative
	 * @param $dirname
	 * @param $pattern
	 * @return \Generator Absolute paths
	 */
	protected function _traverseGenerator($relative,$dirname, $pattern){
		$dirname = $this->normalizePath($dirname);
		foreach(glob($dirname . DIRECTORY_SEPARATOR . '*') as $path){
			if(in_array($path,['.','..']))continue;
			
			if(!is_dir($path)){
				if($pattern==='*' || fnmatch($pattern,basename($path))){
					yield $relative?$this->re($path):$path;
				}
			}else{
				foreach($this->_traverseGenerator($relative, $path, $pattern) as $itm){
					yield $itm;
				}
			}
		}
	}
	
	/**
	 * @param $key
	 * @return bool
	 */
	public function has($key){
		return isset($this->filesMetadata[$key]);
	}
	
	/**
	 * @param $key
	 * @return mixed
	 */
	public function fileMetadata($key){
		if(isset($this->filesMetadata[$key])){
			return $this->filesMetadata[$key];
		}
		return null;
	}
	
	/**
	 * @param $path
	 * @return int|null|string
	 */
	public function fileKeyByPath($path){
		foreach($this->filesMetadata as $key => $metadata){
			if($metadata['store_as'] === $path){
				return $key;
			}
		}
		return null;
	}
	
	/**
	 * @param $key
	 * @return null
	 */
	public function filePath($key){
		return isset($this->filesMetadata[$key])?$this->filesMetadata[$key]['store_as']:null;
	}
	
	
	/**
	 * @param $key
	 * @return null
	 */
	public function filePathInfo($key){
		return isset($this->filesMetadata[$key])?$this->filesMetadata[$key]['path_info']:null;
	}
	
	/**
	 * @param $key
	 * @return null
	 */
	public function fileProperties($key){
		return isset($this->filesMetadata[$key])?$this->filesMetadata[$key]['properties']:null;
	}
	
	/**
	 * @param $key
	 * @return array|null
	 */
	public function fileExtendedProperties($key){
		return isset($this->filesMetadata[$key])?array_replace($this->filesMetadata[$key]['path_info'], $this->filesMetadata[$key]['properties']):null;
	}
	
	/**
	 * @param $key
	 * @return null
	 */
	public function fileStoreTime($key){
		return isset($this->filesMetadata[$key])?$this->filesMetadata[$key]['store_time']:null;
	}
	
	
	
}


