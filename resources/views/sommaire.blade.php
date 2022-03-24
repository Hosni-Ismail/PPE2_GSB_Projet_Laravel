@extends ('modeles/visiteur')
    @section('menu')
            <!-- Division pour le sommaire -->
        <div id="menuGauche">
            <div id="infosUtil">
             </div>  
               <ul id="menuList">
                   <li >
                    <strong>Bonjour {{ $nom. ' ' . $prenom }}</strong>

                   </li>
 
                  <li class="smenu">
                     <a href="{{ route('chemin_gestionFrais')}}" title="Acceuil ">Acceuil</a>
                  </li>
                
                <li class="smenu">
                    <a href="{{ route('chemin_selectionMois') }}" title="AfficheVisiteur">Gerer les visiteurs</a>
                  </li>

                 
                  <li class="smenu">
                <a href="{{ route('chemin_archive') }}" title="Archive">Archive</a>
                  </li>
                  <li class="smenu">
                <a href="{{ route('chemin_deconnexion') }}" title="Se déconnecter">Déconnexion</a><!-- Accompleter -->

                  </li>
                </ul>
        </div>
    @endsection          