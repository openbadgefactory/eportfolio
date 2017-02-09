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


$string['defaultinstitution'] = 'Standard läroanstalt';

$string['description'] = 'Verifiera mot SAML 2.0 IdP service';

$string['errnosamluser'] = 'Inga användare hittades';

$string['errorbadcombo'] = 'Du kan endast välja automatiskt skapande av användare om fjärranvändare inte är utvald.';

$string['errorbadconfig'] = 'SimpleSAMLPHP konfigurationskatalog %s är inkorrekt.';

$string['errorbadinstitution'] = 'Läroanstalt för anslutning av användare kunde inte lösas';

$string['errorbadinstitutioncombo'] = 'Det existerar redan en autentiserings instans med denna läroanstaltsattribut och läroanstaltsvärde kombinationen.';

$string['errorbadlib'] = 'SimpleSAMLPHP lib katalog %s är inte korrekt.';

$string['errorbadssphp'] = 'Ogiltig SimpleSAMLphp session hanterade - skall inte vara phpsession';

$string['errorbadssphplib'] = 'Ogiltig SimpleSAMLphp biblioteks konfiguration';

$string['errormissinguserattributes1'] = 'Du verkar vara autentiserad men vi fick inte reda på alla användarattributeja. Var god och kontrollera att din identitetsleverantör innehåller fälten förnamn, efternamn och e-postadress för SSO till %s eller meddela administratören.';

$string['errorregistrationenabledwithautocreate'] = 'En läroanstalt har aktiverat registrering. Av säkerhetsskäl är automatiskt skapande av användare inte aktiverat.';

$string['errorremoteuser'] = 'Matching för distansanvändare är obligatoriskt om usersuniquebyusername är avkopplad.';

$string['errorretryexceeded'] = 'Högsta antalet försök har överstigits (%s) - det måste vara ett fel i Identitetstjänsten';

$string['institutionattribute'] = 'Läroanstalts attribut (innehåller "%s")';

$string['institutionregex'] = 'Gör en partiell strängmatchning med läroanstaltens kortnamn';

$string['institutionvalue'] = 'Läroanstaltens värde kontrolleras mot attributet';

$string['link'] = 'Länka konton';

$string['linkaccounts'] = 'Vill du länka fjärrkontot %s med lokala kontot %s?';

$string['login'] = 'SSO';

$string['loginlink'] = 'Tillåt användare att länka egna konton';

$string['logintolink'] = 'Lokal inloggning på %s för att länka till ett fjärrkonto';

$string['logintolinkdesc'] = '<p><b>Du är för tillfället kopplad med fjärranvändaren %s. Var god och logga in till ditt lokala konto för att länka kontona eller registrera dig om du inte ännu har ett konto på %s.</b></p>';

$string['notusable'] = 'Var god och installera SimpleSAMLPHP SP bibliotek';

$string['remoteuser'] = 'Matcha användarnamn attribut med fjärranvändarnamn';

$string['samlfieldforemail'] = 'SSO fält för E-post';

$string['samlfieldforfirstname'] = 'SSO fält för Förnamn';

$string['samlfieldforsurname'] = 'SSO fält för Efternamn';

$string['simplesamlphpconfig'] = 'SimpleSAMLPHP konfigurationskatalog';

$string['simplesamlphplib'] = 'SimpleSAMLPHP lib katalog';

$string['title'] = 'SAML';

$string['updateuserinfoonlogin'] = 'Uppdatera användarinformation på login';

$string['userattribute'] = 'Användarattribut';

$string['weautocreateusers'] = 'Vi skapar automatiskt användare';

