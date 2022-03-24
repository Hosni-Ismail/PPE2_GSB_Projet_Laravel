@extends ('sommaire')
    @section('contenu1')
    <div id="contenu">
        <h2><center>Suppression visiteurs</center></h2>
        <h3>visiteur à sélectionner : </h3>
      <form action="{{ route('chemin_listeFrais') }}" method="post">
        {{ csrf_field() }} <!-- laravel va ajouter un champ caché avec un token -->
        @includeWhen($errors != null , 'msgerreurs', ['erreurs' => $errors]) 
        <div class="corpsForm"><p>
          <label for="lsvisiteurs" >Nom/Prenom : </label>
          <select id="lsvisiteuts" name="lsvisiteurs">
              @foreach($lesViteurs as $vi)
                    <option value="{{ $vi['id'] }}">
                      {{ $vi['nom']}}/{{$vi['prenom'] }} 
                    </option>
              @endforeach
          </select>
        </p>
        </div>
        <div class="piedForm">
        <p>
          <center><input id="ok" type="submit" value="Valider" size="20" /></center>
        </p> 
        </div>
        </form>
@endsection 