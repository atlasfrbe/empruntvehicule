<?php 

try {
	$bdd = new PDO('mysql:host=127.0.0.1;dbname=empruntvehicule','root','', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
}
catch (Exception $e) {
	die('Erreur : '.$e->getMessage());
}