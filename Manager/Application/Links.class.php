<?php
// idem

class Links {

	private $field; // Clef étrangère
	private $linkedTable; // Table liée
	private $showFields = array(); // Champs à afficher

	public function __construct($field, $linkedTable, $showFields) {
		$this->field = $field;
		$this->linkedTable = $linkedTable;
		$this->showFields = $showFields;
	}
	
	public function getField() {
		return $this->field;
	}
	
	public function getLinkedTable() {
		return $this->linkedTable;
	}
	
	public function getShowFields() {
		return $this->showFields;
	}
}