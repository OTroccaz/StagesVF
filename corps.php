<div id="corps">
<html>
   <body>
      <center>
         <div>
            <h2>Importation de fichier</h2>
            <form method="post" action="fileupload.php" enctype="multipart/form-data" >
               <table border ="1" cellspacing="1" cellpadding="1">
                  <tr> <td align=center> <input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
                  Fichier .csv à importer </BR>
                  <input type="file" name="fichier_importe" id="monfichier" /> </td> <tr>
               </table>
               </BR></BR>
               <select name="type_de_donnees" size="1">
                  <option>Jeu de données
                  <option>Relevé
                  <option>Végétation
               </select>
               </BR></BR></BR>
               <input type="submit" value="Importer" id="submit" disabled="true" name="import" />
               </BR></BR></BR>
            </form>
            <form method="post" action="fileslist.php">
               <input type="submit" value="Télécharger les fichiers" name="list">
            </form>
         </div>
      <center>
   </body>
</html>
</div>
<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script>
$('input[type=file][name="fichier_importe"]').change(function(){
    var hasNoFiles = this.files.length == 0;
    $(this).closest('form') /* Select the form element */
       .find('input[type=submit]') /* Get the submit button */
       .prop('disabled', hasNoFiles); /* Disable the button. */
});
</script>
