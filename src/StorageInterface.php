<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */

namespace Ceive\Filesystem\Storage;

/**
 * @Author: Alexey Kutuzov <lexus.1995@mail.ru>
 * Interface StorageInterface
 * @package Ceive\Filesystem\StorageLinear
 */
interface StorageInterface{
	
	/**
	 * @param bool $relative
	 * @param $dirname
	 * @return mixed
	 */
	public function traverse($relative = false,$dirname=null);
	
	/**
	 * @param bool $relative
	 * @param $dirname
	 * @return \Generator
	 */
	public function traverseGenerator($relative = false,$dirname=null);
	
	/**
	 * @param string $path absolute
	 * @return bool
	 */
	public function isContains($path);
	
	/**
	 * @return void
	 */
	public function clean();
	
}

