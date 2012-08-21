<?php
class storage{
	protected $name;
	protected $type;
	protected $storageHandler;
	
	public function __construct($name,$type,$connection,$register=true){
		if(!empty($name)&&!empty($type)&&(is_array($connection)||is_string($connection))){
			$this->name=$name;
			$this->type=$type;
			try{
				switch($type){
					case 'file':
					case 'fileRaw':
						/*
							new storage('name','file',array('file'=>'store/public/sweetcat.jpg','mode'=>'wb','options'=>array()));
							new storage('name','file',array('store/public/sweetcat.jpg','wb',array()));
							new storage('name','file','store/public/sweetcat.jpg');
						*/
						if(is_array($connection))
						{
							if(isset($connection['options']) && count($connection['options']))
								$options = $connection['options'];
							elseif(isset($connection[2]) && count($connection[2]))
								$options = $connection[2];
							else
								$options = false;
							
							if((isset($connection['file'])&&isset($connection['mode']))&&(is_string($connection['file'])&&is_string($connection['mode']))){
								$this->storageHandler = new fileRawHandler($connection['file'],$connection['mode'],false,$options);
								break;
							}
							elseif(is_string($connection[0])&&is_string($connection[1])){
								$this->storageHandler = new fileRawHandler($connection[0],$connection[1],false,$options);
								break;
							}
							else
								throw new StorageException("Attempt to create storage-Object with wrong parameters");
							break;
						}
						$this->storageHandler=new fileRawHandler($connection);
						break;
					case 'fileConfig':
						$this->storageHandler=new fileConfigHandler($connection);
						break;
					case 'fileCache':
					case 'dirCache':
					case 'publicDir':
					case 'protectedDir':
					case 'dir':
						$this->storageHandler=dir($connection);
						break;
					default:
						throw new StorageException("Attempt to create storage-Object with wrong parameters");
				}
			}
			catch(StorageException $e){
				throw new StorageException("Couldn't create storageHandler for new storage '".$name."': ".$e->getMessage());
			}
			if($this->storageHandler){
				if($register)// we shouldn't call system::SM() in some situations
					if(!system::SM()->registerStorage($this))
						throw new StorageException("Couldn't register new storage '".$name."' at storageManager");
			}
			else{
				$e=new UnexpectedValueException();
				system::LOG()->wtf("storage","Missing storageHandler for new storage '".$name."'! How should I register a NULL-Pointer?",500,$e);
				throw $e;
			}
		}
		else
			throw new StorageException("Attempt to create storage-Object with wrong parameters");
	}
	public final function getName(){
		return $this->name;
	}
	public final function getType(){
		return $this->type;
	}
	public final function getHandler(){
		return $this->storageHandler;
	}
	public final function close(){
		$this->storageHandler->close();
		unset($this);
	}
}
?>