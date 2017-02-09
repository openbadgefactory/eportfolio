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


$string['approvalemailmessagehtml'] = '<p>Hei %s,</p>

Kiitos, että pyysit tiliä %s. Oppilaitoksen hallinnoijalle 
on ilmoitettu hakemuksestasi. Tulet saamaan toisen sähköpostin 
niin pian kuin hakemuksesi tarkasteltu. </p>

<pre>--
Terveisin,
 %s tiimi</pre>';

$string['approvalemailmessagetext'] = 'Hei %s,

Kiitos, että pyysit tiliä %s. Oppilaitoksen hallinnoijalle 
on ilmoitettu hakemuksestasi. Tulet saamaan toisen sähköpostin 
niin pian kuin hakemuksesi tarkasteltu. 

--
Terveisin,
 %s tiimi';

$string['approvalemailsubject'] = 'Pyyntö rekisteröinnille %s on saapunut';

$string['completeregistration'] = 'Täydellinen rekisteröinti';

$string['confirmcancelregistration'] = 'Haluatko varmasti peruuttaa rekisteröitymisen? Rekisteröitymispyyntösi poistetaan järjestelmästä.';

$string['confirmemailsubject'] = 'Vahista sähköpostiosoite rekisteröinnille %s';

$string['description'] = 'Sisäinen autentikointi';

$string['emailalreadytaken'] = 'Tämä sähköpostiosoite on jo käytössä';

$string['emailconfirmedok'] = '<p>Olet onnistuneesti vahvistanut sähköpostiosoitteesi. Saat ilmoituksen lisä rekisteröintitiedoista pian. </p>';

$string['iagreetothetermsandconditions'] = 'Hyväksyn käyttöehdot';

$string['internal'] = 'Sisäinen';

$string['passwordformdescription'] = 'Salasanasi täytyy olla vähintään kuusi merkkiä pitkä ja sen tulee sisältää vähintään yhden numeron ja kaksi kirjainta';

$string['passwordinvalidform'] = 'Salasanasi täytyy olla vähintään kuusi merkkiä pitkä ja sen tulee sisältää vähintään yhden numeron ja kaksi kirjainta';

$string['pendingregistrationadminemailhtml'] = '<p>Hei %s,</p>

<p>Uusi käyttäjä on pyytänyt liittymistä oppilaitokseen \'%s\'.</p>
<p>Koska sinut on listattu tämän oppilaitoksen hallinnoijaksi, sinun tulee hyväksyä tai hylätä tämä rekisteröintipyyntö. Tehdäksesi tämän, valitse seuraava linkki: <a href=\'%s\'>%s</a></p>
<p>Sinun tulee hyväksyä tai hylätä tämä rekisteröintipyyntö %s kuluessa.</p>
<p>Rekisteröintipyynnön yksityiskohdat:</p>
<p>Nimi: %s</p>
<p>Sähköpostiosoite: %s</p>
<p>Syy rekisteröintiin:</p>
<p>%s</p>
<pre>--
Terveisin,
 %s tiimi</pre>';

$string['pendingregistrationadminemailsubject'] = 'Uusi käyttäjärekisteröinti oppilaitokseen \'%s\' at %s.';

$string['pendingregistrationadminemailtext'] = 'Hei %s,

Uusi käyttäjä on pyytänyt liittymistä oppilaitokseen \'%s\'.

Koska sinut on listattu tämän oppilaitoksen hallinnoijaksi, sinun tulee hyväksyä tai hylätä tämä rekisteröintipyyntö. Tehdäksesi tämän, valitse seuraava linkki:  %s

Sinun tulee hyväksyä tai hylätä tämä rekisteörintipyyntö %s kuluessa.

Rekisteröintipyynnön yksityiskohdat:

Nimi: %s
Sähköpostiosoite: %s
Syy rekisteröintiin:
%s

--
Terveisin,
 %s tiimi';

$string['recaptcharegisterdesc'] = 'Kirjoita laatikossa näkyvät sanat järjestyksessä ja eroteltuna välilyönnillä. Näin autat estämään automaattisia ohjelmia väärinkäyttämään tätä palvelua. ';

$string['recaptcharegistertitle'] = 'reCAPTCHA vaatimus';

$string['registeredemailmessagehtml'] = '<p>Hei %s,</p> <p>Kiitos, että rekisteröit käyttäjätilin %s. Ole hyvä ja klikkaa tätä linkkiä suorittaaksesi loppuun sisäänkirjautumisprosessin: </p> <p><a href="%sregister.php?key=%s">%sregister.php?key=%s</a></p> <p>Linkki erääntyy 24 tunnin kuluessa.</p> <pre>-- Terveisin, %s Tiimi</pre>';

$string['registeredemailmessagetext'] = 'Hei%s, Kiitos, että rekisteröit käyttäjätilin %s. Ole hyvä ja klikkaa tätä linkkiä suorittaaksesi loppuun sisäänkirjautumisprosessin: %sregister.php?key=%s Linkki erääntyy 24 tunnin kuluessa. -- Terveisin, %s Tiimi';

$string['registeredemailsubject'] = 'Olet rekisteröitynyt palveluun %s';

$string['registeredok'] = '<p>Rekisteröitymisesi on onnistunut. Sähköpostiisi on lähetetty ohjeet kuinka aktivoit käyttäjätilisi</p>';

$string['registeredokawaitingemail2'] = 'Olet onnistuneesti jättänyt rekisteröintihakemuksesi. Oppilaitoksen hallinnoijalle on ilmoitettu, ja tulet saamaan sähköpostia niin pian kuin hakemuksesi on prosessoitu. ';

$string['registrationcancelledok'] = 'Olet peruuttanut rekisteröintipyynnön.';

$string['registrationconfirm'] = 'Vahvista rekisteröityminen?';

$string['registrationconfirmdescription'] = 'Oppilaitoksen pääkäyttäjän tulee hyväksyä rekisteröityminen.';

$string['registrationdeniedemailsubject'] = 'Rekisteröinti palveluun %s on hylätty.';

$string['registrationdeniedmessage'] = 'Hei %s,

Olemme saaneet liittymishakemuksesi oppilaitokseemme %s ja
päätimme olla myöntämättä pääsyä sinulle.

Jos tämä päätös oli mielestäsi virheellinen, ota minuun
 yhteyttä sähköpostitse.

Terveisin
%s';

$string['registrationdeniedmessagereason'] = 'Hei %s,

Olemme saaneet liittymishakemuksesi oppilaitokseemme %s ja
päättimme olla myöntämättä pääsyä sinulle seuraavasta syystä:

%s

Jos tämä päätös oli mielestäsi virheellinen, ota minuun
 yhteyttä sähköpostitse.

Terveisin
%s';

$string['registrationexpiredkey'] = 'Pahoittelut, avaimesi on erääntynyt. Odotit ehkä kauemmin kuin 24 tuntia saattaaksesi rekisteröitymisesi päätökseen? Muussa tapauksessa kyse voi olla meidän virheestä. ';

$string['registrationnosuchid'] = 'Pahoittelut, tätä rekisteröintiavainta ei ole olemassa. Ehkä se on jo aktivoitu? ';

$string['registrationnosuchkey1'] = 'Pahoittelut, meillä ei ole avainta, joka sopisi linkkiisi. Ehkä sähköpostiohjelmasi tuhosi sen? ';

$string['registrationreason'] = 'Rekisteröitymisen syy';

$string['registrationreasondesc1'] = 'Syy, miksi haluat liittyä valittuun oppilaitokseen ja mahdolliset pääkäyttäjän tarvitsemat lisätiedot päätöksentekoa varten.';

$string['registrationunsuccessful'] = 'Valitettavasti rekisteröintiyrityksesi epäonnistui. Tämän on meidän virhe, ei sinun. Ole hyvä ja yritä uudestaan.';

$string['title'] = 'Sisäinen';

$string['usernamealreadytaken'] = 'Valitettavasti tämä käyttäjätunnus on jo käytössä';

$string['usernameinvalidadminform'] = 'Käyttäjänimi voi sisältää kirjaimia, numeroita ja useimpia muita merkkejä. Sen tulee olla vähintään kolme ja enintään 236 merkkiä pitkä. Välilyönnit eivät ole sallittuja.';

$string['usernameinvalidform'] = 'Käyttäjätunnus voi sisältää kirjaimia, numeroita ja yleisempiä symboleja ja sen täytyy olla 3-30 merkin mittainen. Välilyönnit eivät ole sallittuja.';

$string['youmaynotregisterwithouttandc'] = 'Et voi rekisteröityä ellet hyväksy <a href="terms.php">Käyttöehdot</a>';

