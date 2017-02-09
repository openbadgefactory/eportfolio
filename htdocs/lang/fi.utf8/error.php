<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage lang
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2014 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();


$string['accessdenied'] = 'Pääsy kielletty';

$string['accessdeniedexception'] = 'Sinulla ei ole oikeutta katsoa tätä sivua';

$string['apcstatoff'] = 'Palvelimella näyttäisi olevan APC käytössä asetuksella apc.stat=0. Mahara ei tue tätä asetusta.
 Tämän tulisi olla apc.stat=1 php.ini-tiedostossa. Jos käytössä on web-hotelli, ota yhteyttä palveluntarjoajaan asetuksen muuttamiseksi.';

$string['artefactnotfound'] = 'Tuotosta id:llä %s ei löydy';

$string['artefactnotfoundmaybedeleted'] = 'Tuotosta id:llä %s ei löydy (mahdollisesti poistettu?)';

$string['artefactnotinview'] = 'Tuotosta %s ei löydy sivulta %s';

$string['artefactonlyviewableinview'] = 'Tämän tyyppisiä tuotoksia voi käyttää vain portfoliosivuilla';

$string['artefactpluginmethodmissing'] = 'Tuotoslaajennoksesta %s puuttuu %s';

$string['artefacttypeclassmissing'] = 'Tuotostyyppien tulisi jokaisen määritellä luokka. %s puuttuu.';

$string['artefacttypemismatch'] = 'Tuotostyypit eivät täsmää. %s ei ole %s';

$string['artefacttypenametaken'] = 'Tuotostyyppiä %s käyttää jo toinen laajennos (%s)';

$string['blockconfigdatacalledfromset'] = 'Asetuksia ei tulisi tehdä suoraan. Käytä PluginBlocktype::instance_config_save-metodia';

$string['blockinstancednotfound'] = 'Lohkoa id:llä %s ei löydy';

$string['blocktypelibmissing'] = 'Tuotoslaajennoksen %s lohkosta %s puuttuu tiedosto lib.php';

$string['blocktypemissingconfigform'] = 'Lohkotyypin %s tulee toteuttaa metodi instance_config_form';

$string['blocktypenametaken'] = 'Lohkotyyppiä %s käyttää jo toinen laajennos (%s)';

$string['blocktypeprovidedbyartefactnotinstallable'] = 'Tämä asennetaan osana tuotoslaajennosta %s';

$string['classmissing'] = 'Luokka %s tyypille %s laajennoksessa %s puuttuu';

$string['couldnotmakedatadirectories'] = 'Jostain syystä datakansioiden luonti epäonnistui. Tätä ei pitäisi tapahtua, koska kirjoitusoikeudet tarkastettiin aiemmin. Tarkasta uudelleen, että oikeudet ovat riittävät.';

$string['curllibrarynotinstalled'] = 'Palvelimelle ei ole asennettu curl-laajennosta. Mahara tarvitsee tämän syötteiden hakemista ja Moodle-integraatiota varten. Tarkista, että se on asennettu ja ladataan php.ini-tiedostossa.';

$string['datarootinsidedocroot'] = 'Datakansio on luotu www-kansion sisään. Tämä on tietoturva-aukko, koska kuka hyvänsä pääsee käsiksi istuntotietoihin tai ladattuihin tiedostoihin. Määrittele datakansio toiseen paikkaan.';

$string['datarootnotwritable'] = 'Määriteltyyn datakansioon %s ei voi kirjoittaa. Palvelimelle ei voi tallentaa istuntotietoja, ladattuja tiedostoja, eikä mitään muutakaan. Luo kansio, jos sitä ei ole olemassa tai tarkista käyttöoikeudet.';

$string['dbconnfailed'] = 'Tietokantayhteyden luonti epäonnistui.
* Yritä hetken kuluttua uudelleen.
* Pyydä ylläpitoa tarkistamaan tietokanta-asetukset. Virheilmoitus:
';

$string['dbnotutf8'] = 'Mahara tallentaa kaikki tiedot sisäisesti UTF-8 -muodossa. Tietokanta tulee luoda uudelleen käyttäen tätä koodaustapaa.';

$string['dbversioncheckfailed'] = 'Tietokantapalvelimen versio %s %s on liian vanha. Mahara tarvitsee vähintään version %s.';

$string['domextensionnotloaded'] = 'Palvelimelle ei ole asennettu dom-laajennosta. Mahara tarvitsee tämän XML-datan käsittelyä varten.';

$string['gdextensionnotloaded'] = 'Palvelimelle ei ole asennettu gd-laajennosta. Mahara tarvitsee tämän kuvien käsittelyä varten. Tarkista, että se on asennettu ja ladataan php.ini-tiedostossa.';

$string['gdfreetypenotloaded'] = 'Palvelimen gd-laajennokseen ei kuulu Freetype-tukea. Mahara tarvitsee tämän CAPTHCA-kuvien luontia varten. Tarkista, että gd on asennettu ja konfiguroitu sen kanssa.';

$string['gdlibrarylacksgifsupport'] = 'Asennettu PH GD kirjasto ei tue sekä GIF-kuvien luomista ja lukemista. GIF-kuvien lataamiseen tarvitaan täysi tuki. ';

$string['gdlibrarylacksjpegsupport'] = 'Asennettu PH GD kirjasto ei tue JPEG/JPG-kuvia. JPEG/JPG-kuvien lataamiseen tarvitaan täysi tuki. ';

$string['gdlibrarylackspngsupport'] = 'Asennettu PH GD kirjasto ei tue PNG-kuvia. PNG-kuvien lataamiseen tarvitaan täysi tuki. ';

$string['interactioninstancenotfound'] = 'Toimintoa id:llä %s ei löydy';

$string['invaliddirection'] = 'Viheellinen suunta: %s';

$string['invalidlayoutselection'] = 'Yritit valita ulkoasun, jota ei ole olemassa.';

$string['invalidnumrows'] = 'Yritit luoda ulkoasun, jossa on oli enemmän kuin sallittu määrä rivejä. (Tämän ei pitäisi olla mahdollista; ilmoita asiasta palvelun ylläpitäjälle.)';

$string['invalidviewaction'] = 'Virheellinen toiminto: %s';

$string['jsonextensionnotloaded'] = 'Palvelimelle ei ole asennettu JSON-laajennosta. Mahara tarvitsee tämän tiedonsiirtoa varten. Tarkista, että se on asennettu ja ladataan php.ini-tiedostossa.';

$string['magicquotesgpc'] = 'You have dangerous PHP settings, magic_quotes_gpc is on. Mahara is trying to work around this, but you should really fix it. If you are using shared hosting and your host allows for it, you should include the following line in your .htaccess file:
+php_flag magic_quotes_gpc off';

$string['magicquotesruntime'] = 'You have dangerous PHP settings, magic_quotes_runtime is on. Mahara is trying to work around this, but you should really fix it. If you are using shared hosting and your host allows for it, you should include the following line in your .htaccess file:
+php_flag magic_quotes_runtime off';

$string['magicquotessybase'] = 'You have dangerous PHP settings, magic_quotes_sybase is on. Mahara is trying to work around this, but you should really fix it. If you are using shared hosting and your host allows for it, you should include the following line in your .htaccess file:
+php_flag magic_quotes_sybase off';

$string['mbstringneeded'] = 'Asenna mbstring laajennus PHP:lle. Tätä tarvitaan, mikäli käyttäjänimissä on UTF-8 merkkejä. Muutoin käyttäjät eivät pysty kirjautumaan sisään. ';

$string['missingparamblocktype'] = 'Valitse ensin lohkotyyppi';

$string['missingparamcolumn'] = 'Kenttämääre puuttuu';

$string['missingparamid'] = 'Id puuttuu';

$string['missingparamorder'] = 'Järjestysmääre puuttuu';

$string['missingparamrow'] = 'Puuttuu rivin määritys';

$string['mysqldbextensionnotloaded'] = 'Palvelimelle ei ole asennettu mysql-laajennosta. Mahara tarvitsee tämän tietokantayhteyttä varten. Tarkista, että se on asennettu ja ladataan php.ini-tiedostossa.';

$string['mysqlnotriggerprivilege'] = 'Mahara vaatii luvan tietokanta triggereiden luomiseksi, mutta ei pysty tekemään niin. Varmista että triggerin oikeus on myönnetty sopivalle käyttäjälle MySQL asennuksessasi. Ohjeet tämän tekemiseen löydät täältä: https://wiki.mahara.org/index.php/System_Administrator\'s_Guide/Granting_Trigger_Privilege';

$string['nopasswordsaltset'] = 'Palvelunlaajuista salasanakryptausta ei ole asetettu. Muokkaa config.php ja aseta "passwordsaltmain" parametriksi järkevä salainen lauseke.';

$string['noreplyaddressmissingorinvalid'] = 'Vastausosoitteen asetus on joko tyhjä tai siinä on kelvoton sähköpostiosoite. Tarkista konfiguraatio <a href="%s">palvelun sähköpostiasetuksista</a>.';

$string['notartefactowner'] = 'Et omista tätä tuotosta';

$string['notenoughsessionentropy'] = 'PHP session.entropy_length asetus on liian pieni. Aseta se php.ini:ssä vähintään 16 varmistaaksesi, että generoituvat istunto-ID:t ovat tarpeeksi sattumanvaraisia ja ennalta-arvaamattomia.';

$string['notfound'] = 'Ei löydy';

$string['notfoundexception'] = 'Sivua jota etsit ei löydy';

$string['notproductionsite'] = 'Tämä palvelu ei ole tuotantokäytössä. Osa tiedoista ei ole mahdollisesti käytössä ja/tai on päivittämättä. ';

$string['onlyoneblocktypeperview'] = 'Sivulla voi olla ainoastaan yksi alue tyyppiä %s';

$string['onlyoneprofileviewallowed'] = 'Ainoastaan yksi profiilisivu sallittu';

$string['openbasedirenabled'] = 'Palvelimella on php open_basedir rajoitus käytössä. ';

$string['openbasedirpaths'] = 'Mahara voi avata tiedostoja ainoastaan seuraavien polkujen sisällä: %s.';

$string['openbasedirwarning'] = 'Joidenkin pyyntöjen suorittaminen loppuun ulkoisiin palveluihin voi epäonnistua. Tämä voi estää joidenkin syötteiden päivittymisen muiden asioiden lisäksi.  ';

$string['parameterexception'] = 'Pakollinen parametri puuttuu';

$string['passwordsaltweak'] = 'Palvelunlaajuinen salsasanakryptauksesi ei ole tarpeeksi vahva. Muokkaa config.php ja aseta "passwordsaltmain" parametriin pidempi salainen lauseke. ';

$string['pgsqldbextensionnotloaded'] = 'Palvelimelle ei ole asennettu pgsql-laajennosta. Mahara tarvitsee tämän tietokantayhteyttä varten. Tarkista, että se on asennettu ja ladataan php.ini-tiedostossa.';

$string['phpversion'] = 'PHP tulisi päivittää. Mahara tarvitsee vähintään version %s.';

$string['pleaseloginforjournals'] = 'Sinun täytyy kirjautua ulos ja takaisin sisään, ennen kuin näet kaikki blogisi ja merkintäsi.';

$string['plpgsqlnotavailable'] = 'PL/pgSQL kieli ei ole sallittu Postgres asennuksessasi, ja Mahara ei voi sallia sitä. Asenna PL/pgSQL tietokantaasi manuaalisesti. Ohjeet löydät täältä: https://wiki.mahara.org/index.php/System_Administrator\'s_Guide/Enabling_Plpgsql';

$string['postmaxlessthanuploadmax'] = 'Your PHP post_max_size setting (%s) is smaller than your upload_max_filesize setting (%s).  Uploads larger than %s will fail without displaying an error.  Usually, post_max_size should be much larger than upload_max_filesize.';

$string['previewimagegenerationfailed'] = 'Pahoittelut, kuvan esikatselun luomisessa oli ongelmia. ';

$string['registerglobals'] = 'You have dangerous PHP settings, register_globals is on. Mahara is trying to work around this, but you should really fix it. If you are using shared hosting and your host allows for it, you should include the following line in your .htaccess file:
+php_flag register_globals off';

$string['safemodeon'] = 'Palvelin käyttää safe_modea, jota Mahara ei tue. Tämä tulee asettaa pois käytöstä joko php.ini-tiedostossa tai Apachen asetuksissa. Jos käytät web-hotellia, pyydä palveluntarjoajaa kääntämään safe_mode pois päältä.';

$string['sessionextensionnotloaded'] = 'Palvelimelle ei ole asennettu istuntolaajennosta. Mahara tarvitsee tämän käyttäjien kirjautumisia varten. Tarkista, että se on asennettu ja ladataan php.ini-tiedostossa.';

$string['sessionpathnotwritable'] = 'Istuntosi tietohakemistoon, %s, ei voi kirjoittaa. Luo hakemisto, mikäli sitä ei ole vielä olemassa tai mikäli seon olemassa, anna hakemiston omistajuus verkkopalvelimen käyttäjälle. ';

$string['smallpostmaxsize'] = 'Your PHP post_max_size setting (%s) is very small.  Uploads larger than %s will fail without displaying an error.';

$string['switchtomysqli'] = '<strong>mysqli</strong> PHP-laajennusta ei ole asetettu serverillesi. Siksi, Mahara siirtyy takaisin alkuperäiseen vanhentuneeseen  <strong>mysql</strong> PHP-laajennukseen. Suosittelemme asennusta <a href="http://php.net/manual/en/book.mysqli.php">mysqli</a>.';

$string['themenameinvalid'] = 'Teeman nimi \'%s\' sisältää kiellettyjä merkkejä.';

$string['timezoneidentifierunusable'] = 'PHP on your website host does not return a useful value for the timezone identifier (%%z) - certain date formatting, such as the LEAP2A export, will be broken. %%z is a PHP date formatting code. This problem is usually due to a limitation in running PHP on Windows.';

$string['unabletosetmultipleblogs'] = 'Salli useat blogit käyttäjälle %s kun sivun %s kopiointi on epäonnistunut. Tämä voidaan asettaa manuaalisesti <a href="%s">Käyttäjätili</a> sivulla.';

$string['unknowndbtype'] = 'Asetuksissa on määritelty tuntematon tietokantapalvelin. Oikeita arvoja ovat "postgres8" ja "mysql5". Ole hyvä ja korjaa tietokantatyyppi config.php tiedostoon.';

$string['unrecoverableerror'] = 'Palautumaton virhe tapahtui. Tämä todennäköisesti tarkoittaa  bugia järjestelmässä.';

$string['unrecoverableerrortitle'] = '%s - Sivusto ei ole käytössä';

$string['versionphpmissing'] = 'Tiedosto version.php puuttuu laajennoksesta %s %s';

$string['viewnotfound'] = 'Sivua, jonka id on %s ei löydy';

$string['viewnotfoundbyname'] = 'Sivua %s tekijältä %s ei löydy.';

$string['viewnotfoundexceptionmessage'] = 'Yrität katsoa sivua, jota ei ole olemassa.';

$string['viewnotfoundexceptiontitle'] = 'Sivua ei löydy';

$string['wwwrootnothttps'] = 'Määrittämäsi wwwroot, %s, ei ole HTTPS. Kuitenkin, muut asaennuksesi asetukset (kuten sslproxy) vaativat, että wwwroot on HTTPS-osoite.

Päivitä wwwroot asetukset HTTPS-osoiteeksi tai korjaa väärät asetukset.';

$string['xmlextensionnotloaded'] = 'Palvelimelle ei ole asennettu laajennosta %s. Mahara tarvitsee tämän XML-data käsittelyä varten. Tarkista, että se on asennettu ja ladataan php.ini-tiedostossa.';

$string['youcannotviewthisusersprofile'] = 'Et voi katsella tämän käyttäjän profiilia';

