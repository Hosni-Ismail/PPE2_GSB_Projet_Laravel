@extends ('v')
@section('contenu2')
<form method="get" action="{{ URL::action('gererFraisController@sauvegarderFrais') }}">
<fieldset>
@foreach($infoVisiteurFicheDeFrais as $visiteurFiche)
    <h3>Fiche de frais: {{ $identite['nom']}}/{{ $identite['prenom']}}  
        </h3>
        <div class="encadre">
        <p>
        Etat : <strong>{{ $visiteurFiche['libEtat'] }} depuis le {{ $visiteurFiche['dateModif'] }} </strong>
            <br> Montant validé : <strong>{{ $visiteurFiche['montantValide'] }} </strong>
        </p>
  	    <table class="listeLegere">
  	        <caption>Eléments forfaitisés </caption>
            <tr>
			    <th> {{$visiteurFiche['libelle']}} </th>
                <th> Etat </th>
		    </tr>
            <tr>
                    <td class="qteForfait">{{ $visiteurFiche['quantite'] }} 
                    </td>
                    <td> {{$visiteurFiche['libEtat']}} </td>
		    </tr>
        </table>
  	    </div>
@endforeach
<input type = "hidden" value="{{ $visiteurFiche['id'] }}" name ="id" /><!-- permet l'envoi de l'Id visiteur par URL -->
<center><input id="ok" type="submit" value="Valider la suppression" size="20" /></center>
<center><a href="{{ URL::action('etatFraisController@selectionnerMois') }}" class="btn btn-default">RETOUR</a></center>
</fieldset>
</form>
@endsection
