<?php

namespace JiJiHoHoCoCo\IchiRoute\Command;

use Exception;
class RouteCommand{

	private $middlewarePath='app/Middlewares';
	private $controllerPath='app/Controllers';


	private $middlewareCommandLine='make:middleware';
	private $controllerCommandLine='make:controller';

	private $resourceController=FALSE;
	private $apiResourceController=FALSE;

	private $green="\033[0;32m";
	private $red="\033[01;31m";
	private $end=" \033[0m";

	public function setMiddlewarePath(string $middlewarePath){
		$this->middlewarePath=$middlewarePath;
	}

	public function getMiddlewarePath(){
		return $this->middlewarePath;
	}

	public function setControllerPath(string $controllerPath){
		$this->controllerPath=$controllerPath;
	}

	public function getControllerPath(){
		return $this->controllerPath;
	}

	private function getNamespace(string $defaulFolder){
		return str_replace('/', '\\', ucfirst($defaulFolder));
	}

	private function makeControllerContent(string $defaulFolder,string $createdFile){
		return "<?php 

namespace ".$this->getNamespace( $defaulFolder ).";


class ".$createdFile."{

















}";
	}


	private function makeApiResourceControllerContent(string $defaulFolder,string $createdFile){
		$variable='$id';
		return  "<?php

namespace ".$this->getNamespace( $defaulFolder ).";


class ".$createdFile."{


	public function index(){


	}

	public function save(){
		

	}


	public function edit(".$variable."){


	}


	public function update(".$variable."){


	}


	public function destroy(".$variable."){


	}



}";

}

	private function makeResourceControllerContent(string $defaulFolder,string $createdFile){
		$variable='$id';
		return  "<?php

namespace ".$this->getNamespace( $defaulFolder ).";


class ".$createdFile."{


	public function index(){


	}



	public function create(){


	}

	public function save(){
		

	}


	public function edit(".$variable."){


	}


	public function update(".$variable."){


	}


	public function destroy(".$variable."){


	}



}";

}

	private function makeMiddlewareContent(string $defaulFolder,string $createdFile){
		$next='$this->next();';
		return "<?php

namespace ". $this->getNamespace( $defaulFolder ).";
use JiJiHoHoCoCo\IchiRoute\Middleware\MainMiddleware;

class ".$createdFile." extends MainMiddleware{

	public function handle(){


		return ".$next."
	}

}
";
	}

	private function checkOption(string $command){
		switch ($command) {
			case $this->middlewareCommandLine:
			return 'Middleware';
			break;
			
			case $this->controllerCommandLine:
			return 'Controller';
			break;			
			
		}
	}

	private function checkPath(string $command){
		switch ($command) {
			case $this->middlewareCommandLine:
			return $this->getMiddlewarePath();
			break;
			

			case $this->controllerCommandLine:
			return $this->getControllerPath();
			break;
		}
	}

	private function checkContent(string $command,string $defaulFolder,string $createdFile){
		switch ($command) {
			case $this->middlewareCommandLine:
			return $this->makeMiddlewareContent($defaulFolder,$createdFile);
			break;
			
			case $this->controllerCommandLine:
			if($this->resourceController==TRUE){
				return $this->makeResourceControllerContent($defaulFolder,$createdFile);
			}elseif($this->apiResourceController==TRUE){
				return $this->makeApiResourceControllerContent($defaulFolder,$createdFile);
			}else{
				return $this->makeControllerContent($defaulFolder,$createdFile);
			}
			break;
		}
	}

	public function successMessage(string $message){
		return $this->green.$message.$this->end.PHP_EOL;
	}

	public function errorMessage(string $message){
		return $this->red.$message.$this->end.PHP_EOL;
	}

	private function alreadyHave(string $createdFile,string $createdOption){
		echo $this->errorMessage($createdFile . " ".$createdOption." is already created");
		exit();
	}

	private function success(string $createdFile,string $createdOption){
		echo $this->successMessage($createdFile . " ".$createdOption." is created successfully");
		exit();
	}

	private function wrongCommand(){
		echo $this->errorMessage("You type wrong command");
		exit();
	}

	private function createError(string $createdFile,string $createdOption){
		echo $this->errorMessage("You can't create ". $createdFile . " " . $createdOption);
		exit();
	}

	public function run(string $dir,array $argv){

		if((count($argv)==3 || count($argv)==4) && ($argv[1]==$this->middlewareCommandLine || $argv[1]==$this->controllerCommandLine ) ){
			if(isset($argv[3]) && $argv[3]=='--resource' ){
				$this->resourceController=TRUE;
			}elseif(isset($argv[3]) && $argv[3]=='--api-resource' ){
				$this->apiResourceController=TRUE;
			}
			$command=$argv[1];
			$createdOption=$this->checkOption($command);
			$defaulFolder=$this->checkPath($command);
			$baseDir=$dir.'/'.$defaulFolder;
			if(substr($argv[2], -1)=='/'){
				return $this->wrongCommand();
			}
			try {
				if(!is_dir($baseDir)){
					$createdFolder=NULL;
					$basefolder=explode('/', $defaulFolder);
					foreach($basefolder as $key => $folder){
						$createdFolder .= $key == 0 ? $dir . '/' . $folder : '/' . $folder;
						if(!is_dir($createdFolder)){
							mkdir($createdFolder);
						}
					}
				}
				$inputFile=explode('/',$argv[2]);
				$count=count($inputFile);

				if($count==1 && $inputFile[0]!==NULL && !file_exists($baseDir.'/'.$inputFile[0].'.php') ){
					$createdFile=$inputFile[0];
					fopen($baseDir.'/'.$createdFile.'.php', 'w') or die('Unable to create '.$createdOption);
						$createdFileContent=$this->checkContent($command,$defaulFolder,$createdFile);
						file_put_contents($baseDir.'/'.$createdFile.'.php', $createdFileContent);
						return $this->success($createdFile,$createdOption);
				
				}elseif($count==1 && $inputFile[0]!==NULL && file_exists($baseDir . '/'.$inputFile[0].'.php') ){
					$createdFile=$inputFile[0];
				
					return $this->alreadyHave($createdFile,$createdOption);
				
				}elseif($count>1 && file_exists($baseDir.'/'. implode('/', $inputFile) . '.php' ) ){
					$createdFile=implode('/',$inputFile);
					return $this->alreadyHave($createdFile,$createdOption);
				
				}elseif($count>1 && !file_exists($baseDir .'/'. implode('/', $inputFile) . '.php' ) ){
					$createdFile=$inputFile[$count-1];
					unset($inputFile[$count-1]);
					$currentFolder=NULL;
					$newCreatedFolder=NULL;
					foreach($inputFile as $key => $folder){
						$currentFolder .= $key == 0 ? $baseDir . '/' . $folder : '/' . $folder;
						$newCreatedFolder .= $key ==0 ? $defaulFolder . '/' . $folder : '/' . $folder;
						if(!is_dir($currentFolder)){
							mkdir($currentFolder);
						}
					}

					fopen($currentFolder.'/'.$createdFile.'.php', 'w') or die('Unable to create '.$createdOption);
						$createdFileContent=$this->checkContent($command,$newCreatedFolder,$createdFile);
						file_put_contents($currentFolder.'/'.$createdFile.'.php', $createdFileContent);
						return $this->success($createdFile,$createdOption);
				}
			} catch (Exception $e) {

				return $this->createError($createdFile,$createdOption);

			}

		}
	}

}