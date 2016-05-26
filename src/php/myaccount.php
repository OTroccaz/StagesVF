<?php
session_start();
if (isset($_SESSION['connect'])) // On vérifie que le variable existe.
{
   $connect = $_SESSION['connect']; // On récupère la valeur de la variable de session.
}
else
{
   $connect = 0; // Si $_SESSION['connect'] n'existe pas, on donne la valeur "0".
}

if ($connect == "1") // Si le visiteur s'est identifié.
{
   /* CONNEXION BDD */
   include ('../../config/connection.php');
   $bdd = connexionMySQL();
   /* CONNEXION FAITE */

   $requete = $bdd->query("SELECT firstname, lastname, pseudo, password, email FROM users WHERE pseudo = '". $_SESSION['pseudo'] ."'");
   $donnees = $requete->fetch();

   $firstname = $donnees['firstname'];
   $lastname = $donnees['lastname'];
   $email = $donnees['email'];

   include("../html/myaccount.htm");

   // Si on clique sur enregistrer
   if(isset($_POST['Enregistrer']))
   {
      $new_firstname = htmlspecialchars($_POST['firstname']);
      $new_lastname = htmlspecialchars($_POST['lastname']);
      $new_email = htmlspecialchars($_POST['email']);

      // On vérifie si un des champs des mots de passe est rentré
      if(($_POST['old_password'] != '') && ($_POST['new_password_1'] != '') && ($_POST['new_password_2'] != ''))
      {
         $old_password = sha1($_POST['old_password']);
         $new_password_1 = sha1($_POST['new_password_1']);
         $new_password_2 = sha1($_POST['new_password_2']);
         $requete2 = $bdd->query("SELECT password FROM users WHERE pseudo = '" . $_SESSION['pseudo'] . "'");
         $donnees2 = $requete2->fetch();
         if ($old_password == $donnees2['password'])
         {
            if($new_password_1 == $new_password_2)
            {
               $sql = "UPDATE users SET firstname = '" . $new_firstname . "', lastname = '" . $new_lastname;
               $sql .= "', email = '" . $new_email . "', password = '" . $new_password_1 . "' WHERE pseudo = '" . $_SESSION['pseudo'] . "'";
               $requete = $bdd->exec($sql); // Requete avec changement de mot de passe
               ?>
               <center>
                  <div class="alert alert-success" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
                     Modification des informations personnelles et du mot de passe effectuées !
                  </div>
               </center>
               <?php
            } else {
               ?>
               <center>
                  <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
                     Vos deux mots de passe diffèrent.<br> Enregistrement échoué.
                  </div>
               </center>
               <?php
            }
         } else {
            ?>
            <center>
               <div class="alert alert-danger" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
                  Vous vous êtes trompé(e) de mot de passe actuel.<br> Enregistrement échoué.
               </div>
            </center>
            <?php
         }
      } else { // Sinon on update dans la BDD (on écrase toutes les infos) sans les mots de passe
         $sql = "UPDATE users SET firstname = '" . $new_firstname . "', lastname = '" . $new_lastname . "', email = '" . $new_email . "' WHERE pseudo = '" . $_SESSION['pseudo'] . "'";
         $requete = $bdd->exec($sql); // Requete sans changement de mot de passe
         ?>
         <center>
            <div class="alert alert-success" role="alert" style="display:inline-block;list-style-type:none;text-align:center">
               Modification des informations personnelles effectuées !
            </div>
         </center>
         <?php
      }
      $firstname = $new_firstname;
      $lastname = $new_lastname;
      $email = $new_email;
   }

   // On met à jour les inputs avec les bonnes informations
   ?>
   <script type="text/javascript">
   document.getElementById("firstname").value = "<?php echo $firstname ?>";
   document.getElementById("lastname").value = "<?php echo $lastname ?>";
   document.getElementById("email").value = "<?php echo $email ?>";
   </script>
   <?php
} else {
   ?>
   <head>
      <meta charset="utf-8" />
      <title>Connexion VegFrance</title>
   </head>
   <?php
   include('../html/sign_in.htm');
   echo '<p style="text-align:center;color:red">Vous n\'êtes pas autorisé(e) à accéder à cette zone</p>';
   exit;
}
?>
