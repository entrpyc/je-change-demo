Dégroupage 
<label><input type="radio" name="typeDeLigne" value="" @if($_GET['typeDeLigne'] == '') checked @endif> Indifférent</label>
<label><input type="radio" name="typeDeLigne" value="1" @if($_GET['typeDeLigne'] == '1') checked @endif> dégroupé</label>
<label><input type="radio" name="typeDeLigne" value="2" @if($_GET['typeDeLigne'] == '2') checked @endif> non dégroupé</label>

<br>
<label><input type="checkbox" name="television" value="1" id="TV" @if($_GET['television'] == '1') checked @endif>Télévision</label>

TODO <label><input type="checkbox" name="telephonieMobile" value="1" id="Appel" @if($_GET['telephonieMobile'] == '1') checked @endif> Appels illimités vers les mobiles</label>

<br>
Débits :   
<label><input type="radio" name="debit-100" value="" @if($_GET['debit-100'] == '') checked @endif> Indifférent</label>
<label><input type="radio" name="debit-100" value="99" @if($_GET['debit-100'] == '99') checked @endif> jusqu'à 100 Mb/s</label>
<label><input type="radio" name="debit-100" value="100" @if($_GET['debit-100'] == '100') checked @endif> à partir de 100 Mb/s</label>

     