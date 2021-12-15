<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

use JiJiHoHoCoCo\IchiRoute\Setting\CORS;
class CORSMiddleware{

	public function handle(){

		$this->getAccessControlAllowOrigin();
		$this->getAccessControlMethods();
		$this->getAccessControlHeaders();
		header('Access-Control-Allow-Credentials: ');

		return  $this->next();
	}

	private function getAccessControlAllowOrigin(){
		$availableSites=CORS::getAvailableSites();
		if($availableSites=='*'){
			header('Access-Control-Allow-Origin: *');
		}else{
			$requestDomain=getRequestDomain();
			foreach($availableSites as $site){
				if($site==$requestDomain){
					header('Access-Control-Allow-Origin: '.$site);
					break;
				}
			}

		}
	}

	private function getAccessControlMethods(){
		return $this->getAccessControl('Access-Control-Allow-Methods',CORS::getAvailableMethods());
	}

	private function getAccessControlHeaders(){
		return $this->getAccessControl('Access-Control-Allow-Headers',CORS::getAvailableHeaders());
	}

	private function getAccessControl(string $function,$availableData){
		if($availableData=='*'){
			header($function.': '.$availableData);
		}elseif(is_array($availableData)){
			$accessData='';
			$lastData=end($availableData);
			foreach ($availableData as $key => $data){
				$accessData .= $lastData==$data ? ' '.$data: ' '.$data.',';
			}
			header($function.': '.$accessData);
		}
	}

	private function getAccessControlCredential(){
		header('Access-Control-Allow-Credentials: '.CORS::getAllowedCredential());
	}
}