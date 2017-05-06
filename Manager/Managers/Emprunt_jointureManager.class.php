<?php

// include_once('Manager.class.php');
// include_once('Links.class.php');

class Emprunt_jointureManager extends Manager {
	
	public function __construct($bdd, $table) {
		parent::__construct($bdd, $table);
		$this->showPrimaryKey = false;
	}
	
	public function reserver(){

		if (isset($_REQUEST['id'])) {
			$id = $_REQUEST['id']; // A SECURISER SI BESOIN
			$donnees = $this->getRecord($id);
			$action = 'update';
			//$idurl = '&amp;id='.$id;
		}
		else {
			$donnees = $this->initRecord();
			$action = 'add';
			//$idurl = '';
		}
		$tmp = '<FORM action="index.php?module='.$this->table.'&amp;action='.$action.'" method="POST">';
		$tmp .= '<FIELDSET><TABLE>';
		$autofocus = 'autofocus';
		foreach ($this->structure as $field) {
			if (!($field->getField() == $this->primarykey and $field->getExtra() == 'auto_increment') or $this->showPrimaryKey) {
				$tmp .= '<TR><TD align="RIGHT">'.$field->getComment().': </TD><TD>';
				$getInput = 'getInput'.ucfirst($field->getField());
				$tmp .= $this->$getInput($donnees[$field->getField()], $autofocus);
				/*
				if ($field->getLink() != null) {
					$tmp .= $this->getDropList($field, $donnees[$field->getField()]);
				} else {
					$tmp .=  $field->getInput($donnees[$field->getField()], $autofocus);
				}*/
				$tmp .= '</TD></TR>';
				$autofocus = '';
			} else {
				if ($action == 'update') {
					$tmp .= '<INPUT type="hidden" name="'.$field->getField().'" value="'.$donnees[$field->getField()].'" />';
				} 
			} 
		}
		$tmp .= '<TR><TD colspan="2"><INPUT type="submit" name="bValider" value="Enregistrer" />';
		$tmp .= '<INPUT type="submit" name="bAnnuler" value="Annuler" /></TD></TR>';
		$tmp .= '</TABLE></FIELDSET>';
		$tmp .= '</FORM>';
		return $tmp;
	}

	public function getLinks() {
		return array(
			new Links('userid', 'user', array('username','\\ [','userid','\\]'))		// pour obtenir les menus déroulants
			,new Links('vehiculeid', 'vehicule', array('vehiculename','\\ [','vehiculeid','\\]'))
//			,new Links('idlocal', 'locaux', array('denlocal','\\ [','idlocal','\\]'))
//			,new Links('idfourn', 'fournisseurs', array('denfourn'))
		);
	}
	
/*	public function getInputIdlocal($value, $suppl) {
		return 'Ici votre local';
	}
	
	public function getAlignType() {
		return 'center';
	}*/
	
 /* 	public function getInfoEnservice($donnees, $field) {
 		return ($donnees['enservice'] == 'Y') ? 'Oui' : 'Non';
 	}
	
	public function getAlignEnservice() {
		return 'CENTER';
	} */
	
/*	public function getVisibleIdmarque() {
		return false;
	}
	
	public function getVisibleNumserie() {
		return false;
	}
	
	public function getVisibleMaintenance() {
		return false;
	}*/
	
	public function getInvisibleFields() {
		return array('userpwd');
	}
	
/*	public function getInfoDateInv($donnees, $field) {
		$dateAAfficher = substr($donnees['dateinv'],0,10);
		return $dateAAfficher;
	}*/
}

?>