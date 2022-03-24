@extends ('sommaire')
    @section('contenu1')
   <div id="contenu">
        <h2><center>archives visiteurs</center></h2>
        <h3>visiteur archive à sélectionner : </h3>
      <form action="{{ route('chemin_infoarchive') }}" method="get">
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
        <input id="ok" type="submit" value="Informations" size="20"/>
        </div>
        </form>
@endsection 
