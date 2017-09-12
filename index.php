<?php
// enregistrement de l'autoload
function chargerClasse($classname) {
	require $classname .'.php';
}

spl_autoload_register('chargerClasse');

session_start();

if (isset($_GET['deconnexion']))
{
  session_destroy();
  header('Location: .');
  exit();
}

if(isset($_SESSION['perso'])) {
	$perso = $_SESSION['perso'];
}

$db = new PDO('mysql:host=localhost; dbname=tp_minijeu', 'root', '');
// paramétrage des alertes lorsqu'une requête a échoué
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$manager = new PersonnageManager($db);

if(isset($_POST['creer']) && isset($_POST['nom'])) {
	$perso = new Personnage(['nom' => $_POST['nom']]); // create new perso
	
	if(!$perso->nomValide()){
		$message = 'Le nom choisi est invalide.';
		unset($perso);
	}
	elseif ($manager->exists($perso->nom())){
		$message = 'Le nom du personnage est déjà pris.';
		unset ($perso);
	}
	else {		
		$manager->add($perso);
	}	
}
elseif (isset($_POST['utiliser']) && isset($_POST['nom'])) { // si on souhaite utiliser un perso
	if($manager->exists($_POST['nom'])) { // si le perso existe
		$perso = $manager->get($_POST['nom']);
	}
	else {
		$message = 'Ce personnage n\'existe pas !';
	}
}

elseif (isset($_GET['frapper'])){ // si on a cliqué sur un perso pour le frapper
	if(!isset($perso)){
		$message = 'Merci de créer un personnage ou de vous identifier.';
	}
	else {
		if(!$manager->exists((int) $_GET['frapper'])){
			$message = 'Le personnage que vous essayez de frapper n\'existe pas.';
		}
		else {
			$persoAFrapper = $manager->get((int) $_GET['frapper']);
			
			$resultat = $perso->frapper($persoAFrapper);
			
			switch($resultat) {
				case Personnage::CEST_MOI :
					$message = 'Mais... pourquoi voulez-vous vous frapper ???';
					break;
				
				case Personnage::PERSONNAGE_FRAPPE :
					$message = 'Le personnage a bien été frappé !';
					
					// $manager->update($perso);
					$manager->update($persoAFrapper);
					break;
					
				case Personnage::PERSONNAGE_TUE :
					$message = 'Le personnage a été tué !!';
					
					// $manager->update($perso);
					$manager->delete($persoAFrapper);
					break;
			}
		}
	}
}

?>

<!DOCTYPE html>

<html>
  <head>
    <title>TP : Mini jeu de combat</title>    

    <meta charset="utf-8" />
  </head>

  <body>
	<p>Nombre de personnages créés : <?= $manager->count() ?></p>
	
	<?php
	if(isset($message)){
		echo '<p>'. $message .'</p>';
	}
	
	if(isset($perso)){
	?>
		<p><a href=".?deconnexion=1">Déconnexion</a></p>
	
		<fieldset>
			<legend>Mes informations</legend>
			<p>
				Nom : <?= htmlspecialchars($perso->nom()) ?><br />
				Dégâts : <?= $perso->degats() ?>
			</p>
		</fieldset>
		
		<fieldset>
			<legend>Qui frapper ?</legend>
			<p>
	
	<?php
	$persos = $manager->getList($perso->nom());
	
	if(empty($persos)) {
		echo 'Personne à frapper !';
	}
	else {
		foreach ($persos as $unPerso) {
			echo '<a href="?frapper='. $unPerso->id() .'">'. htmlspecialchars($unPerso->nom()) .'</a> (dégâts : '. 
				$unPerso->degats() .')<br />';
		}
	}
	?>
			</p>
		</fieldset>
	<?php
	}
	else { // il n'y a pas de perso de sélectionné ou créé
	?>
  
  
		<form action="" method="post">
		  <p>
			Nom : <input type="text" name="nom" maxlength="50" />
			<input type="submit" value="Créer ce personnage" name="creer" />
			<input type="submit" value="Utiliser ce personnage" name="utiliser" />
		  </p>
		</form>
		
	<?php
	}
	?>
	</body>
</html>

<?php
if (isset($perso)) // Si on a créé un personnage, on le stocke dans une variable session afin d'économiser une requête SQL.
{
  $_SESSION['perso'] = $perso;
}