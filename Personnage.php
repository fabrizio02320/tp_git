<?php
class Personnage
{
	private $_id, 
			$_degats, 
			$_nom;
			
	const CEST_MOI = 1;
	const PERSONNAGE_TUE = 2;
	const PERSONNAGE_FRAPPE = 3;
	
	public function __construct(array $donnees){
		$this->hydrate($donnees);
	}
	
	public function hydrate(array $donnees){
		foreach($donnees as $key => $value){
			$method = 'set'. ucfirst($key);
			
			if(method_exists($this, $method)){
				$this->$method($value);
			}
		}
	}
	
	public function nomValide() {
		return !empty($this->_nom);
	}
  
	public function frapper(Personnage $perso){
		// ne pas se frapper soi-même
		// si c'est le cas, on arrete en renvoyant une valeur signifiant que le perso
		// ciblé est l'attaquant
		if($perso->id() == $this->id()){
			return self::CEST_MOI;
		}
		
		// on indique au perso frappé qu'il recoit des degats
		return $perso->recevoirDegats();
	}
	
	public function recevoirDegats(){
		// on augmente les degats de 5
		$this->_degats += 5;
		
		// si on a 100 de degats ou plus, la methode renverra une valeur 
		// signifiant que le perso a été tué
		if($this->_degats >= 100){
			return self::PERSONNAGE_TUE;
		}
		
		// sinon, elle renverra une valeur signifiant que le perso a bien 
		// été frappé
		return self::PERSONNAGE_FRAPPE;
	}
	
	public function id(){
		return $this->_id;
	}
	
	public function degats(){
		return $this->_degats;
	}
	
	public function nom(){
		return $this->_nom;
	}
	
	public function setDegats($degats){
		$degats = (int) $degats;
		
		if($degats >= 0 AND $degats <=100){
			$this->_degats = $degats;
		}
	}
	
	public function setId($id){
		$id = (int) $id;
		
		if($id > 0){
			$this->_id = $id;
		}
	}
	
	public function setNom($nom){
		if(is_string($nom)){
			$this->_nom = $nom;
		}
	}
}