<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

use JiJiHoHoCoCo\IchiRoute\Setting\CORS;
class CORSMiddleware extends MainMiddleware{

	public function handle(){

		$this->getAccessControlAllowOrigin();
		$this->getAccessControlMethods();
		$this->getAccessControlHeaders();
		$this->getAccessControlCredential();
		$this->getAccessControlMaxAge();

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
			$availableSitesRegex=CORS::getAvailableSitesRegex();
			if($availableSitesRegex!==NULL && preg_match($availableSitesRegex, $requestDomain) ){
				header('Access-Control-Allow-Origin: '.$requestDomain);
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

	private function getAccessControlMaxAge(){
		$age=CORS::getMaxAge();
		if($age>0){
			header('Access-Control-Max-Age: '.$age);
		}
	}
}