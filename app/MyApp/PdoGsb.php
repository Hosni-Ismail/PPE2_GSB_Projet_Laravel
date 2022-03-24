<?php
namespace App\MyApp;
use PDO;
use Illuminate\Support\Facades\Config;
class PdoGsb
{
        private static $serveur;
        private static $bdd;
        private static $user;
        private static $mdp;
        private  $monPdo;
	
/**
 * crée l'instance de PDO qui sera sollicitée
 * pour toutes les méthodes de la classe
 */				
	public function __construct(){
        
        self::$serveur='mysql:host=' . Config::get('database.connections.mysql.host');
        self::$bdd='dbname=' . Config::get('database.connections.mysql.database');
        self::$user=Config::get('database.connections.mysql.username') ;
        self::$mdp=Config::get('database.connections.mysql.password');	  
        $this->monPdo = new PDO(self::$serveur.';'.self::$bdd, self::$user, self::$mdp); 
  		$this->monPdo->query("SET CHARACTER SET utf8");
	}
	public function _destruct(){
		$this->monPdo =null;
	}
	

/**
 * Retourne les informations d'un visiteur
 
 * @param $login 
 * @param $mdp
 * @return l'id, le nom et le prénom sous la forme d'un tableau associatif 
*/
	public function getInfosVisiteur($login, $mdp){
		$req = "select visiteur.id as id, visiteur.nom as nom, visiteur.prenom as prenom , role,login,mdp from visiteur 
        where visiteur.login='" .$login . "' and visiteur.mdp='" . $mdp."'";
    	$rs = $this->monPdo->query($req);
		$ligne = $rs->fetch();
		return $ligne;
	}

	public function get($login,$mdp){

		$sql = "select visiteur.id as id, visiteur.nom as nom, visiteur.prenom as prenom , role,login,mdp from visiteur 
        where visiteur.login=:login and visiteur.mdp=:mdp"; 
		//prepartion de la requete 
    	$rs = $this->monPdo->prepare($sql);


		$tableau=array(
			"login"=>$login,
			"mdp"=>$mdp
		);

		//On donne les valeurs et on execute la requete 

		$rs->execute($tableau);

		//on recupere les resultats comme precedemment 

		$rs->setFetchMode(PDO::FETCH_CLASS,'Voiture');

		//GG tu as bloque l'injection ma couille 
		$rep = $rs->fetch();

		return $rep ;

	
	}




/**
 * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
 * concernées par les deux arguments
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return l'id, le libelle et la quantité sous la forme d'un tableau associatif 
*/
	public function getLesFraisForfait($idVisiteur, $mois){
		$req = "select fraisforfait.id as idfrais, fraisforfait.libelle as libelle, 
		lignefraisforfait.quantite as quantite from lignefraisforfait inner join fraisforfait 
		on fraisforfait.id = lignefraisforfait.idfraisforfait
		where lignefraisforfait.idvisiteur ='$idVisiteur' and lignefraisforfait.mois='$mois' 
		order by lignefraisforfait.idfraisforfait";	
		$res = $this->monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes; 
	}
/**
 * Retourne tous les id de la table FraisForfait
 
 * @return un tableau associatif 
*/
	public function getLesIdFrais(){
		$req = "select fraisforfait.id as idfrais from fraisforfait order by fraisforfait.id";
		$res = $this->monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes;
	}
/**
 * Met à jour la table ligneFraisForfait
 
 * Met à jour la table ligneFraisForfait pour un visiteur et
 * un mois donné en enregistrant les nouveaux montants
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
 * @return un tableau associatif 
*/
	public function majFraisForfait($idVisiteur, $mois, $lesFrais){
		$lesCles = array_keys($lesFrais);
		foreach($lesCles as $unIdFrais){
			$qte = $lesFrais[$unIdFrais];
			$req = "update lignefraisforfait set lignefraisforfait.quantite = $qte
			where lignefraisforfait.idvisiteur = '$idVisiteur' and lignefraisforfait.mois = '$mois'
			and lignefraisforfait.idfraisforfait = '$unIdFrais'";
			$this->monPdo->exec($req);
		}
		
	}

/**
 * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return vrai ou faux 
*/	
	public function estPremierFraisMois($idVisiteur,$mois)
	{
		$ok = false;
		$req = "select count(*) as nblignesfrais from fichefrais 
		where fichefrais.mois = '$mois' and fichefrais.idvisiteur = '$idVisiteur'";
		$res = $this->monPdo->query($req);
		$laLigne = $res->fetch();
		if($laLigne['nblignesfrais'] == 0){
			$ok = true;
		}
		return $ok;
	}
/**
 * Retourne le dernier mois en cours d'un visiteur
 
 * @param $idVisiteur 
 * @return le mois sous la forme aaaamm
*/	
	public function dernierMoisSaisi($idVisiteur){
		$req = "select max(mois) as dernierMois from fichefrais where fichefrais.idvisiteur = '$idVisiteur'";
		$res = $this->monPdo->query($req);
		$laLigne = $res->fetch();
		$dernierMois = $laLigne['dernierMois'];
		return $dernierMois;
	}
	
/**
 * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un visiteur et un mois donnés
 
 * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
 * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
*/
	public function creeNouvellesLignesFrais($idVisiteur,$mois){
		$dernierMois = $this->dernierMoisSaisi($idVisiteur);
		$laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur,$dernierMois);
		if($laDerniereFiche['idEtat']=='CR'){
				$this->majEtatFicheFrais($idVisiteur, $dernierMois,'CL');
				
		}
		$req = "insert into fichefrais(idvisiteur,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
		values('$idVisiteur','$mois',0,0,now(),'CR')";
		$this->monPdo->exec($req);
		$lesIdFrais = $this->getLesIdFrais();
		foreach($lesIdFrais as $uneLigneIdFrais){
			$unIdFrais = $uneLigneIdFrais['idfrais'];
			$req = "insert into lignefraisforfait(idvisiteur,mois,idFraisForfait,quantite) 
			values('$idVisiteur','$mois','$unIdFrais',0)";
			$this->monPdo->exec($req);
		 }
	}


/**
 * Retourne les mois pour lesquel un visiteur a une fiche de frais
 
 * @param $idVisiteur 
 * @return un tableau associatif de clé un mois -aaaamm- et de valeurs l'année et le mois correspondant 
*/
	public function getLesMoisDisponibles($idVisiteur){
		$req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idvisiteur ='$idVisiteur' 
		order by fichefrais.mois desc ";
		$res = $this->monPdo->query($req);
		$lesMois =array();
		$laLigne = $res->fetch();
		while($laLigne != null)	{
			$mois = $laLigne['mois'];
			$numAnnee =substr( $mois,0,4);
			$numMois =substr( $mois,4,2);
			$lesMois["$mois"]=array(
		     "mois"=>"$mois",
		    "numAnnee"  => "$numAnnee",
			"numMois"  => "$numMois"
             );
			$laLigne = $res->fetch(); 		
		}
		return $lesMois;
	}
/**
 * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état 
*/	
	public function getLesInfosFicheFrais($idVisiteur,$mois){
		$req = "select fichefrais.idEtat as idEtat, fichefrais.dateModif as dateModif, fichefrais.nbJustificatifs as nbJustificatifs, 
			fichefrais.montantValide as montantValide, etat.libelle as libEtat from  fichefrais inner join etat on fichefrais.idEtat = etat.id 
			where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		$res = $this->monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne;
	}
/**
 * Modifie l'état et la date de modification d'une fiche de frais
 
 * Modifie le champ idEtat et met la date de modif à aujourd'hui
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 */
 
	public function majEtatFicheFrais($idVisiteur,$mois,$etat){
		$req = "update ficheFrais set idEtat = '$etat', dateModif = now() 
		where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		$this->monPdo->exec($req);
	}


/**
 * Retourne le  nom le prenom d'un visiteur
 
 * @param aucun
 * @return  le nom et le prénom sous la forme d'un tableau associatif 
*/
	public function getNomPrenom()
	{
		$req = "select nom,prenom,id from visiteur where not role=1 order by nom ,prenom";
        $res = $this->monPdo->query($req);
		$lesLigne = $res->fetchall();
		return $lesLigne;
	}
       

	/**
 * Retourne le  nom le prenom d'un visiteur
 
 * @param aucun
 * @return  le nom et le prénom sous la forme d'un tableau associatif 
*/
public function getNomPrenomArchive()
{
	$req = "select nom,prenom,id from archive where not role=1 order by nom ,prenom";
	$res = $this->monPdo->query($req);
	$lesLigne = $res->fetchall();
	return $lesLigne;
}


	/**
 * Retourne les informations d'un visiteur en fonction de son nom et prenom
 
 * @param $prenom
 * @return l'id, le nom et le prénom sous la forme d'un tableau associatif 
*/
public function getInfoVisiteur($id)
{  $req="select count(id) from visiteur where  prenom='$id'";
	$res = $this->monPdo->query($req);
	return $res->fetch();
}


/**
 * Retourne les informations d'un visiteur en fonction de son nom et prenom
 
 * @param $id
 * @return id,nom,prenom,login,mdp,adrese,cp,ville,date_embauche et role d'un visiteur sous la forme d'un tableau associatif 
*/
public function getInfoVisiteurAsupprime($id)
    {  $req="SELECT id,nom,prenom,login,mdp,adresse,cp,ville,dateEmbauche,role FROM visiteur WHERE id='$id'";
		$res = $this->monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne;
    }

/**
 * Retourne les informations des fiches de frais d'un visiteur en fonction de son nom et prenom
 
 * @param $prenom
 * @return l'idEtat, datemotif et nombre de justificatif sous la forme d'un tableau associatif 
*/
public function getInfoFicheFraisVisiteur($prenom)
{
    $req = "select fichefrais.idEtat as idEtat, fichefrais.dateModif as dateModif, fichefrais.nbJustificatifs as nbJustificatifs, 
		fichefrais.montantValide as montantValide, etat.libelle as libEtat from  fichefrais inner join etat on fichefrais.idEtat = etat.id 
        inner join visiteur on visiteur.id=fichefrais.idVisiteur
		where visiteur.prenom = '$prenom'";
		$res = $this->monPdo->query($req);
		$lesLigne = $res->fetchall();
		return $lesLigne;
}










    


/**
 * Insert les informations du visiteur qui doit etre supprimer dans la table archive
 * @param $id
 * @param $prenom
 * @param nom
 * @param login
 * @param mdp
 * @param adresse
 * @param cp
 * @param ville
 * @param date_embauche
 * @param role
 * @return  ;
*/

public function InsertVisiteurInfoSupprime($id,$nom,$prenom,$login,$mdp,$adresse,$cp,$ville,$date_embauche,$role,$datesup,$dateval)
{
    $req = "INSERT INTO `archive`(`id`, `nom`, `prenom`, `login`, `mdp`, `adresse`, `cp`, `ville`, `dateEmbauche`, `role`,`dateSupression`,`dateValidite`) VALUES ('$id','$nom','$prenom','$login','$mdp','$adresse','$cp','$ville','$date_embauche','$role','$datesup','$dateval') ";  
	$this->monPdo->exec($req);


}

/**
 * Fonction qui va  supprimer le visiteur de la table visiteur
 * @param $id
 * @return ;
 *
 */
public function supprimeVisiteurSurVisiteur($id)
{
	$req ="DELETE from visiteur where id='$id' ";
	$this->monPdo->exec($req);
	
}


/**
 * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état 
*/	
public function getLesInfosFicheFraisComplet($id)
{
	$req = "select fraisforfait.id as idfrais,fichefrais.mois as mois, 
		fraisforfait.libelle as libelle, lignefraisforfait.idvisiteur as id,
	 	fichefrais.idEtat as idEtat, fichefrais.dateModif as dateModif,
		fichefrais.nbJustificatifs as nbJustificatifs, 
		fichefrais.montantValide as montantValide, etat.libelle as libEtat,
		lignefraisforfait.quantite as quantite 
		from etat
		inner join fichefrais
		on fichefrais.idEtat = etat.id
		inner join 
		lignefraisforfait 
		on fichefrais.mois=lignefraisforfait.mois
		inner join fraisforfait 
		on fraisforfait.id = lignefraisforfait.idfraisforfait
		where lignefraisforfait.idvisiteur ='$id'  
		";	
	$res = $this->monPdo->query($req);
	$lesLigne = $res->fetchall();
	return $lesLigne;
}



/**
 * Retourne les mois pour lesquel un visiteur a une fiche de frais
 
 * @param $idVisiteur 
 * @return un tableau associatif de clé un mois -aaaamm- et de valeurs l'année et le mois correspondant 
*/
public function getLesInfosFicheFraisMois($id)
{
	$req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idvisiteur ='$id' 
	order by fichefrais.mois desc ";
	$res = $this->monPdo->query($req);
	$lesMois =array();
	$laLigne = $res->fetch();
	while($laLigne != null)	{
		$mois = $laLigne['mois'];
		$numAnnee =substr( $mois,0,4);
		$numMois =substr( $mois,4,2);
		$lesMois["$mois"]=array(
		 "mois"=>"$mois",
		"numAnnee"  => "$numAnnee",
		"numMois"  => "$numMois"
		 );
		$laLigne = $res->fetch(); 		
	}
	return $lesMois;
}

/**
 * Retourne le  nom le prenom d'un visiteur
 
 * @param id visiteur
 * @return  le nom et le prénom sous la forme d'un tableau associatif 
*/
public function getidentite($id)
{
	$req = "select nom,prenom from visiteur where id = '$id' ";
	$res = $this->monPdo->query($req);
	$lesLigne = $res->fetch();
	return $lesLigne;
}

/**
 * Retourne le  nom le prenom d'un visiteur
 
 * @param aucun
 * @return  le nom et le prénom sous la forme d'un tableau associatif 
*/
public function getnbFiches($id)
{
	$req = "SELECT COUNT(*),idVisiteur FROM `fichefrais` where idVisiteur ='$id' group by idVisiteur ";
	$res = $this->monPdo->query($req);
	$lesLigne = $res->fetch();
	return $lesLigne;
}



/**
 * Fonction qui va verifier si l'occurence inserer est bien presente dans la table archive
 * @param $id
 * @return la valeur si le visiteur est present  1 et 0 si il n'est pas present  ;
*/

public function verificationVisiteurArchive($id)
    {
        $req = "SELECT count(*) as nb from archive where id='$id' ";
        $res = $this->monPdo->query($req);
		return $res->fetch();     
    }


/**
 * Fonction qui va  supprimer le visiteur de la table fichefrais
 * @param $id
 * @return ;
 *
 */
public function supprimeVisiteurSurFicheFrais($id)
{
    $req = "delelte from fichefrais where idVisiteur='$id' ";
	$res = $this->monPdo->query($req);
	$lesLigne = $res->fetch();
	return $lesLigne;
}

/**
 * Fonction qui va  supprimer le visiteur de la table lignefraisforfait
 * @param $id
 * @return ;
 */

public function supprimeVisiteurSurlignefraisforfait($id)
{
    $req = "delelte from lignefraisforfait where idVisiteur='$id' ";
	$res = $this->monPdo->query($req);
	$lesLigne = $res->fetch();
	return $lesLigne;
}

/**
 * Fonction qui verifie la suppresion du visiteur sur les tables concernees
 * @param $id
 * @return 0 si le visiteur est absent des tables et 1 si il est present
 */
public function verificationSuppresionVisiteur($id)
{
    $req = "SELECT COUNT(id) as nb from visiteur where id='$id'";
	$res = $this->monPdo->query($req);
	return $res->fetch();  

}

	/**
 * Retourne le  nom le prenom d'un visiteur
 
 * @param aucun
 * @return  le nom et le prénom sous la forme d'un tableau associatif 
*/
public function getinfoArchive($id)
{
	$req = "SELECT nom,prenom,cp,adresse,ville,dateEmbauche,dateSupression,dateValidite 	FROM `archive` where id ='$id' ";
	$res = $this->monPdo->query($req);
	$lesLigne = $res->fetch();
	return $lesLigne;
}
















}
