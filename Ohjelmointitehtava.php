<!DOCTYPE html>

<!-- TIETOKANTAJÄRJESTELMÄT (SQL) 2020
Ohjelmointitehtävä
Toni Kuikka (toni.kuikka@tuni.fi) -->

<?php

  // Luodaan tietokantayhteys ja ilmoitetaan mahdollisesta virheestä.
  $y_tiedot = "dbname=tk421568 user=tk421568 password=UNp6aXDEFGVVXlf";
  if (!$yhteys = pg_connect($y_tiedot))
    die("Tietokantayhteyden luominen epäonnistui.");

  // Aloitetaan sessio.
  session_start();

    // Luetaan syötteet, alustetaan muuttujat sekä tarkistetaan tietojen oikeellisuus.
    if (isset($_POST['tallenna'])) {
      $summa = intval($_POST['summa']);
      $veloitettava   = intval($_POST['veloitettava']);
      $saaja   = intval($_POST['saaja']);
      $tiedot_ok = $summa != 0 && $veloitettava != 0 && $saaja != 0;

      // Aloitetaan tapahtuma. Jos se ei jostakin syystä onnistu, tulostetaan virheilmoitus.
      pg_query('BEGIN')
      or die('Ei onnistuttu aloittamaan tapahtumaa:' . pg_last_error());

      if ($tiedot_ok) {
		// Yritetään ensin vähentää summa tietokannasta veloitettavan riviltä. Jos se ei
		// onnistu, keskeytetään koko tapahtuma ja tulostetaan virheilmoitus.
        $paivitys = pg_query("UPDATE tilit 
                              SET summa = summa - $summa 
                              WHERE tilinumero = $veloitettava AND summa >= $summa")
                    or die ('Virhe ensimmäisessä päivityksessä: ' . pg_last_error());
	    // Tarkistetaan vielä, että muuttuneita rivejä oli vain yksi. Muussa tapauksessa
		// tapahtuma keskeytetään.
        if (pg_affected_rows($paivitys) != 1) {
          pg_query('ROLLBACK')
          or die('Ei onnistuttu perumaan tapahtumaa: ' . pg_last_error());
          echo '<p style="color:red">Lähdetilin tilinumero on väärä tai saldoa ei ole tarpeeksi.</p>';
        }
		// Jos kaikki on mennyt tähän asti hyvin, jatketaan tapahtumaa lisäämällä summa
		// tietokannassa vastaanottajan riville. Jos se ei onnistu, keskeytetään koko 
		// tapahtuma ja tulostetaan virheilmoitus.
        else {
          $paivitys = pg_query("UPDATE tilit 
                                SET summa = summa + $summa 
                                WHERE tilinumero = $saaja")
                      or die('Virhe toisessa päivityksessä: ' . pg_last_error());
	      // Tarkistetaan jälleen, että muuttuneita rivejä oli vain yksi. Muussa tapauksessa
		  // tapahtuma keskeytetään.
          if (pg_affected_rows($paivitys) != 1) {
            pg_query('ROLLBACK')
            or die('Ei onnistuttu perumaan tapahtumaa: ' . pg_last_error());
            echo '<p style="color:red">Vastaanottajan tilinumero on väärä.</p>';
          }
		  // Jos tapahtumaa ei päädytty keskeyttämään missään if-haarassa, sitoudutaan siihen.
		  // Jos kohdataan jokin virhe, keskeytetään koko tapahtuma ja tulostetaan virheilmoitus.
          else { 
            pg_query('COMMIT')
            or die('Ei onnistuttu hyväksymään tapahtumaa: ' . pg_last_error());

            // Jos tapahtuma onnistui, alustetaan sessiomuuttujat ja siirrytään sivulle 
			// Ohjelmointitehtava2.php, joka ilmoittaa, kuka siirsi rahaa kenelle ja kuinka
			// paljon.
            $_SESSION['summa'] = $summa;
	        $veloitettavannimi = pg_query("SELECT omistaja FROM tilit WHERE tilinumero = $veloitettava");
            $_SESSION['veloitettavanimi'] = pg_fetch_row($veloitettavannimi)[0];
            $saajannimi = pg_query("SELECT omistaja FROM tilit WHERE tilinumero = $saaja");
            $_SESSION['saajannimi'] = pg_fetch_row($saajannimi)[0];
            header('Location: Ohjelmointitehtava2.php');
          }
       }
    }
	// Jos syötteet olivat virheellisiä, perutaan tapahtuma ja tulostetaan virheilmoitus.
    else {
        pg_query('ROLLBACK')
        or die('Ei onnistuttu perumaan tapahtumaa: ' . pg_last_error());
        echo '<p style="color:red">Annetut tiedot virheelliset - tarkista, ole hyvä!</p>';
    } 
  }
  // Suljetaan lopuksi tietokantayhteys.
  pg_close($yhteys);
?>


<html>
  <head>
    <meta charset="utf-8" />
    <link href="/style.css" rel="stylesheet" />
    <title>Tietokantajärjestelmät (SQL), Tampereen yliopisto</title>
    <h1>Ohjelmointitehtävä¤</h1>
  </head>

  <body>

<h2>Tilit</h2>

    <?php

    // Luodaan vielä tietokantayhteys Tilit-taulun tulostamiseksi sivulle.
    $y_tiedot = "dbname=tk421568 user=tk421568 password=UNp6aXDEFGVVXlf";
    if (!$yhteys = pg_connect($y_tiedot))
      die("Tietokantayhteyden luominen epäonnistui.");

    // Muodostetaan kysely kaikkien Tilit-taulun sarakkeiden saamiseksi. 
    $tulos = pg_query("SELECT * FROM tilit");
    if (!$tulos) {
      echo '<p style="color:red">Virhe kyselyssÃ¤.\n</p>';
      exit;
    }
    
	// Tulostetaan taulukko sivuston alkuun.
    echo "<table border='4' class='stats' cellspacing='0'>
      <tr>
        <td class='hed' colspan='8'><center>TILIT</center></td>
      </tr>
      <tr>
        <th>Tilinumero</th>
        <th>Omistaja</th>
        <th>Summa</th>
      </tr>";
      while ($rivi = pg_fetch_row($tulos)) {
        echo "<tr>";
          echo "<td>" . "$rivi[0]" . "</td>";
          echo "<td>" . "$rivi[1]" . "</td>";
          echo "<td>" . "$rivi[2] euroa" . "</td>";
        echo "</tr>";
      }
    echo "</table>";

    // Suljetaan tietokantayhteys.
    pg_close($yhteys);
    ?>
	
    <!-- Tilinsiirtolomake lähetetään samalle sivulle. -->
    <form action="Ohjelmointitehtava.php" method="post">

      <h2>Tilinsiirto</h2>

      <table border="0" cellspacing="0" cellpadding="3">

        <!-- Pyydetään käyttäjältä syötteinä siirrettävä summa, veloitettavan 
		tilinumero sekä tilinumero, jonne summa siirretään. -->
        <tr>
          <td>Siirrettävä summa</td>
    	   <td><input type="text" name="summa" value="" /></td>
        </tr>
        <tr>
          <td>Veloitettavan tilinumero</td>
          <td><input type="text" name="veloitettava" value="" /></td>
        </tr>
        <tr>
    	   <td>Tilinumero, jonne summa siirretään</td>
    	   <td><input type="text" name="saaja" value="" /></td>
        </tr>
      </table>

      <br>

	 <!-- Lähetetään lomakkeen tiedot php-ohjelmalle. Hidden-kenttää käytetään 
	 varotoimena, sillä esim. IE ei välttämättä lähetä submit-tyyppisen kentän 
	 arvoja, jos lomake lähetetään enterin painalluksella. Tätä arvoa 
	 tarkkailemalla voidaan skriptissä helposti päätellä, saavutaanko lomakkeelta. -->
      <input type="hidden" name="tallenna" value="ok" />
      <input type="submit" value="Tee tilinsiirto" />
    </form>

  </body>
</html>