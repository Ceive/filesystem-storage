<?php
/**
 * @created Alexey Kutuzov <lexus27.khv@gmail.com>
 * @Project: ceive.filesystem
 */
namespace Ceive\Filesystem\Storage;
use Ceive\Filesystem\File\UploadedFile;
use Ceive\Filesystem\FS;
use Ceive\Filesystem\Storage\Operation\OperationUpload;

include '../vendor/autoload.php';

$storage = new StorageBasic('D:\\OpenServer', true,'Windows-1251');
$factory = new OperationFactory($storage);
$root_path = isset($_GET['root'])?$_GET['root']:'';


if(isset($_POST['list'])){
	$data = array_replace([
		'path'    => null,
		'pattern' => null,
	],$_POST);
	header('Content-Type','text/json');
	$a = [];
	if($nodes = $storage->traverse(true,$data['path'],$data['pattern'])){
		foreach($nodes as $path){
			if($storage->file_exists($path)){
				$a[] = [
					'path' 		=> $path,
					'basename' 	=> basename($path),
					'size' 		=> $storage->is_dir($path)? null : $storage->filesize($path),
					'type' 		=> $storage->is_dir($path)?'dir' : 'file'
				];
			}
		}
	}
	
	
	
	echo json_encode($a,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
	
	function my_json_encode($arr,$flags=0){
		//convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
		array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
		return mb_decode_numericentity(json_encode($arr,$flags), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
		
	}
	
}else{
	?>
	
	<?
	
	if(isset($_POST['up'])){
		$operation = $factory->create([
			'class' => OperationUpload::class,
			'properties' => [
					// TODO uploaded file factory from request incapsulate
				'file' => UploadedFile::_FILES($filename),
				'destination' => '/uploads/'.$filename
			]
		]);
		$operation->execute();
	}
	
	?>
	<style>
		
		.container{
			padding-left: 20px;
			border-left: 1px solid whitesmoke;
		}
		.element{
		}
		.element > div > .name{
			font-family: Ubuntu,sans-serif;
			font-size:18px;
			padding: 3px;
		}
		.element > .file > .name{
			color: darkslateblue;
		}
		.element > .directory > .name{
			color: #9786c2;
			font-weight: 500;
			
		}
		.element > .directory > .name > .before{
			cursor: pointer;
		}
	</style>
	<section>
		<form method="post" enctype="multipart/form-data">
			<input type="hidden" name="up" value="1">
			<input type="file" name="file"/>
			<input type="submit"/>
		</form>
	</section>
	<section id="files">
		
	</section>
	<?=publicJsVariable('operations',$factory->getAutocomplete())?>
	<script>
		(function(){
			var Director = (function(){
				var c = function(el){
					this.el = el;
					this.nodes = {};
					
					this.root = new Node.Directory('<?=$root_path?:''?>',null,this);
					this.root.initialize();
					this.root.setBasename('Root');
				};
				var p = c.prototype;
				p.initialize = function(){
					this.el.appendChild(this.root.el);
					this.root.expand();
				};
				p.remove = function(node, force, listeners){
					
				};
				p.move = function(node, destination, strategy, listeners){

				};
				p.copy = function(node, destination, strategy, listeners){

				};
				p.rename = function(node, new_name, listeners){

				};
				p.createNode = function(data,parent,director){
					var node;
					if(data.type === 'dir'){
						node = new Node.Directory(data.path, parent, director);
					}else{
						node = new Node.File(data.path, parent, director);
					}
					this.nodes[data.path] = node;
					node.initialize();
					node.setBasename(data.basename);
					return node;
				};
				
				p.request = function(params, listeners){
					var properties = params.params || {};
					listeners = listeners || {};
					var method      = params.method || 'POST';
					var url         = params.url    || null;
					var headers     = params.headers || {};
					var data        = new FormData();
					for (var key in properties) {
						data.append(key,properties[key]);
					}


					var xhr = new XMLHttpRequest();
					console.log(params,properties);
					xhr.open(method, url, true);
					for(var p in headers){
						xhr.setRequestHeader(p, headers[p]);
					}

					xhr.onreadystatechange = function() {
						if (this.readyState != 4) return;
						clearTimeout(timeout);
						console.log(xhr);
						if (xhr.status == 200) {
							if(listeners.onSuccess){
								listeners.onSuccess.call(window, xhr.responseText,xhr);
							}
						} else {
							if(listeners.onFailure){
								listeners.onFailure.call(window, xhr.status, xhr.responseText,xhr);
							}
						}
						if(listeners.onComplete){
							listeners.onComplete.call(window, xhr.status, xhr.responseText,xhr);
						}
					};
					if(listeners.beforeSend){
						listeners.beforeSend.call(window, xhr);
					}
					xhr.send(data);
					var timeout = setTimeout(function(){
						xhr.abort();
						if(listeners.onFailure){
							listeners.onFailure.call(window, 0, '',xhr);
						}
						if(listeners.onComplete){
							listeners.onComplete.call(window, xhr.status, xhr.responseText,xhr);
						}
					}, 10000);
				};
				return c;
			})();
			
			var Node = (function(){
				var c = function(path,parent,director){
					this.path = path;
					this.parent = parent;
					this.director = director;
					this.el             = null;
					this.el_inner       = null;
					this.el_basename    = null;
					this.el_name        = null;
				};
				var p = c.prototype;
				p.remove = function(force, listeners){

				};
				p.move = function(destination, strategy, listeners){

				};
				p.copy = function(destination, strategy, listeners){

				};
				p.rename = function(new_name, listeners){

				};
				p.setBasename = function(value){
					this.el_basename.innerHTML = value;
					return this;
				};
				p.initialize = function(){
					var that = this;
					this.el = document.createElement('div');
					this.el.classList.add('element');
					console.log(that.path);
					this.el_inner = document.createElement('div');
					this.el_inner.innerHTML = '<div class="name"> <span class="before"></span> <span class="basename">'+that.path+'</span><span class="after"></span> </div> ';
					this.el_name = this.el_inner.querySelector('.name');
					this.el_basename = this.el_name.querySelector('.basename');
					this.el_name.addEventListener('click', function(){
						that.onClick.call(that);
					});
					this.el_basename_after = this.el_name.querySelector('.after');
					this.el_basename_before = this.el_name.querySelector('.before');
					this.el.appendChild(this.el_inner);
					this.el_inner.appendChild(this.el_name);
				};
				p.onClick = function(){};
				return c;
			})();
			
			Node.File = (function(){
				var c = function(path,parent,director){
					Node.call(this,path,parent,director);
				};
				var p = c.prototype = Object.create(Node.prototype);
				p.initialize = function(){
					Node.prototype.initialize.call(this);
					this.el_inner.classList.add('file');
				};
				return c;
			})();
			
			Node.Directory = (function(){
				var c = function(path,parent,director){
					Node.call(this,path,parent,director);
					this.children = [];
					this.el_children = null;
					this.expanded = false;
					this.loaded = false;
				};
				var p = c.prototype = Object.create(Node.prototype);
				p.initialize = function(){
					Node.prototype.initialize.call(this);
					
					this.el_basename_after.innerHTML = '/';
					this.el_basename_before.innerHTML = '';
					this.el_inner.classList.add('directory');

					this.el_children = document.createElement('div');
					this.el_children.classList.add('container');
					this.el_inner.appendChild(this.el_children);
					this.collapse();
				};
				p.add = function(node){
					node.parent = this;
					this.children.push(node);
					this.el_children.appendChild(node.el);
				};
				p.expand = function(){
					this.expanded = true;
					this.el_children.style.display = 'block';
					if(this.loaded){
						for(var i =0; i < this.children.length;i++){
							if(this.children[i] instanceof Node.Directory){
								this.children[i].collapse();
							}
						}
					}else{
						this.load();
					}
				};
				p.load = function(){
					var that = this;
					if(this.loaded) return true;
					this.loaded = true;

					this.el_children.innerHTML = '';
					this.children = [];
					
					this.director.request({
						url: '/',
						params: {
							path: this.path,
							list: 1
						}
					},{
						onSuccess: function(text){
							var array = JSON.parse(text);
							console.log('array',array);
							if(!array.length){
								
								that.el_children.innerHTML = '<i style="color:gray;">...empty...</i>';
								
								return;
							}
							
							for(var i = 0;i<array.length;i++){
								var node = that.director.createNode(array[i],that,that.director);
								that.add(node);
								console.log('add',node);
							}
						},
						beforeSend: function(){}
					});
				};
				p.collapse  = function(){
					this.expanded = false;
					console.log('collapse',this);
					this.el_children.style.display = 'none';
					
				};
				p.onClick = function(){
					console.log('click',this);
					if(this.expanded){
						this.collapse();
					}else{
						this.expand();
					}
				};
				return c;
			})();






			var director = new Director(document.getElementById('files'));
			console.log('d',director);
			director.initialize();
		})();
		
		
		
	</script>
	<?
}
function convValue($value){
	if(is_bool($value)){
		return $value?'true':'false';
	}
	if(is_scalar($value)){
		
		if(is_string($value)){
			return '\''.addcslashes($value,'\'').'\'';
		}
		
		if(is_nan($value)){
			return 'NaN';
		}
		
		if(is_integer($value) || is_float($value)){
			return "$value";
		}
		
	}
	if(is_array($value) || is_object($value)){
		return @json_encode($value,JSON_PRETTY_PRINT);
	}
	return 'null';
}
function publicJsVariable($varname, $value, $inScript = true){
	if($inScript)$a[] = '<script>';
	$a[] = ($inScript?"\t":'') . "// generated variable";
	$a[] = ($inScript?"\t":'') . "{$varname} = " . convValue($value) . ';';
	if($inScript)$a[] = '</script>';
	return implode("\r\n",$a);
};
?>