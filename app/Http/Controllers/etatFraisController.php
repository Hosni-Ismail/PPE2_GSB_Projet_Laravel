<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use PdoGsb;
use MyDate;
class etatFraisController extends Controller
{
    function selectionnerMois()
    {
        if(session('visiteur') != null)
        {
            $visiteur = session('visiteur');
            $idVisiteur = $visiteur['id'];
            $lesMois = PdoGsb::getLesMoisDisponibles($idVisiteur);
            $lesVisiteurs = PdoGsb::getNomPrenom();//recuperation nom et prenom des visiteurs
            $roleVisiteur = $visiteur['role'];
            $prenom = $visiteur['prenom'];
            $nom = $visiteur['nom'];

            if($roleVisiteur == 1)
            {
                return view('v')
                ->with('nom',$nom)
                ->with('prenom',$prenom)
                ->with('roleVisiteur',$roleVisiteur)
                ->with('lesViteurs',$lesVisiteurs)
                ->with('visiteur',$visiteur);
            }

		    else
            {
                // Afin de sélectionner par défaut le dernier mois dans la zone de liste
		        // on demande toutes les clés, et on prend la première,
		        // les mois étant triés décroissants
		        $lesCles = array_keys( $lesMois );
		        $moisASelectionner = $lesCles[0];
                return view('listemois')
                        ->with('lesMois', $lesMois)
                        ->with('leMois', $moisASelectionner)
                        ->with('visiteur',$visiteur);
            }
        }

        else
        {
            return view('connexion')->with('erreurs',null);
        }

    }


    function voirFrais(Request $request)
    {
        if( session('visiteur')!= null)
        {               
            $visiteur=session('visiteur');
            $roleVisiteur = $visiteur['role'];
            $prenom = $visiteur['prenom'];
            $nom = $visiteur['nom'];
            $identifiantVisiteur=$request['lsvisiteurs'];
            $idVisiteur=$visiteur['id'];
            $roleVisiteur=$visiteur['role'];
            $verificationVisiteur=PdoGsb::getInfoVisiteur($identifiantVisiteur);
            $nbfiche = PdoGsb::getnbFiches($identifiantVisiteur);
            if($roleVisiteur==1)
            //verification , savoir si le visiteur selectionner figure dans la base et possede des fiches
            {
                if($verificationVisiteur!=0 and $nbfiche!=0)
                {
                    //recuparation des informations des fiches de frais en fonction de l'id du visiteur 
                    $infoVisiteurFicheDeFrais=PdoGsb::getLesInfosFicheFraisComplet($identifiantVisiteur);
                    $lesVisiteurs = PdoGsb::getNomPrenom();
                    $lesCles = array_keys( $lesVisiteurs );
                    $visiteurASelectionner = $lesCles[0];
                    $nomPrenomVisiteur =PdoGsb::getidentite($identifiantVisiteur);
                    $vue = view('listeFraisVisiteur')
                    ->with('identite',$nomPrenomVisiteur)
                    ->with('infoVisiteurFicheDeFrais',$infoVisiteurFicheDeFrais)
                    ->with('nom',$nom)
                    ->with('prenom',$prenom)
                    ->with('roleVisiteur',$roleVisiteur)
                    ->with('lesViteurs',$lesVisiteurs)
                    ->with('leVisiteur',$visiteurASelectionner)
                    ->with('visiteur',$visiteur);
                    return $vue;
                }
                else
                {
                    $nomPrenomVisiteur =PdoGsb::getidentite($identifiantVisiteur);
                    $preNomfraisVisiteur = $nomPrenomVisiteur['prenom'];
                    $nomfraisVisiteur = $nomPrenomVisiteur['nom'];
                    $lesVisiteurs = PdoGsb::getNomPrenom();
                    $lesCles = array_keys( $lesVisiteurs );
                    $visiteurASelectionner = $lesCles[0];
                    //message stipullant qu'il n'y a aucune fiche de frais pour ce visiteur;
                    $erreurs[]="Aucun frais existant pour $nomfraisVisiteur  $preNomfraisVisiteur";
                    return view('listeFraisVisiteur')
                    ->with('nom',$nom)
                    ->with('prenom',$prenom)
                    ->with('roleVisiteur',$roleVisiteur)
                    ->with('lesViteurs',$lesVisiteurs)
                    ->with('leVisiteur',$visiteurASelectionner)
                    ->with('visiteur',$visiteur)
                    ->with('errors',$erreurs);
                }

            }
            else
            {

                $visiteur = session('visiteur');
                $idVisiteur = $visiteur['id'];
                $leMois = $request['lstMois']; 
		        $lesMois = PdoGsb::getLesMoisDisponibles($idVisiteur);
                $lesFraisForfait = PdoGsb::getLesFraisForfait($idVisiteur,$leMois);
		        $lesInfosFicheFrais = PdoGsb::getLesInfosFicheFrais($idVisiteur,$leMois);
		        $numAnnee = MyDate::extraireAnnee( $leMois);
		        $numMois = MyDate::extraireMois( $leMois);
		        $libEtat = $lesInfosFicheFrais['libEtat'];
		        $montantValide = $lesInfosFicheFrais['montantValide'];
                $nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
                $dateModif =  $lesInfosFicheFrais['dateModif'];
                $dateModifFr = MyDate::getFormatFrançais($dateModif);
                $vue = view('listefrais')
                    ->with('lesMois', $lesMois)
                    ->with('leMois', $leMois)
                    ->with('numAnnee',$numAnnee)
                    ->with('numMois',$numMois)
                    ->with('libEtat',$libEtat)
                    ->with('montantValide',$montantValide)
                    ->with('nbJustificatifs',$nbJustificatifs)
                    ->with('dateModif',$dateModifFr)
                    ->with('lesFraisForfait',$lesFraisForfait)
                    ->with('visiteur',$visiteur);
                return $vue;//la vue retourner aura acces a toute les tableaux de la fonction de la classe qui la retourne
            }
        }
        else
        {
            return view('connexion')->with('erreurs',null);
        }

    }
  
        
    }