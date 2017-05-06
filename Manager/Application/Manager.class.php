<?php
// To do list: réécrire le code en anglais ou en français mais plus en franglais :)
// include('Fields.class.php');

class Manager {
	
	protected $bdd;
	protected $table;
	protected $structure = array();
	protected $primarykey;
	protected $showPrimaryKey = false; // Par défaut, on n'affiche pas la clef primaire
	private static $histoRecord = array();
	
	public function __construct($bdd, $table) {
		$this->bdd = $bdd;
		$this->table = $table;
		
		$rep = $this->bdd->prepare('SHOW FULL COLUMNS FROM '.$this->table);
		$rep->execute();
		
		while ($donnees = $rep->fetch(PDO::FETCH_ASSOC)) {
			// Recherche du lien éventuel
			$lien = null;
			foreach ($this->getLinks() as $link) {
				if ($link->getField() == $donnees['Field']) {
					$lien = $link;
					break;
				}
			}
			
			$this->structure[] = new Fields($donnees, $lien);
			if ($donnees['Key'] == 'PRI') {
				$this->primarykey = $donnees['Field'];
			}
		}
		//  var_dump($this);
	}
	
	public function __call($nomDeLaMethode, $listeDesArguments) {
		
		// echo "Impossible d'appeler la méthode $nomDeLaMethode avec la liste d'arguments :";
		// var_dump($listeDesArguments);

		$listeMethodes = array('getInput','getAlign','getInfo','getVisible');
		
		foreach ($listeMethodes as $get) {
			if (substr($nomDeLaMethode, 0, strlen($get)) == $get) {
				$fieldName = strtolower(substr($nomDeLaMethode, strlen($get)));
				$field = $this->getFieldByName($fieldName);
				
				$p1 = isset($listeDesArguments[0]) ? $listeDesArguments[0] : null;			
				$p2 = isset($listeDesArguments[1]) ? $listeDesArguments[1] : null;
				
				if ($field->getLink() != null and $get=='getInput') {
					return $this->getDropList($field, $p1);
				}
				if ($get == 'getInfo') {
					return $this->getInfo($p1,$p2);
				}
				if ($get == 'getVisible') {
					return !in_array($fieldName, $this->getInvisibleFields());
				}
				return $field->$get($p1, $p2);	
			}
		}
		
		echo "Impossible d'appeler la méthode $nomDeLaMethode avec la liste d'arguments :";
		var_dump($listeDesArguments);
	}
	
	public function getFieldByName($fieldName) {
		foreach ($this->structure as $field) {
			if ($field->getField() == $fieldName)
				return $field;
		}
	}
	
	public function getLinks() {
		return array();
	}
	
	public function getList() {
		
		// PAGINATION >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
		
		// $sql = 'SELECT * FROM '.$this->table;
		// ligne ci dessus remplacé pour limiter l' "affichage des records" par page par ceci:
		// on va donc "lister l’ensemble des enregistrements (par pagination)"
		
		$limit = 3;	// on indique ici la limite de records a appeler dans la bdd par page
		
		if (isset($_GET['idPage'])) {		// si on l'id de la page
			$idPage = $_GET['idPage'];		// alors l'affecter a une variable gràce au contenu de GET
			$offset = $limit*($idPage-1);	// et la limite multipliée par cet id de la page minoré de 1 fixe le décalage(début-1)
		}
		// exemple: si on est à la page 9 alors $offset = 9-1 * la limite, donc ici 160 on commence donc à l'entrée 161
		else {
			$offset = 0;						// sinon on commence par le début (décalage à 0)
		}
		// echo 'l offset est de '.$offset;
		
		// tout ceci pouvait aussi être limité à la ternaire suivante mais j'ai évité pour rendre le code lisible:
		// $offset = (empty($_GET['idPage'])) ? 0 : $limit*($_GET['idPage']-1);
		
		// requête valide pour MySQL et PostgreSQL:
		$sql = 'SELECT * FROM '.$this->table.' LIMIT '.$limit.' OFFSET '.$offset;
		
		//autre requête avec LIMIT uniquement (à utiliser pour les anciennes versions MySQL)
		// $sql = 'SELECT * FROM "'.$this->table.' LIMIT '.$offset.', '.$limit;
		
		
		/* on utilise ici LIMIT comme défini ici: http://sql.sh/cours/limit
		Certains développeur pensent à tort que l’utilisation de LIMIT permet de réduire le temps d’exécution d’une requête. Or, le temps d’exécution est sensiblement le même car la requête va permettre de récupérer toutes les lignes (donc temps d’exécution identique) PUIS seulement les résultats définit par LIMIT et OFFSET seront retournés. Au mieux, utiliser LIMIT permet de réduire le temps d’affichage car il y a moins de lignes à afficher.*/
		
		// fin PAGINATION <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
		
		// Générer le tableau
		$tmp = '<TABLE border="1">';
		// Générer les entêtes de colonnes
		$tmp .= '<TR><TH><A href="index.php?module='.$this->table.'&amp;action=getForm">Ajouter</A></TH>';
		
		foreach ($this->structure as $field) {
			
			if (!($field->getField() == $this->primarykey and $field->getExtra() == 'auto_increment') or $this->showPrimaryKey) {
				$getVisible = 'getVisible'.ucfirst($field->getField());
				if ($this->$getVisible()) {
					$tmp .= '<TH>'.$field->getComment().'</TH>';
				}
			}
		}
		$tmp .= '</TR>';
		
		$req = $this->bdd->prepare($sql);
		$req->execute();
		
		// Générer la liste des enregistrements
		while ($donnees = $req->fetch(PDO::FETCH_ASSOC)) {
			$tmp .= '<TR><TD><A href="index.php?module='.$this->table.'&amp;action=getForm&amp;id='.$donnees[$this->primarykey].'">Modifier</A> <A href="index.php?module='.$this->table.'&amp;action=delete&amp;id='.$donnees[$this->primarykey].'" onclick="return(confirm(\'Etes-vous sûr de vouloir supprimer cette information ?\'))">Supprimer</A></TD>';
			// var_dump($donnees);
			foreach ($this->structure as $field) {
				if (!($field->getField() == $this->primarykey and $field->getExtra() == 'auto_increment') or $this->showPrimaryKey) {
					$getVisible = 'getVisible'.ucfirst($field->getField());
					if ($this->$getVisible()) {
						$getAlign = 'getAlign'.ucfirst($field->getField());
						$getInfo = 'getInfo'.ucfirst($field->getField());
						$tmp.='<TD align="'.$this->$getAlign() .'">'.$this->$getInfo($donnees, $field).'</TD>';
					}
				}
			}
			$tmp .= '</TR>';
		}
		$tmp .= '</TABLE>';
		
		// GESTION DE LA PAGINATION  >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
		
		$sql=' SELECT COUNT(*) FROM '.$this->table;		// on compte le nombre de records
		$req= $this->bdd->prepare($sql);
		$req->execute();
		$nombreRecords=$req->fetch();						// on place ce nombre dans une variable
		$nombre=$nombreRecords[0];							// on enregistre ce nombre dans un tableau
		$Npage=ceil($nombre/$limit);// on divise le nombre d'entrée par la limite puis on arrondi à l'entier au dessus avec ceil.
		$tmp .='page <table border="1">';					// on affiche la page dans un nouveau tableau
		$tmp .='<tr>';
		for ($i=1; $i <= $Npage; $i++) { 
			$tmp .='<td><a href="index.php?module='.$this->table.'&amp;action=getList&amp;idPage='.$i.'">'.$i.'</a></td>';
		}
		$tmp .='</tr>';
		$tmp .= '</TABLE>';		
		
		// GESTION DE LA PAGINATION <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
		
		return $tmp;
	}
		
	public function getInvisibleFields() {
		return array();
	}
	
	public function getInfo($donnees, $field) {
		if ($field->getLink() != null) {
			// Aller chercher l'information dans la table liée
			$linkManager = $this->getNewManager($field->getLink()->getLinkedTable());

			$linkedData = $linkManager->getRecord($donnees[$field->getField()]);
			
			$tmp = '';
			foreach ($field->getLink()->getShowFields() as $fieldName) {
				if ($fieldName[0] == '\\') {
					$tmp .= substr($fieldName, 1);
				} else {
					$tmp .= $linkedData[$fieldName];
				}
			}
			
			return $tmp;
		}
		return $donnees[$field->getField()];
	}
	
	public function getRecord($id) {
		
		if (array_key_exists($this->table.$id, self::$histoRecord)) {
			return self::$histoRecord[$this->table.$id];
		}
		
		$req = $this->bdd->prepare('SELECT * FROM '. $this->table.' WHERE '.$this->primarykey.' = :id');
		$req->bindParam('id', $id, PDO::PARAM_INT);
		$req->execute();
		
		if ($donnees = $req->fetch(PDO::FETCH_ASSOC)) {
			self::$histoRecord[$this->table.$id] = $donnees;
			return $donnees;
		}
		else {
			return $this->initRecord();
		}		
	}
	
	public function getForm($modif = false) {
		if (isset($_REQUEST['id'])) {
			$id = $_REQUEST['id']; // A SECURISER ?
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
	
	public function getNewManager($table) {
		$manager = $table.'Manager';
		if (file_exists('Managers/'.$manager.'.class.php')) {
			// include_once($manager.'.class.php');
			return new $manager($this->bdd, $table);
		} else {
			return new Manager($this->bdd, $table);
		}
	}
	
	public function getDropList($field, $value) {
		$linkManager = $this->getNewManager($field->getLink()->getLinkedTable());
		$tmp = '<SELECT name="'.$field->getField().'">';
		$tmp .= $linkManager->getOptions($field->getLink()->getShowFields(), $value);
		$tmp .= '</SELECT>';
		return $tmp;		
	}
	
	public function getOptions($showFields, $value) {
		$order = '';
		foreach ($showFields as $fieldName) {
			if ($fieldName[0] != '\\') {
				$order .= ($order != '') ? ', ' : '';
				$order .= $fieldName;
			}
		}
		$sql = 'SELECT * FROM '.$this->table.' ORDER BY '.$order;
		$req = $this->bdd->prepare($sql);
		$req->execute();
		
		$tmp = '<OPTION></OPTION>';
		while ($donnees = $req->fetch(PDO::FETCH_ASSOC)) {
			$selected = ($donnees[$this->primarykey] == $value) ? 'SELECTED' : '';
			$tmp .= '<OPTION value="'.$donnees[$this->primarykey].'" '.$selected.'>';
			foreach ($showFields as $fieldName) {
				if ($fieldName[0] == '\\') {
					$tmp .= substr($fieldName,1);
				} else {
					$tmp .= $donnees[$fieldName];
				}
			}
			$tmp .= '</OPTION>';
		}
		return $tmp;
	}
	
	public function initRecord() {
		$donnees[] = array();
		foreach ($this->structure as $field) {
			$donnees[$field->getField()] = $field->getDefault();
		}
		return $donnees;
	}
	
	public function delete() {
		if (isset($_REQUEST['id'])) {
			$this->deleteSQL($_REQUEST['id']);
		}
		return $this->getList();
	}
	
	public function deleteSQL($id) {
		$req = $this->bdd->prepare('DELETE FROM '.$this->table.' WHERE '.$this->primarykey.' = :id');
		$req->bindParam('id', $id, PDO::PARAM_INT);
		$req->execute();
	}
	
	public function add() {
		if (isset($_POST['bValider'])) {
			$donnees = array();
			foreach ($this->structure as $field) {
				$donnees[$field->getField()] =
					(isset($_REQUEST[$field->getField()])) ? htmlspecialchars($_REQUEST[$field->getField()]) : null;
			}
			$lastId = $this->insertSQL($donnees);
		}
		return $this->getList();
	}
	
	public function insertSQL($donnees) {
		$tmp = '';
		foreach ($this->structure as $field) {
			$tmp .= ($tmp == '') ? '' : ', ';
			$tmp .= $field->getField(). ' = :'.$field->getField();
		}
		$sql = 'INSERT INTO '.$this->table.' SET '.$tmp;
		$req = $this->bdd->prepare($sql);
		foreach ($this->structure as $field) {
			$req->bindValue($field->getField(), $donnees[$field->getField()],			$field->getPDOParam());
		}
		//var_dump($req);
		$req->execute();
		$lastId = $this->bdd->lastInsertId();
		// echo $lastId;
		return $lastId;
	}
	
		public function update() {
		if (isset($_POST['bValider'])) {
			$donnees = array();
			foreach ($this->structure as $field) {
				$donnees[$field->getField()] =
					(isset($_REQUEST[$field->getField()])) ? htmlspecialchars($_REQUEST[$field->getField()]) : null;
			}
			// var_dump($donnees);
			$this->updateSQL($donnees);
		}
		return $this->getList();
	}
	
	public function updateSQL($donnees) {
		$tmp = '';
		foreach ($this->structure as $field) {
			$tmp .= ($tmp == '') ? '' : ', ';
			$tmp .= $field->getField(). ' = :'.$field->getField();
		}
		$sql = 'UPDATE '.$this->table.' SET '.$tmp.' WHERE '.$this->primarykey.' = :'.$this->primarykey;
		$req = $this->bdd->prepare($sql);
		foreach ($this->structure as $field) {
			$req->bindValue($field->getField(), $donnees[$field->getField()],$field->getPDOParam());
		}
		//var_dump($req);
		$req->execute();
		// $lastId = $this->bdd->lastInsertId();
		// echo $lastId;
		// return $lastId;
	}
}

?>