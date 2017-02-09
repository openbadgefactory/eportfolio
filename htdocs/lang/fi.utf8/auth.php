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


$string['addauthority'] = 'Lisää valtuuttaja';

$string['application'] = 'Palvelu';

$string['authloginmsg2'] = 'Kun et ole valinnut valtuutusta, kirjoita viesti, joka näytetään kun käyttäjä yrittää kirjautua sisäänkirjautumislomakkeen kautta. ';

$string['authname'] = 'Valtuuttajan nimi';

$string['cannotjumpasmasqueradeduser'] = 'Et voi hypätä toiseen hakemukseen kun toimit toisena käyttäjänä. ';

$string['cannotremove'] = 'Tunnistuspluginia ei voi poistaa, koska se on tämän oppilaitoksen ainoa.';

$string['cannotremoveinuse'] = 'Tunnistuspluginia ei voi poistaa, koska se on käytössä joillain käyttäjillä. Näiden tiedot tulee päivittää, ennen kuin pluginin voi poistaa.';

$string['cantretrievekey'] = 'Julkisen avaimen hakemisessa tapahtui virhe.<br>Tarkista, että sovellus ja www-root ovat oikein määritelty ja että etäpalvelimen verkkoasetukset ovat oikein määritelty.';

$string['changepasswordurl'] = 'Salasananvaihto-osoite';

$string['duplicateremoteusername'] = 'Tämä ulkoinen käyttäjätunnus on jo käytössä käyttäjällä %s. Ulkoisten tunnusten tulee olla yksilöllisiä kussakin tunnistautumistavassa.';

$string['duplicateremoteusernameformerror'] = 'Ulkoisten tunnusten tulee olla yksilöllisiä kussakin tunnistautumistavassa.';

$string['editauthority'] = 'Muokkaa valtuuttajaa';

$string['errnoauthinstances'] = 'Tunnistuspluginia ei ole määritetty palvelimelle %s';

$string['errnoxmlrpcinstances'] = 'XMLRPC-autentikaatio ei ole käytössä palvelimelle %s';

$string['errnoxmlrpcuser1'] = 'Emme pysty autentikoimaan sinua tällä kertaa. Mahdollisia syitä voivat olla:

    * SSO-istuntosi voi olla erääntynyt. Mene takaisin toiseen ohjelmaan ja napsauta linkkiä kirjautuaksesi uudestaan Go back to %s.
    * Sinulla ei ole mahdollisesti pääsyä SSO:lla palveluun %s. Tarkista ylläpitäjältäsi mikäli sinun pitäisi päästä palveluun.';

$string['errnoxmlrpcwwwroot'] = 'Palvelimesta %s ei ole tietoja';

$string['errorcertificateinvalidwwwroot'] = 'Sertifikaatti on luotu osoitteelle %s, mutta sitä yritetään käyttää %s:lle';

$string['errorcouldnotgeneratenewsslkey'] = 'Uuden SSL-avaimen luonti epäonnistui. Tarkista, että OpenSSL ja sen PHP-moduuli ovat asennettuina.';

$string['errornotvalidsslcertificate'] = 'Tämä SSL-sertifikaatti ei kelpaa';

$string['host'] = 'Isäntäkoneen nimi tai osoite';

$string['hostwwwrootinuse'] = 'WWW-polku on jo toisen oppilaitoksen käytössä (%s)';

$string['ipaddress'] = 'IP-osoite';

$string['mobileuploadnotenabled'] = 'Mobiililataus on poistettu käytöstä.';

$string['mobileuploadtokennotfound'] = 'Tätä mobiililatausavainta ei löydy. Tarkista mobiilisovelluksen asetukset.';

$string['mobileuploadtokennotset'] = 'Mobiililatausavain ei voi olla tyhjä. Tarkista mobiilisovelluksen asetukset.';

$string['mobileuploadusernamenotset'] = 'Mobiililatauksen käyttäjänimi ei voi olla tyhjä. Tarkista mobiilisovelluksen asetukset.';

$string['name'] = 'Palvelun nimi';

$string['noauthpluginconfigoptions'] = 'Tälle pluginille ei ole asetuksia';

$string['nodataforinstance'] = 'Tunnistusinstanssin tietoja ei löydy ';

$string['parent'] = 'Isäntävaltuuttaja';

$string['primaryemaildescription'] = 'Ensisijainen sähköpostiosoite. Saat sähköpostin, jossa on napsautettava linkki - seuraa linkkiä vahvistaaksesi osoitteen ja kirjautuaksesi järjestelmään. ';

$string['protocol'] = 'Protokolla';

$string['requiredfields'] = 'Pakolliset profiilitiedot';

$string['requiredfieldsset'] = 'Pakolliset profiilitiedot asetettu';

$string['saveinstitutiondetailsfirst'] = 'Tallenna oppilaitoksen tiedot ennen tunnistuspluginien asettamista.';

$string['shortname'] = 'Palvelusi lyhyt nimi';

$string['ssodirection'] = 'SSO-suunta';

$string['theyautocreateusers'] = 'Etäpalvelin luo automaattisesti käyttäjätilejä';

$string['theyssoin'] = 'Etäpalvelimelta kirjaudutaan sisään';

$string['toomanytries'] = 'Olet käyttänyt maksimimäärän kirjautumisyrityksiä. Tämä tili on lukittu 5 minuutiksi. ';

$string['unabletosigninviasso'] = 'SSO epäonnistui';

$string['updateuserinfoonlogin'] = 'Päivitä käyttäjän tiedot kirjautumisen yhteydessä';

$string['updateuserinfoonlogindescription'] = 'Hae ja päivitä käyttäjän tiedot etäpalvelimelta jokaisella kirjautumiskerralla.';

$string['validationprimaryemailsent'] = 'Vahvistussähköposti on lähetetty. Napsauta viestissä olevaa linkkiä vahvistaaksesi osoitteen';

$string['weautocreateusers'] = 'Tämä palvelin luo automaattisesti käyttäjätilejä';

$string['weimportcontent'] = 'Sisältöä voi tuoda tälle palvelimelle';

$string['weimportcontentdescription'] = '(koskee vain joitain palveluja)';

$string['wessoout'] = 'Tältä palvelimelta kirjaudutaan ulospäin';

$string['wwwroot'] = 'WWW root';

$string['xmlrpccouldnotlogyouin'] = 'Sisäänkirjautuminen epäonnistui';

$string['xmlrpccouldnotlogyouindetail1'] = 'Pahoittelut, emme pysty tällä kertaa kirjaamaan sinua sisään palveluun %s. Koeta pian uudelleen. Mikäli ongelma jatkuu, ota yhteyttä palvelun ylläpitäjään. ';

$string['xmlrpcserverurl'] = 'XML-RPC -palvelimen osoite';

