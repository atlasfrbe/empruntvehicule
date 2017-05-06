<?php

class userManager extends Manager {

	public function getInvisibleFields() {
		return array('userpwd');
	}
	
	public function getVisibleUserpwd() {
		return false;
	}	

	/*public function getInputDenos($value, $suppl)
	{
		return 'ICI';
	}*/
}