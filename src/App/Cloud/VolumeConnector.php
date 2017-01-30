<?php

namespace App\Cloud;

class VolumeConnector
{
	protected $oExplorerObject;
	protected $aOptions;
	protected $sHeader = 'Content-Type: application/json';
	
	public function __construct($explorer, $debug = false)
	{
		$this->oExplorerObject = $explorer;
		if($debug) {
			$this->sHeader = 'Content-Type: text/html; charset=utf-8';
		}
	}
}