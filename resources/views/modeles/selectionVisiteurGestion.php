@extends ('sommaire')
    @section('contenu1')
      <div id="contenu">
        <h2>Mes visiteurs</h2>
        <h3>Visiteur a selectionner : </h3>
      <form action="{{ route('chemin_listeFrais') }}" method="">
        {{ csrf_field() }} <!-- laravel va ajouter un champ caché avec un token -->
        <div class="corpsForm">
        <p>
          <label for="lsnomPrenom" >Nom / Prenom: </label>
          <select id="lstMois" name="leNomPrenom">
              @foreach($nomPrenom as $nom)
                  @if ($nom['nom'] == $lenom)
                    <option selected value="{{ $nom['nom'] }}">
                      {{ $nom['nom']}}/{{$nom['prenom'] }} 
                    </option>
                  @else 
                    <option value="{{ $nom['nom'] }}">
                      {{ $nom['nom']}}/{{$nom['prenom'] }} 
                    </option>
                  @endif
              @endforeach
          </select>
        </p>
        </div>
        <div class="piedForm">
        <p>
          <input id="ok" type="submit" value="Valider" size="20" />
          <input id="annuler" type="reset" value="Effacer" size="20" />
        </p> 
        </div>
        </form>
  @endsection 
 