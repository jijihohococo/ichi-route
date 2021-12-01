<?php

namespace JiJiHoHoCoCo\IchiRoute\UI;

class NotFound{
	public function show(string $message='404 - URL is not found',int $code=404){
		http_response_code($code);
		return <<<HTML
		<html>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
		<title>$message</title>
		<body style="background-color: black;">
		<div style="position: fixed;top:50%;left:50%;transform: translate(-50%, -50%);color:white;font-family: 'Nunito', sans-serif;">
		<p>$message</p>
		</div>
		</body>
		</html>
		HTML;
	}
}