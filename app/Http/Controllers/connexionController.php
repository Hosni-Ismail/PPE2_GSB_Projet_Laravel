<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use PdoGsb;

class connexionController extends Controller
{
    function connecter()
    {
        
        return view('connexion')->with('erreurs',null);
    } 
    function valider(Request $request)
    {
        $login = htmlspecialchars($request['login']);
        $mdp = htmlspecialchars($request['mdp']);

        //Verification du captcha
        $request->validate([
            'g-recaptcha-response' => 'required|captcha'
        ]);
        
        $visiteur = PdoGsb::get($login,$mdp);
        $roleVisiteur = PdoGsb::get($login,$mdp);
        if(!is_array($visiteur))
        {
            $erreurs[] = "Login ou mot de passe incorrect(s) ";
            return view('connexion')->with('erreurs',$erreurs);
        }
        else
        {   
            $role = $roleVisiteur['role'];
            if($role==1)//Affichage du menue Gestionnaire
            {
                session(['visiteur' => $visiteur]);
                $prenom = $visiteur['prenom'];
                $nom = $visiteur['nom'];
                return view('sommaire')
                ->with('nom',$nom)
                ->with('prenom',$prenom)
                ->with('roleVisiteur',$roleVisiteur)
                ->with('visiteur',session('visiteur')); 
            }
            else//affichage menue visiteur
            {
                session(['visiteur' => $visiteur]);
                return view('sommaire2')
                ->with('visiteur',session('visiteur')); 
            }
             
        }

    } 

    function deconnecter()
    {
            session(['visiteur' => null]);
            auth()->logout();
            return redirect()->route('chemin_connexion');
       
           
    }
    
}
