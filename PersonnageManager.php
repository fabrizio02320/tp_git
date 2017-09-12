<?php
class PersonnageManager{
	private $_db;
	
	public function __construct($db)
	{
		$this->setDb($db);
	}
	
	public function add(Personnage $perso){
		// preparation de la requete
		$q = $this->_db->prepare('INSERT INTO personnages(nom) VALUES(:nom)');
		
		// assignation des valeurs
		$q->bindValue(':nom', $perso->nom());
		
		// execution de la requete
		$q->execute();
		
		// hydratation du personnage passé en paramètre avec assignation de son id 
		// et des degats initiaux (=0)
		$perso->hydrate([
			'id' => $this->_db->lastInsertId(), 
			'degats' => 0
		]);
	}
	
	// compte le nombre de personnage
	public function count() {
		// execute une requete count et retourne le nombre de résultats trouvés.
		return $this->_db->query('SELECT COUNT(*) FROM personnages')->fetchColumn();
	}
	
	public function delete(Personnage $perso){
		// exécute une requete de type DELETE
		$this->_db->query('DELETE FROM personnages WHERE id = '. $perso->id());
	}
	
	// vérifie si un personnage existe
	public function exists($info){
		// Si le paramètre est un entier, c'est qu'on a fourni un identifiant.
		// On exécute alors une requête COUNT() avec une clause WHERE, et on retourne un boolean.
		if(is_int($info)) {
			return (bool) $this->_db->query('SELECT COUNT(*) FROM personnages WHERE id = '. $info)->fetchColumn();
		}
		
		// Sinon c'est qu'on a passé un nom.
		// Exécution d'une requête COUNT() avec une clause WHERE, et retourne un boolean.
		$q = $this->_db->prepare('SELECT COUNT(*) FROM personnages WHERE nom = :nom');
		$q->execute([':nom' => $info]);
		
		// print_r('<p>Fetch column nom exist : '. $q->fetchColumn() .'</p>');
		return (bool) $q->fetchColumn();
	}
	
	public function get($info){
		// Si le paramètre est un entier, on veut récupérer le personnage avec son identifiant.
		if(is_int($info)){
			$q = $this->_db->query('SELECT id, nom, degats FROM personnages WHERE id = '. $info);
			$donnees = $q->fetch(PDO::FETCH_ASSOC);
			
			return new Personnage($donnees);
		}		
		// Sinon, on veut récupérer le personnage avec son nom.
		else {
			$q = $this->_db->prepare('SELECT id, nom, degats FROM personnages WHERE nom = :nom');
			$q->execute([':nom' => $info]);
			
			return new Personnage($q->fetch(PDO::FETCH_ASSOC));
		}

		// Exécute une requête de type SELECT avec une clause WHERE, et retourne un objet Personnage.
	}
	
	// retourne la liste des personnages dont le nom n'est pas $nom 
	// et retourne un tableau d'instance de personnage
	public function getList($nom = null){
		$persos = [];
		
		// Retourne la liste des personnages dont le nom n'est pas $nom.
		if($nom){
			$q = $this->_db->prepare('SELECT * FROM personnages WHERE nom <> :nom ORDER BY nom');
			$q->execute([':nom' => $nom]);
		}
		else {
			$q = $this->_db->query('SELECT * FROM personnages ORDER BY nom');
		}
		
		// Le résultat sera un tableau d'instances de Personnage.
		while($donnees = $q->fetch(PDO::FETCH_ASSOC)){
			$persos[] = new Personnage($donnees);
		}
		
		return $persos;
	}
	
	public function update(Personnage $perso){
		// preparation de la requete
		$q = $this->_db->prepare('UPDATE personnages SET degats = :degats WHERE id = :id');
		
		// assignation des valeurs
		$q->bindValue(':degats', $perso->degats(), PDO::PARAM_INT);
		$q->bindValue(':id', $perso->id(), PDO::PARAM_INT);
		
		// execution de la requete
		$q->execute();
	}
	
	public function setDb($db){
		$this->_db = $db;
	}
}