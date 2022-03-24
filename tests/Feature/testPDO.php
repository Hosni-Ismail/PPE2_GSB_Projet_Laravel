<?php
/**
 * Test des requetes 
 * fonction fonctionne parfaitement
 * recuperation des resultat sous forme de tableau 'OK'
 */



/**
 * Retourne le  nom le prenom d'un visiteur
 
 * @param aucun
 * @return  le nom et le prénom sous la forme d'un tableau associatif 
*/
public function getNomPrenom()
	{
		$req = "select nom,prenom from visiteur";
        $res = $this->monPdo->query($req);
		$lesLigne = $res->fetchall();
		return $lesLignes;
	}
        
/**
 * Retourne les informations d'un visiteur en fonction de son nom et prenom
 
 * @param $prenom
 * @return l'id, le nom et le prénom sous la forme d'un tableau associatif 
*/
public function getInfoVisiteur($prenom)
    {  $req="select count(id) from visiteur where  prenom='$prenom'";
        $res=  $this->$monPdo->query($req);
        return $res->fetch();
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
 * Retourne les informations d'un visiteur en fonction de son nom et prenom
 
 * @param $id
 * @return id,nom,prenom,login,mdp,adrese,cp,ville,date_embauche et role d'un visiteur sous la forme d'un tableau associatif 
*/
public function getInfoVisiteur($id)
    {  $req="select id,nom,prenom,login,mdp,adrese,cp,ville,date_embauche from visteur where id='$id'";
       $res=  $this->$monPdo->query($req);
       return $res->fetch();
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

public function InsertVisiteurInfoSupprime($id,$nom,$prenom,$login,$mdp,$adresse,$cp,$ville,$date_embauche)
{
    $req = "INSERT INTO 'archive' ('id','nom','prenom','login','mdp','adresse','cp','ville','date_embauche','role')
    VALUES ('$id','$nom','$prenom','$login','mdp','$adresse','$cp','$ville','$date_embauche','$role') ";
    $res= $this->$monPdo->query($req);
    return $res;
 
}

/**
 * Fonction qui va verifier si l'occurence inserer est bien presente dans la table archive
 * @param $id
 * @return la valeur si le visiteur est present et 0 si il n'est pas present  ;
*/

public function verificationVisiteurArchive($id)
    {
        req = "SELECT count(*) as nb from visiteur where id='$id' ";
        $res = $this->$monPdo->query($req);
        return $res->fetch();      
    }
/**
 * Fonction qui va  supprimer le visiteur de la table visiteur
 * @param $id
 * @return ;
 *
 */
  public function supprimeVisiteurSurVisiteur($id)
    {
        req = "DELETE from visiteur where id='$id' ";
        $res = $this->$monPdo->query($req);
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
    req = "DELETE from fichefrais where id='$id' ";
    $res = $this->$monPdo->query($req);
    return $res->fetch();      
}

/**
 * Fonction qui va  supprimer le visiteur de la table lignefraisforfait
 * @param $id
 * @return ;
 */

public function supprimeVisiteurSurlignefraisforfait($id)
{
    req = "DELETE from lignefraisforfait where id='$id' ";
    $res = $this->$monPdo->query($req);
    return $res->fetch();      
}

/**
 * Fonction qui verifie la suppresion du visiteur sur les tables concernees
 * @param $id
 * @return 0 si le visiteur est absent des tables et 1 si il est present
 */
public function verificationSuppresionVisiteur($id)
{
    req = "SELECT COUNT(visiteur.id) as nb from visiteur
    INNER JOIN fichefrais
    ON visiteur.id = fichefrais.idVisiteur
    INNER JOIN lignefraisforfait
    ON fichefrais.idVisiteur = fichefrais.idVisiteur
    where id='$id'
    GROUP BY visiteur.id ";
    $res = $this->$monPdo->query($req);
    return $res->fetch();      
}













?>



