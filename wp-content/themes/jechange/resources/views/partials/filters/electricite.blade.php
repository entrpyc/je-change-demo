TODO
<label><input type="radio" name="prixFixe" value="" @if($_GET['prixFixe'] == '') checked @endif>Tous les tarifs(1)</label>
<label><input type="radio" name="prixFixe" value="TRV" @if($_GET['prixFixe'] == 'TRV') checked @endif>
    Tarif indexé sur les <abbr title="Regulated sales rates">TRV</abbr> d'EDF
</label>
<label><input type="radio" name="prixFixe" value="" @if($_GET['prixFixe'] == 'FIXE') checked @endif>Tarif fixe</label>

 
<br>
<label><input type="checkbox" name="pourcentVert" value="100" id="TV" @if($_GET['pourcentVert'] == '100') checked @endif>Uniquement les offres d'énergie verte(2)</label>

 