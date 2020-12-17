
<!DOCTYPE html>

<!-- TIETOKANTAJÄRJESTELMÄT (SQL) 2020
Ohjelmointitehtävä
Toni Kuikka (toni.kuikka@tuni.fi) -->

<?php

  // Koska Ohjelmointitehtava2.php sisältyy samaan istuntoon, toistetaan
  // sessiofunktiokutsu myös tämän sivun alussa.
  session_start();

  // Tulostetaan ilmoitus onnistuneesta tilinsiirrosta sessiomuuttajia hyväksikäyttäen.
  echo $_SESSION['veloitettavanimi'].' on siirtänyt '.$_SESSION['summa'].
  ' euroa henkilölle '.$_SESSION['saajannimi'].'.<br /><br />';
  
  // Lopetetaan sessio ja siirrytään takaisin sivulle Ohjelmointitehtava.php.
  if (isset($_POST['lopeta'])) {
    session_destroy();
    header('Location: Ohjelmointitehtava.php');
  }
?>

<html>
  <head>
    <meta charset="utf-8" />
    <link href="/style.css" rel="stylesheet" />
    <title>Tietokantajärjestelmät (SQL), Tampereen yliopisto</title>
  </head>

  <body>
    <!-- Näytetään php-ohjelman tulosteet tässä kohtaa HTML-sivua. -->
    <form method="post" action="Ohjelmointitehtava2.php">
      <input type="submit" name ="lopeta" value="OK"/>
    </form>
  </body>

</html>
