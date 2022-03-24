@extends ('listeVisiteurArchive')
@section('contenu1')
@section('contenu2')
<? /* permet d afficher le contenu inferieur */?>
<form method = "GET" action = "{{ URL::action('gererFraisController@telecharge') }}">
<fieldset>
    <h3>Visiteur Archiver : {{ $Visiteurs['nom']}}/{{ $Visiteurs['prenom']}}  
        </h3>
        <div class="encadre">
        <p>
        Date de suppression : <strong>{{ $Visiteurs['dateSupression'] }} </strong>
        <br> Date de Validite de concervation des informations : <strong>{{ $Visiteurs['dateValidite'] }} </strong>
        </p>
  	    <table class="listeLegere">
  	        <caption>Informations visiteurs :</caption>
            <tr>
			    <th> NOM </th>
                <th> PRENOM</th>
                <th> ADRESSE </th>
                <th> VILLE </th>
                <th> CP </th>
                <th>DATE EMBAUCHE</th>
		    </tr>
            <tr>
                    <td class="qteForfait">{{ $Visiteurs['nom'] }} </td>
                    <td class="qteForfait">{{ $Visiteurs['prenom'] }} </td>
                    <td class="qteForfait">{{ $Visiteurs['adresse'] }} </td>
                    <td class="qteForfait">{{ $Visiteurs['ville'] }} </td>
                    <td class="qteForfait">{{ $Visiteurs['cp'] }} </td>
                    <td class="qteForfait">{{ $Visiteurs['dateEmbauche'] }} </td> 
		    </tr>
        </table>
  	    </div>
        <center><input id="ok" type="submit" value="Generer PDF" size="20" /></center>
        <input type = "hidden" value="{{ $Visiteurs['nom'] }}" name ="nom" /><!-- permet l'envoi de info du visiteur par URL -->
        <input type = "hidden" value="{{ $Visiteurs['prenom'] }}" name ="prenom" /><!-- permet l'envoi de info du visiteur par URL -->
        <input type = "hidden" value="{{ $Visiteurs['adresse'] }}" name ="adresse" /><!-- permet l'envoi de info du visiteur par URL -->
        <input type = "hidden" value="{{ $Visiteurs['ville'] }}" name ="ville" /><!-- permet l'envoi de info du visiteur par URL -->
        <input type = "hidden" value="{{ $Visiteurs['cp'] }}" name ="cp" /><!-- permet l'envoi de info du visiteur par URL -->
        <input type = "hidden" value="{{ $Visiteurs['dateEmbauche'] }}" name ="dateEmbauche" /><!-- permet l'envoi de info du visiteur par URL -->
        <input type = "hidden" value="{{ $Visiteurs['dateSupression'] }}" name ="dateSupression" /><!-- permet l'envoi de info du visiteur par URL -->
        <input type = "hidden" value="{{ $Visiteurs['dateValidite'] }}" name ="dateValidite" /><!-- permet l'envoi de info du visiteur par URL -->
        <center><a href="{{ URL::action('gererFraisController@archive') }}" class="btn btn-default">RETOUR</a></center>
</fieldset>
</form>
@endsection 