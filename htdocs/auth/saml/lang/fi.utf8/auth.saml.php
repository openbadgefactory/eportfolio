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


$string['defaultinstitution'] = 'Default institution';

$string['description'] = 'Authenticate against a SAML 2.0 IdP service';

$string['errnosamluser'] = 'No User found';

$string['errorbadcombo'] = 'You can only choose user auto creation if you have not selected remoteuser';

$string['errorbadconfig'] = 'SimpleSAMLPHP config directory %s is in correct.';

$string['errorbadinstitution'] = 'Institution for connecting user not resolved';

$string['errorbadinstitutioncombo'] = 'On jo olemassa autentikointi-instanssi tämän oppilaitoksen attribuutin ja oppilaitoksen arvon yhdistelmänä';

$string['errorbadlib'] = 'SimpleSAMLPHP lib directory %s is not correct.';

$string['errorbadssphp'] = 'Epäkelpo SimpleSAMLphp session käsittelijä - ei pidä olla phpsessio';

$string['errorbadssphplib'] = 'Epäkelpo SimpleSAMLphp kirjasto konfiguraatio';

$string['errormissinguserattributes1'] = 'Näyttää siltä, että olet jo autentikoitu, mutta emme ole saaneet pyydettyjä käyttäjä attribuutteja. Tarkista, että Identity Provider julkistaa etunimen, sukunime ja sähköpostiosoite kentät SSO:hon %s tai tiedottaa admininstraattoria. ';

$string['errorregistrationenabledwithautocreate'] = 'Oppilaitoksella on rekisteröinti sallittu. Turvallisuussyistä tämä ei sisällä käyttäjien automaattista luontia. ';

$string['errorremoteuser'] = 'Etäkäyttäjän yhdistäminen on pakollista jos usersuniquebyusername on otettu pois päältä. ';

$string['errorretryexceeded'] = 'Maximum number of retries exceeded (%s) - there must be a problem with the Identity Service';

$string['institutionattribute'] = 'Institution attribute (contains "%s")';

$string['institutionregex'] = 'Do partial string match with institution shortname';

$string['institutionvalue'] = 'Institution value to check against attribute';

$string['link'] = 'Yhdistä tilit';

$string['linkaccounts'] = 'Haluatko yhdistää etätilin %s paikalliseen tiliin %s?';

$string['login'] = 'SSO';

$string['loginlink'] = 'Salli käyttäjien yhdistää omaan tiliin';

$string['logintolink'] = 'Paikallinen kirjautuminen %s yhdistää etätiliin';

$string['logintolinkdesc'] = '<p><b>Olet tällä hetkellä yhdistetty etäkäyttäjään %s. Kirjaudu sisään paikalliseen tiliisi yhdistääksesi heidät yhteen tai rekisteröidy, jos sisnulla ei ole tiliä %s. </b></p>';

$string['notusable'] = 'Please install the SimpleSAMLPHP SP libraries';

$string['remoteuser'] = 'Match username attribute to Remote username';

$string['samlfieldforemail'] = 'SSO field for Email';

$string['samlfieldforfirstname'] = 'SSO field for First Name';

$string['samlfieldforsurname'] = 'SSO field for Surname';

$string['simplesamlphpconfig'] = 'SimpleSAMLPHP config directory';

$string['simplesamlphplib'] = 'SimpleSAMLPHP lib directory';

$string['title'] = 'SAML';

$string['updateuserinfoonlogin'] = 'Update user details on login';

$string['userattribute'] = 'User attribute';

$string['weautocreateusers'] = 'We auto-create users';

