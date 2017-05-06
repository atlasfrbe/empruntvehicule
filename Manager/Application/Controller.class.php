<?php

class Controller {

	private $bdd;
	private $module;
	private $action;
	private $manager;
	
	public function __construct($bdd) {
		$this->bdd = $bdd;
		
		if (!isset($_REQUEST['module']) or !isset($_REQUEST['action'])) {
			if (file_exists('Default.txt')) {
				$f = fopen('Default.txt', 'r');
				while (!feof($f)) {					
					$l = fgets($f);
					$t = explode('=', $l);
					$_REQUEST[trim($t[0])] = trim($t[1]);
				}
				fclose($f);
			}
		}
		$this->module = $_REQUEST['module'];
		$this->action = $_REQUEST['action'];
		
		$nomDuManager = $this->module . 'Manager';
		if (file_exists('Managers/'.$nomDuManager.'.class.php')) {
			// include_once($nomDuManager.'.class.php');
			$this->manager = new $nomDuManager($bdd, $this->module);
		}
		else {
			//include_once('Manager.class.php');
			$this->manager = new Manager($bdd, $this->module);
		}
	}
	
	public function getView() {
		$action  = $this->action;
		if (method_exists($this->manager, $this->action)) {
			return $this->manager->$action();
		}
		else {
			return $this->getError404();
		}
	}
	
	public function getError404() {
		return 'Cette page n\'existe pas...';
	}
}