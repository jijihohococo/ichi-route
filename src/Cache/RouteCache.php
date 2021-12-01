<?php

namespace JiJiHoHoCoCo\IchiRoute\Cache;
use PDO;
class RouteCache{

	public $pdo,$pdoCachedTime;

	public function __construct(PDO $pdo,int $pdoCachedTime=NULL){
		$this->pdo=$pdo;
		$this->pdoCachedTime=$pdoCachedTime;
	}

	public function set(string $routeKey,string $routeMethod,int $expiredTime=NULL){

		$expiredTime=$expiredTime!==NULL ? addExpiredDateTime($expiredTime) : $expiredTime;
		$currentDateTime=date('Y-m-d H:i:s');
		$stmt=$this->pdo->prepare("INSERT INTO ichi_routes (route_key, route_method,expired_time,created_time) VALUES (?, ?, ?, ?)");
		$stmt->bindParam(1,$routeKey);
		$stmt->bindParam(2,$routeMethod);
		$stmt->bindParam(3,$expiredTime);
		$stmt->bindParam(4,$currentDateTime);
		$stmt->execute();
	}

	private function update(string $routeKey){
		$currentDateTime=date('Y-m-d H:i:s');
		$expiredTime=$this->pdoCachedTime!==NULL ? addExpiredDateTime($this->pdoCachedTime) : $this->pdoCachedTime;
		$stmt=$this->pdo->prepare("UPDATE ichi_routes SET expired_time = ? ,
			updated_time = ? WHERE route_key = ?");
		$stmt->bindParam(1,$expiredTime);
		$stmt->bindParam(2,$currentDateTime);
		$stmt->bindParam(3,$routeKey);
		$stmt->execute();
	}

	public function get(string $routeKey){
		$currentDateTime=date('Y-m-d H:i:s');
		$stmt=$this->pdo->prepare("SELECT route_method,CASE WHEN expired_time = NULL THEN 'route_method_available' WHEN expired_time > '".$currentDateTime."' THEN 'route_method_available' WHEN expired_time < '".$currentDateTime."' THEN 'route_method_unavailable' END AS route_mode  FROM ichi_routes WHERE route_key = ? LIMIT 1");
		$stmt->bindParam(1,$routeKey);
		$stmt->execute();
		$objectArray=$stmt->fetchAll(PDO::FETCH_ASSOC);
		if(isset($objectArray[0])){
			if($objectArray[0]['route_mode']=='route_method_unavailable'){
				$this->update($routeKey);
			}
			return $objectArray[0]['route_method'];
		}
		return NULL;
	}

	public function delete(string $routeKey){
		$stmt=$this->pdo->prepare("DELETE FROM ichi_routes WHERE route_key = ?");
		$stmt->bindParam(1,$routeKey);
		$stmt->execute();
	}
}