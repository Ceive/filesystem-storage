<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */

namespace Ceive\Filesystem\Storage;
use Ceive\Filesystem\Adapter\Local;
use Ceive\Filesystem\AdapterInterface;
use Ceive\Filesystem\FS;
use Ceive\Filesystem\LocationPoint;


/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class Storage
 * @package Ceive\Filesystem\Storage\Storage
 */
abstract class Storage implements LocationPoint, AdapterInterface, StorageInterface{
	
	/** @var  Storage */
	protected $parent;
	
	/** @var  AdapterInterface */
	protected $adapter;
	
	/**
	 * StorageLinear constructor.
	 * @param $dirname
	 * @param bool $autocreate
	 * @param null $encoding
	 * @throws \Exception
	 */
	public function __construct($dirname, $autocreate = false, $encoding = null){
		$this->adapter = new Local($dirname, $autocreate, $encoding);
	}
	
	/**
	 * @param Storage $storage
	 * @return $this
	 */
	public function setParent(Storage $storage = null){
		$this->parent = $storage;
		return $this;
	}
	
	/**
	 * @return Storage
	 */
	public function getParent(){
		return $this->parent;
	}
	
	/**
	 * @Relative path
	 * @param $path - pass absolute
	 * @return string
	 * @see StorageInterface::relative
	 */
	public function re($path){
		return $this->adapter->relative($path);
	}
	
	/**
	 * @Absolute path
	 * @param $path - pass relative
	 * @return string
	 * @see StorageInterface::absolute
	 */
	public function abs($path){
		return $this->adapter->absolute($path);
	}
	
	/**
	 * @Relative
	 * @param $path - pass absolute
	 * @return string
	 * @see StorageInterface::re
	 */
	public function relative($path){
		return $this->adapter->relative($path);
	}
	/**
	 * @Absolute
	 * @param $path - pass relative
	 * @return string
	 * @see StorageInterface::abs
	 */
	public function absolute($path){
		return $this->adapter->absolute($path);
	}
	
	/**
	 * @return void
	 */
	public function clean(){
		foreach($this->adapter->nodeList('/') as $p){
			$this->adapter->deleteRecursive($p, true);
		}
	}
	
	public function deleteRecursive($path, $force = false){
		return $this->adapter->deleteRecursive($path,$force);
	}
	
	public function moveRecursive($path, $destination, $strategy = false){
		return $this->adapter->moveRecursive($path,$destination,$strategy);
	}
	
	public function copyRecursive($path, $destination, $strategy = false){
		return $this->adapter->copyRecursive($path,$destination, $strategy);
	}
	
	public function chmodRecursive($path, $permissions = 0777){
		return $this->adapter->chmodRecursive($path,$permissions);
	}
	
	public function getSize($path=null){
		return $this->adapter->getSize($path);
	}
	
	
	/**
	 * @param string $path
	 * @param bool $orIsStorage
	 * @return bool
	 */
	public function isContains($path, $orIsStorage = false){
		$dirname = $this->adapter->getRoot();
		$path = FS::path($this->adapter->ds(),$path);
		if($orIsStorage && $path === ''){
			return true;
		}
		return FS::isContains($dirname, $this->abs($path) );
	}
	
	/**
	 * @param bool $relative
	 * @param null $dirname
	 * @param null $pattern
	 * @return array
	 */
	public function traverse($relative = false, $dirname = null, $pattern=null){
		if($pattern===null)$pattern = '*';
		$a = $this->_traverse($relative, $dirname, $pattern);
		usort($a,function($a,$b){
			if($this->is_dir($a) && $this->is_dir($b)){
				return strnatcmp($a,$b);
			}
			return $this->is_dir($a)?-1:1;
		});
		return $a;
	}
	
	/**
	 * @param bool $relative
	 * @param null $dirname
	 * @param null $pattern
	 * @return \Generator
	 */
	public function traverseGenerator($relative = false,$dirname = null, $pattern=null){
		if($pattern===null)$pattern = '*';
		$dirname = $this->abs(FS::path('',$dirname));
		if($this->isContains($dirname, true)){
			return $this->_traverseGenerator($relative, $dirname, $pattern);
		}else{
			return null;
		}
	}
	
	protected function _matchPattern($pattern, $relativePath, $absPath, array $a = null){
		if(is_callable($pattern)){
			if(call_user_func($pattern, $absPath, $a)){
				return true;
			}
		}elseif($pattern==='*' || fnmatch($pattern,basename($absPath))){
			return true;
		}
		return false;
	}
	
	/**
	 * @param $toRelative
	 * @param $dirname
	 * @param $pattern
	 * @return array Absolute paths
	 */
	protected function _traverse($toRelative,$dirname, $pattern){
		$a = [];
		$dirname = FS::path($this->adapter->ds(), $dirname, '*');
		foreach($this->nodeListMatch($dirname) as $relativePath){
			$abs = $this->abs($relativePath);
			if($this->_matchPattern($pattern, $relativePath, $abs, $a)){
				
				$isDir = $this->is_dir($relativePath);
				if($toRelative){
					$result = $isDir? ($relativePath . $this->adapter->ds()) : $relativePath ;
				}else{
					$result = $isDir? ($abs . $this->adapter->ds()) : $abs ;
				}
				
				$a[] = $result;
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
		$dirname = FS::path($this->adapter->ds(), $dirname, '*');
		foreach($this->nodeListMatch($dirname) as $path){
			$abs = $this->abs($path);
			if($this->_matchPattern($pattern, $path, $abs, $a)){
				
				$isDir = $this->is_dir($path);
				if($relative){
					$result = $isDir? $relative . $this->adapter->ds() : $relative ;
				}else{
					$result = $isDir? $abs . $this->adapter->ds() : $abs ;
				}
				
				yield $result;
			}
		}
	}
	
	
	/**
	 * @param $path
	 * @return string
	 */
	public function normalizePath($path){
		return rtrim($path,'\\/');
	}
	
	public function getAdapter(){
		return $this->adapter;
	}
	
	/**
	 * @param $relative_path
	 * @return string
	 */
	protected function _pave($relative_path){
		$dir = dirname($relative_path);
		if($dir && !$this->is_dir($dir) && !in_array($dir,['.','..'])){
			$this->mkdir($dir, 0777, true);
		}
		return $this->abs($relative_path);
	}
	
	
	
	
	
	public function getEncoding(){
		return $this->adapter->getEncoding();
	}
	
	public function getDriveLetter(){
		return $this->adapter->getDriveLetter();
	}
	
	public function filesize($path){
		return $this->adapter->filesize($path);
	}
	
	public function disk_free_space($path){
		return $this->adapter->disk_free_space($path);
	}
	
	public function disk_total_space($path){
		return $this->adapter->disk_total_space($path);
	}
	
	public function touch($path, $modifyTime = null, $accessTime = null){
		return $this->adapter->touch($path, $modifyTime = null, $accessTime = null);
	}
	
	public function getAccessTime($path){
		return $this->adapter->getAccessTime($path);
	}
	
	public function getModifyTime($path){
		return $this->adapter->getModifyTime($path);
	}
	
	public function getCreateTime($path){
		return $this->adapter->getCreateTime($path);
	}
	
	public function is_link($path){
		return $this->adapter->is_link($path);
	}
	
	public function is_dir($path){
		return $this->adapter->is_dir($path);
	}
	
	public function is_file($path){
		return $this->adapter->is_file($path);
	}
	
	public function is_readable($path){
		return $this->adapter->is_readable($path);
	}
	
	public function is_writable($path){
		return $this->adapter->is_writable($path);
	}
	
	public function is_executable($path){
		return $this->adapter->is_executable($path);
	}
	
	public function fileperms($path){
		return $this->adapter->fileperms($path);
	}
	
	public function fileowner($path){
		return $this->adapter->fileowner($path);
	}
	
	public function file_exists($path){
		return $this->adapter->file_exists($path);
	}
	
	public function unlink($path){
		return $this->adapter->unlink($path);
	}
	
	public function mkdir($path, $mod = 0777, $recursive = false){
		return $this->adapter->mkdir($path, $mod, $recursive);
	}
	
	public function mkfile($path){
		return $this->adapter->mkfile($path);
	}
	
	public function rmdir($path){
		return $this->adapter->rmdir($path);
	}
	
	public function changeOwner($path, $owner){
		return $this->adapter->changeOwner($path, $owner);
	}
	
	public function changePermissions($path, $mod){
		return $this->adapter->changePermissions($path, $mod);
	}
	
	public function changeGroup($path, $group){
		return $this->adapter->changeGroup($path, $group);
	}
	
	public function rename($path, $newPath){
		return $this->adapter->rename($path, $newPath);
	}
	
	public function copy($path, $destination){
		return $this->adapter->copy($path, $destination);
	}
	
	public function nodeList($path){
		return $this->adapter->nodeList($path);
	}
	
	public function nodeListMatch($pattern){
		return $this->adapter->nodeListMatch($pattern);
	}
	
	public function fileGetContents($filePath){
		return $this->adapter->fileGetContents($filePath);
	}
	
	public function filePutContents($filePath, $content){
		return $this->adapter->filePutContents($filePath, $content);
	}
	
	
}


