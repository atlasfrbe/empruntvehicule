<?php

function chargerClasse($classe) {
	if (file_exists($classe.'.class.php'))
		require_once($classe.'.class.php');
	elseif (file_exists('Application/'.$classe.'.class.php'))
		require_once('Application/'.$classe.'.class.php');
	elseif (file_exists('Managers/'.$classe.'.class.php'))
		require_once('Managers/'.$classe.'.class.php');
	else die('Classe introuvable');
}

spl_autoload_register('chargerClasse');

include('template.php');
include('connect.php');

// echo '<A href="index.php?module=emprunt_jointure&amp;action=getForm">Ajouter un emprunt</A> - ';
echo '<A href="index.php?module=user&amp;action=getList">Personne</A> - ';
echo '<A href="index.php?module=vehicule&amp;action=getList">Véhicule</A> - ';
echo '<A href="index.php?module=emprunt_jointure&amp;action=getList">Réservation</A> ';
echo '<A href="index.php?module=marque&amp;action=getList">Marque</A> ';
echo '<BR/><BR/>';

$control = new Controller($bdd);
echo $control->getView();

?>

	</body>
</html>