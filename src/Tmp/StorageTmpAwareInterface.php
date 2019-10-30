<?php
/**
 * @Creator Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Author: Alexey Kutuzov <lexus27.khv@gmai.com>
 * @Project: ceive.filesystem-storage
 */

namespace Ceive\Filesystem\Storage\Tmp;

/**
 * @Author: Alexey Kutuzov <lexus.1995@mail.ru>
 * Interface StorageTmpAwareInterface
 * @package Ceive\Filesystem\Storage\Tmp
 */
interface StorageTmpAwareInterface{
	
	/**
	 * @return StorageTmp
	 */
	public function getTmpStorage();
	
}

