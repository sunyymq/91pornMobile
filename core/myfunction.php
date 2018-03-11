<?php
	/*
	* 获取随机IP地址，用于伪装
	*/
	function randIp(){
		return rand(50,250).".".rand(50,250).".".rand(50,250).".".rand(50,250);
	}
	
?>