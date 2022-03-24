<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use PdoGsb;
use MyDate;
use Dompdf\Dompdf;
use Dompdf\Options;

class gererFraisController extends Controller
{

    function saisirFrais(Request $request)
    {
        if( session('visiteur') != null)
        {
            $visiteur = session('visiteur');
            $idVisiteur = $visiteur['id'];
            $roleVisiteur = $visiteur['role'];
            $prenom = $visiteur['prenom'];
            $nom = $visiteur['nom'];
            if($roleVisiteur == 1)//permet d'afficher l'acceuil du gestionnaire
            {
                
                return view('sommaire')
                ->with('roleVisiteur',$roleVisiteur)
                ->with('nom',$nom)
                ->with('prenom',$prenom)
                ->with('visiteur',$visiteur); 
            }
            else
            {
                $anneeMois = MyDate::getAnneeMoisCourant();
                $mois = $anneeMois['mois'];
                if(PdoGsb::estPremierFraisMois($idVisiteur,$mois))
                {
                 PdoGsb::creeNouvellesLignesFrais($idVisiteur,$mois);
                }
                    $lesFrais = PdoGsb::getLesFraisForfait($idVisiteur,$mois);
                    $view = view('majFraisForfait')
                    ->with('lesFrais', $lesFrais)
                    ->with('numMois',$anneeMois['numMois'])
                    ->with('erreurs',null)
                    ->with('numAnnee',$anneeMois['numAnnee'])
                    ->with('visiteur',$visiteur)
                    ->with('message',"")
                    ->with ('method',$request->method());
                    return $view;
            }
        }
                else
                {
                    return view('connexion')->with('erreurs',null);
                }
    }


    function sauvegarderFrais(Request $request)
    {
        if( session('visiteur')!= null)
        {
            $visiteur = session('visiteur');
            $idVisiteur = $visiteur['id'];
            $roleVisiteur=$visiteur['role'];
            $prenom = $visiteur['prenom'];
            $nom = $visiteur['nom'];
            $indentifiantVisiteurASupprimer = $request['id'];
            if($roleVisiteur==1)//supression visiteur selectionne
            {
                //recuperation des informations du visiteurs a supprimes
                $infoVisiteurAsupprime=PdoGsb::getInfoVisiteurAsupprime($indentifiantVisiteurASupprimer);
                $id=$infoVisiteurAsupprime['id'];
                $prenom=$infoVisiteurAsupprime['prenom'];
                $nom=$infoVisiteurAsupprime['nom'];
                $login=$infoVisiteurAsupprime['login'];
                $mdp=$infoVisiteurAsupprime['mdp'];
                $adresse=$infoVisiteurAsupprime['adresse'];
                $cp=$infoVisiteurAsupprime['cp'];
                $ville=$infoVisiteurAsupprime['ville'];
                $date_embauche=$infoVisiteurAsupprime['dateEmbauche'];
                $role=$infoVisiteurAsupprime['role'];

                //date
                $datesupression = date("Y-m-d");
                $dateVal = date("Y-m-d", strtotime('+10 year'));


                //insertion du visiteur a supprimer dans la table archive
                $insertionVisiteurAsupprimer=PdoGsb::InsertVisiteurInfoSupprime($id,$nom,$prenom,$login,$mdp,$adresse,$cp,$ville,$date_embauche,$role,$datesupression,$dateVal);
                //Suppresion du visteur 
                $deleteVisiteurtableVisiteur=PdoGsb::supprimeVisiteurSurVisiteur($indentifiantVisiteurASupprimer);
                //(alteration des tables en vue de modifier les contraintes de base et integrer le on delete cascade)verification
                $verification = PdoGsb::getLesInfosFicheFraisMois($indentifiantVisiteurASupprimer);
                if($verification == null)
                {
                        $visiteur = session('visiteur');
                        $idVisiteur = $visiteur['id'];
                        $roleVisiteur=$visiteur['role'];
                        $prenom = $visiteur['prenom'];
                        $nom = $visiteur['nom'];
                        $message = "Suppression effectuée avec succès !";
                        return view('valider')
                        ->with('nom',$nom)
                        ->with('prenom',$prenom)
                        ->with('message',$message)
                        ->with('roleVisiteur',$roleVisiteur)
                        ->with('visiteur',$visiteur);  
                }
                else
                {
                    $visiteur = session('visiteur');
                    $idVisiteur = $visiteur['id'];
                    $roleVisiteur=$visiteur['role'];
                    $prenom = $visiteur['prenom'];
                    $nom = $visiteur['nom'];
                    $erreurs[] = "Erreur lors de la suppression.Veuillez recommencer
                    NB: Si le problème persiste signalez le à votre développeur web .
                    Merci!!!
                    ";
                    return view('erreur')
                    ->with('nom',$nom)
                    ->with('prenom',$prenom)
                    ->with('erreurs',$erreurs)                        
                    ->with('roleVisiteur',$roleVisiteur)
                    ->with('visiteur',$visiteur);
                }


            }
        
        
            else
            {
                $anneeMois = MyDate::getAnneeMoisCourant();
                $mois = $anneeMois['mois'];
                $lesFrais = $request['lesFrais'];
                $lesLibFrais = $request['lesLibFrais'];
                $nbNumeric = 0;
                foreach($lesFrais as $unFrais)
                {
                    if(is_numeric($unFrais))
                        $nbNumeric++;
                }
                $view = view('majFraisForfait')->with('lesFrais', $lesFrais)
                    ->with('numMois',$anneeMois['numMois'])
                    ->with('numAnnee',$anneeMois['numAnnee'])
                    ->with('visiteur',$visiteur)
                    ->with('lesLibFrais',$lesLibFrais)
                    ->with ('method',$request->method());
                if($nbNumeric == 4)
                {
                    $message = "Votre fiche a été mise à jour";
                    $erreurs = null;
                    PdoGsb::majFraisForfait($idVisiteur,$mois,$lesFrais);
        	    }
		        else
                {
                    $erreurs[] ="Les valeurs des frais doivent être numériques";
                    $message = '';
                }
                return $view
                ->with('erreurs',$erreurs)
                ->with('message',$message);
            }
        }
        else
        {
            return view('connexion')->with('erreurs',null);
        }
    }





    function archive(Request $request)
    {
        if(session('visiteur') != null)
        {
            $visiteur = session('visiteur');
            $idVisiteur = $visiteur['id'];
            $lesVisiteurs = PdoGsb::getNomPrenomArchive();//recuperation nom et prenom des visiteurs en archives
            $roleVisiteur = $visiteur['role'];
            $prenom = $visiteur['prenom'];
            $nom = $visiteur['nom'];

            if($roleVisiteur == 1)
            {
                return view('listeVisiteurArchive')
                ->with('nom',$nom)
                ->with('prenom',$prenom)
                ->with('roleVisiteur',$roleVisiteur)
                ->with('lesViteurs',$lesVisiteurs)
                ->with('visiteur',$visiteur);
            }
            else
            {
                return view('connexion')->with('erreurs',null);
            }
        }    
    }
    function infoarchive(Request $request)
    {
        if(session('visiteur') != null)
        {
            $visiteur = session('visiteur');
            $idVisiteur = $visiteur['id'];
            $idinfovisiteur=$request['lsvisiteurs'];
            $lesVisiteurs = PdoGsb::getNomPrenomArchive();//recuperation nom et prenom des visiteurs en archives
            $Visiteurs = PdoGsb::getinfoArchive($idinfovisiteur);//recuperation nom et prenom des visiteurs en archives
            $roleVisiteur = $visiteur['role'];
            $prenom = $visiteur['prenom'];
            $nom = $visiteur['nom'];

            if($roleVisiteur == 1)
            {
                return view('infovisiteurArchive')
                ->with('nom',$nom)
                ->with('prenom',$prenom)
                ->with('roleVisiteur',$roleVisiteur)
                ->with('Visiteurs',$Visiteurs)
                ->with('lesViteurs',$lesVisiteurs)
                ->with('visiteur',$visiteur);
            }
            else
            {
                return view('connexion')->with('erreurs',null);
            }
        }    
    }
    
    function generateur($nom,$prenom,$ville,$cp,$adresse,$dateEmbauche,$dateSupression,$dateValidite)
    {

        $fiche = "Nom : $nom <br/>Prenom :$prenom<br/> Ville :$ville <br/>CP :$cp<br/>Adresse :$adresse<br/>Date-Embauche :$dateEmbauche<br/>Date-Suppresion:$dateSupression<br/>Date-Validiter:$dateValidite" ;


        require_once __DIR__.'/dompdf/autoload.inc.php';

        $option = new Options();
        $option ->set('defaultFont', 'Courier');

        ob_start();
        include 'F:/Laragon/laragon/www/mission2-no-git/gsbLaravel/resources/views/pdf.php';
        $html = ob_get_contents();/*permet d'avoir le fichier infovisiteurArchive.blade.php dans $html*/
        ob_end_clean();

        $dompdf = new Dompdf($option);

        $dompdf->loadHtml($fiche);/* mettre de html*/

        
        $dompdf->setPaper('A4', 'portrait');/* information sur la forme du fichier */

        $dompdf -> render (); 
        
        $fichier = "user--document";

        $dompdf->stream($fichier);


    }

    function telecharge(Request $request)
    {
        $VisiteursNom= $request['nom'];
        $VisiteursPrenom = $request['prenom'];
        $VisiteursAdresse =  $request['adresse'];
        $VisiteursVille  = $request['ville'];
        $VisiteursCp = $request['cp'];
        $VisiteursEmbauche = $request['dateEmbauche'];
        $VisiteursDeSuppression = $request['dateSupression'];
        $VisiteursDeValiditer = $request['dateValidite'];

        $u = gererFraisController::generateur($VisiteursNom,$VisiteursPrenom,$VisiteursAdresse,$VisiteursVille,$VisiteursCp,$VisiteursEmbauche,$VisiteursDeSuppression,$VisiteursDeValiditer);

        return $u;



    }

}














