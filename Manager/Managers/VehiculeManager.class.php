<?php

class vehiculeManager extends Manager {
	
	public function __construct($bdd, $table) {
		parent::__construct($bdd, $table);
		$this->showPrimaryKey = false;
	}
	
	public function getLinks() {
		return array(
			/*new Links('vehiculeid', 'vehicule', array('vehiculename')),*/
			// ceci est réutilisé par le getDropList du manager pour fournire la liste déroulante des marque:
			new Links('marqueid', 'marque', array('marquename','\\ [','marqueid','\\]'))
			/*, new Links('idfourn', 'fournisseurs', array('denfourn'))*/
		);
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
		$sql = 'SELECT * FROM '.$this->table;
		if(isset($_POST['tri'])){
			$sql .= ' ORDER BY '.$_POST['tri'];
			if(isset($_POST['desc']) && $_POST['desc'] == "desc"){
				$sql .= ' DESC';
			}
		}
		
		$sql .= ' LIMIT '.$limit.' OFFSET '.$offset;
		
		$req_order = $this->bdd->prepare($sql);
		$req_order->execute();
		$donnees = $req_order->fetch(PDO::FETCH_ASSOC);

		//autre requête avec LIMIT uniquement (à utiliser pour les anciennes versions MySQL)
		// $sql = 'SELECT * FROM "'.$this->table.' LIMIT '.$offset.', '.$limit;
		
		
		/* on utilise ici LIMIT comme défini ici: http://sql.sh/cours/limit
		Certains développeur pensent à tort que l’utilisation de LIMIT permet de réduire le temps d’exécution d’une requête. Or, le temps d’exécution est sensiblement le même car la requête va permettre de récupérer toutes les lignes (donc temps d’exécution identique) PUIS seulement les résultats définit par LIMIT et OFFSET seront retournés. Au mieux, utiliser LIMIT permet de réduire le temps d’affichage car il y a moins de lignes à afficher.*/
		
		// fin PAGINATION <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
		
		$tmp = '<form method="post">';
		$tmp .= '<select name="tri">';
		foreach ($donnees as $key => $value)
		{
        $tmp .= ' <option value="'.$key.'">'.$key.'</option>';
		}
		$tmp .= '</select>';
		$tmp .= '<input type="radio" name="desc" value="desc"> Descendant';
		$tmp .= '<input type="radio" name="desc" value="asc"> Ascendant';

		$tmp .= '<input type="submit" value="rafraichir">';

		$tmp .= '</form>';


		// Générer le tableau
		$tmp .= '<TABLE border="1">';
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

/*	public function getInputDenfourn() {
		return 'Rien du tout';
	}
	
	public function getAlignTelfourn()
	{
		return 'CENTER';
	}*/
}