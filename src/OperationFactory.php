<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */

namespace Ceive\Filesystem\Storage;
use Ceive\Filesystem\Storage\Operation\OperationCopy;
use Ceive\Filesystem\Storage\Operation\OperationDelete;
use Ceive\Filesystem\Storage\Operation\OperationMove;
use Ceive\Filesystem\Storage\Operation\OperationRename;
use Ceive\Filesystem\Storage\Operation\OperationSave;
use Ceive\Filesystem\Storage\Operation\OperationUpload;
use Ceive\Filesystem\Storage\Operation\OperationUploadTmp;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class OperationFactory
 * @package Ceive\Filesystem\Storage\Storage
 */
class OperationFactory{
	
	/**
	 * @var Storage
	 */
	private $storage;
	
	public function __construct(Storage $storage){
		
		$this->storage = $storage;
	}
	
	/**
	 * @param $definition
	 *
	 * class: null || Operation::class,
	 * properties: []
	 *
	 * @return Operation
	 */
	public function create($definition){
		
		if(is_array($definition)){
			
			$definition = array_replace_recursive([
				'class'      => null,
				'properties' => []
			],$definition);
			
			
			if(isset($definition['class'])){
				if(class_exists($definition['class'])){
					/** @var Operation $object */
					$c = $definition['class'];
					$object = new $c($this->storage, $definition['properties']);
					return $object;
				}
				
			}
			
		}
		return null;
	}
	
	/**
	 * @return array
	 * @IDEA autocomplete see ceive/data-autocomplete and ceive/dsi
	 */
	public function getAutocomplete(){
		
		return [
			// Удаление
			OperationDelete::class => [
				'path' => 'string',
				'force' => 'bool',
			],
			// Копирование
			OperationCopy::class => [
				'path' => 'string',
				'destination' => 'string',
				'strategy' => [
					'-string|null',
					'-array' => [ 'string|null', 'string|null' ]
				],
			],
			// Перемещение
			OperationMove::class => [
				'path'          => 'string',
				'destination'   => 'string',
				'strategy'      => [
					'-string|null',
					'-array' => [ 'string|null', 'string|null' ]
				],
			],
			// Переименование
			OperationRename::class => [
				'path'      => 'string',
				'new_name'  => 'string',
				'force'     => 'bool',
			],
			// Загрузка на сервер
			OperationUpload::class => [
				'file'          => 'string',
				'destination'   => 'string',
			],
			// Предварительная загрузка на сервер
			OperationUploadTmp::class => [
				'file'  => 'string',
			],
			// Сохранение предварительно загруженного файла
			OperationSave::class => [
				'tmp'           => 'string',
				'destination'   => 'string',
			],
		];
		
	}
	
}


