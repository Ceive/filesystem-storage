<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */

namespace Ceive\Filesystem\Storage\Tests;

use Ceive\Filesystem\FS;
use Ceive\Filesystem\Storage\Operation;
use Ceive\Filesystem\Storage\Operation\OperationCopy;
use Ceive\Filesystem\Storage\Operation\OperationDelete;
use Ceive\Filesystem\Storage\Operation\OperationMove;
use Ceive\Filesystem\Storage\Operation\OperationRename;
use Ceive\Filesystem\Storage\Operation\OperationSave;
use Ceive\Filesystem\Storage\Operation\OperationUpload;
use Ceive\Filesystem\Storage\Operation\OperationUploadTmp;
use Ceive\Filesystem\Storage\OperationFactory;
use Ceive\Filesystem\Storage\Storage;
use Ceive\Filesystem\Storage\StorageBasic;
use Ceive\Filesystem\Storage\StorageComposition;
use Ceive\Filesystem\Storage\StorageLinear;
use Ceive\Filesystem\Storage\Tmp\StorageTmp;

/**
 * @Author: Alexey Kutuzov <lexus27.khv@gmail.com>
 * Class StorageTestCase
 * @package Ceive\Filesystem\Tests
 */
class StorageTestCase extends \PHPUnit_Framework_TestCase{
	
	
	public $dirname;
	public $source_dirname;
	
	/** @var  Storage */
	public $storage;
	
	/** @var  StorageTmp */
	public $storageTmp;
	
	/** @var  OperationFactory */
	public $opFactory;
	
	public function setUp(){
		$this->source_dirname   = __DIR__ . DIRECTORY_SEPARATOR . 'my_filesystem';
		$this->dirname          = __DIR__ . DIRECTORY_SEPARATOR . 'my_storage';
		
		$this->storage = new StorageBasic($this->dirname, true);
		$this->storageTmp = new StorageTmp($this->storage->abs('.tmp'), true);
		
		$this->opFactory = new OperationFactory($this->storage);
		
	}



    public function testUserContainer(){


	    $container = new StorageBasic( __DIR__ . DIRECTORY_SEPARATOR . 'user_210', true);
	    $tmp = new StorageTmp(__DIR__ . DIRECTORY_SEPARATOR . ".tmp", true);

        $container->setTmpStorage($tmp);

        $opFactory = new OperationFactory($container);

        $opFactory->create([

        ]);

        $this->source_dirname   = __DIR__ . DIRECTORY_SEPARATOR . 'my_filesystem';
        $this->dirname          = __DIR__ . DIRECTORY_SEPARATOR . 'my_storage';

        $this->storage =
        $this->storageTmp =

        $this->opFactory = new OperationFactory($this->storage);

    }

	public function testStorage(){
		
		$lifetime = 10; // in sec.
		
		$this->storageTmp->setLifetime($lifetime);
		
		$i = 0;
		$tmpKey = null;
		while(true){
			switch($i){
				
					
				case 0:
					$this->assertEquals(false, isset($tmpKey));
					
					$tmpKey = $this->storageTmp->copyFrom(FS::path(null,$this->source_dirname,'pro100','cg.dll'));
					break;
					
				default:
					// $tmpKey is defined
					$this->assertEquals(true, isset($tmpKey));
					// existing tmp file by key $tmpKey
					$this->assertEquals(true, $this->storageTmp->has($tmpKey));
					
					break;
					
				case 10:
					//before gc
					$this->assertEquals(true, $this->storageTmp->has($tmpKey));
					// garbage collect
					$this->storageTmp->gc(false);
					//after gc
					$this->assertEquals(false, $this->storageTmp->has($tmpKey));
					
					break;
				
				case 11:
					//after gc, after 1 sec
					$this->assertEquals(false, $this->storageTmp->has($tmpKey));
					
					break(2);
			}
			sleep(1);
			$i++;
		}
		
		
	}
	
	
	public function testFunctionality(){
		
		$traverse = $this->storage->traverse();
		
		$strategy = [
			'file'      => [
				'replace',
				
				'reject',
				'keep',
			],
			'directory' => [
				'replace',
				'merge',
				
				'reject',
				'keep',
			],
		];
		
		/**
		 * @see OperationUpload::class,
		 * @see OperationUploadTmp::class,
		 * @see OperationSave::class,
		 * @see OperationCopy::class,
		 * @see OperationDelete::class,
		 * @see OperationMove::class,
		 * @see OperationRename::class,
		 *
		 * return [
		 *      // Удаление
		 *      OperationDelete::class => [
		 *      	'path' => 'string',
		 *      	'force' => 'bool',
		 *      ],
		 *      // Копирование
		 *      OperationCopy::class => [
		 *      	'path' => 'string',
		 *      	'destination' => 'string',
		 *      	'strategy' => [
		 *      		'-string|null',
		 *      		'-array' => [ 'string|null', 'string|null' ]
		 *      	],
		 *      ],
		 *      // Перемещение
		 *      OperationMove::class => [
		 *      	'path'          => 'string',
		 *      	'destination'   => 'string',
		 *      	'strategy'      => [
		 *      		'-string|null',
		 *      		'-array' => [ 'string|null', 'string|null' ]
		 *      	],
		 *      ],
		 *      // Переименование
		 *      OperationRename::class => [
		 *      	'path'      => 'string',
		 *      	'new_name'  => 'string',
		 *      	'force'     => 'bool',
		 *      ],
		 *      // Загрузка на сервер
		 *      OperationUpload::class => [
		 *      	'file'          => 'string',
		 *      	'destination'   => 'string',
		 *      ],
		 *      // Предварительная загрузка на сервер
		 *      OperationUploadTmp::class => [
		 *      	'file'  => 'string',
		 *      ],
		 *      // Сохранение предварительно загруженного файла
		 *      OperationSave::class => [
		 *      	'tmp'           => 'string',
		 *      	'destination'   => 'string',
		 *      ],
		 * ];
		 */
		
		
		$opUpload = $this->opFactory->create([
			'class' => OperationUpload::class,
			'properties' => [
				'file'          => '',
				'destination'   => '',
			],
		]);
		$opUploadTmp = $this->opFactory->create([
			'class' => OperationUploadTmp::class,
			'properties' => [
				'file'          => '',
			],
		]);
		$opSave = $this->opFactory->create([
			'class' => OperationSave::class,
			'properties' => [
				'tmp'           => '',
				'destination'   => '',
			],
		]);
		
		$opRename = $this->opFactory->create([
			'class' => OperationRename::class,
			'properties' => [
				'path'          => 'string',
		       	'new_name'      => 'string', // new basename
		       	'force'         => false,
			],
		]);
		
		$opMove = $this->opFactory->create([
			'class' => OperationMove::class,
			'properties' => [
				'path'          => 'string',
		       	'destination'   => 'string',
		       	'strategy'      => [ FS::S_KEEP, FS::S_KEEP ],
			],
		]);
		
		$opCopy = $this->opFactory->create([
			'class' => OperationCopy::class,
			'properties' => [
				'path'          => 'string',
				'destination'   => 'string',
				'strategy'      => [ FS::S_KEEP, FS::S_KEEP ],
			],
		]);
		
		$opDelete = $this->opFactory->create([
			'class' => OperationDelete::class,
			'properties' => [
				'path'          => 'string',
				'force'         => false,
			],
		]);
		
		$operations = [
			$opUpload,
			
			$opUploadTmp,
			$opSave,
			
			$opRename,
			
			$opMove,    // preferred for [low level utility]
			$opCopy,    // preferred for [low level utility]
			$opDelete   // preferred for [low level utility]
			
			/**
			 * todo solve @see OperationRead
			 * todo solve @see OperationTraverse
			 * */
		];


		$uploadsStorage = new StorageComposition("/uploads");
        $uploadsStorage->addStorage('cars', new StorageComposition($uploadsStorage->abs("/cars"), true));

        $tmpStorage = new StorageTmp($uploadsStorage->abs("/.tmp"));
        $uploadsStorage->addStorage(".tmp", $tmpStorage);

        $uploadsStorage->setTmpStorage($tmpStorage);

        $carsStorage = $uploadsStorage->getStorage("cars");

        $recordStorage = new StorageBasic($carsStorage->abs("/car_{$this->id}"), true);
        $carsStorage->addStorage("car_{$this->id}", $recordStorage);

        $tmpStorage->moveOut();

	}
	
}


