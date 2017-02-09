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


$string['addauthority'] = 'Lägg till en fullmaktsgivare';

$string['application'] = 'Program';

$string['authloginmsg2'] = 'Då du inte har valt en fullmaktsgivarvärd, mata in ett meddelande som visas då en användare försöker logga in via login formuläret';

$string['authname'] = 'Namnet på fullmaktsgivaren';

$string['cannotjumpasmasqueradeduser'] = 'Du kan inte byta till en annan applikation medan du agerar som en annan användare.';

$string['cannotremove'] = 'Vi kan inte ta bort den här auktoritetspluginen, eftersom det är den enda 
 pluginen som existerar för den här läroanstalten';

$string['cannotremoveinuse'] = 'Vi kan inte ta bort den här auktoritetspluginen, eftersom den används av några användare.
Du måste uppdatera deras data innan du kan ta bort den här pluginen.';

$string['cantretrievekey'] = 'Ett fel uppstod vid försöket att hämta en publik nyckel från fjärrservern.<br>Vänligen kontrollera att program- och webbrots-fälten är korrekta och att nätverksarbetande är möjligt för fjärrvärden.';

$string['changepasswordurl'] = 'URL för ändring av lösenord';

$string['duplicateremoteusername'] = 'Det här externa autentiseringsanvändarnamn används redan av användaren %s. Externa autentiseringasanävndarnamn måste vara unika inom autetinseringsmetoden.';

$string['duplicateremoteusernameformerror'] = 'Externa autentiseringsanvändarnamn måste vara unika inom autetinseringsmetoden.';

$string['editauthority'] = 'Redigera en fullmaktsgivare';

$string['errnoauthinstances'] = 'Det verkar som om vi inte har några autentiseringspluginsinstanser konfigurerade för värden hos %s.';

$string['errnoxmlrpcinstances'] = 'Det verkar som om vi inte har några XML-RPC-autentiseringspluginsinstanser konfigurerade för värden hos %s.';

$string['errnoxmlrpcuser1'] = 'Vi kunde inte autentiska dig. Möjliga orsak:
 
    * Din SSO session har gått ut. Återvänd till den andra applikationen och klicka på länken för att logga in till %s igen.
    * Du kanske inte har rättigheter att SSO till %s. Var vänlig och kontakta din administratör ifall du tror att du borde ha tillgång.';

$string['errnoxmlrpcwwwroot'] = 'Vi har inga uppgifter om en värd vid %s.';

$string['errorcertificateinvalidwwwroot'] = 'Det här certifikatet påstår sig vara för %s, men du försöker använda det för %s.';

$string['errorcouldnotgeneratenewsslkey'] = 'Kunde inte generera en ny SSL-nyckel. Är du säker på att både openssl och PHP-modulen för openssl är installerade på den här maskinen?';

$string['errornotvalidsslcertificate'] = 'SSL-certifikatet är inte giltigt.';

$string['host'] = 'Värdnamn eller -adress';

$string['hostwwwrootinuse'] = 'Webbroten används redan av en annan läroanstalt (%s).';

$string['ipaddress'] = 'IP-adress';

$string['mobileuploadnotenabled'] = 'Tyvärr är mobiluppladdning inte aktiverad.';

$string['mobileuploadtokennotfound'] = 'Tyvärr hittades inte verifieringsnyckeln för mobiluppladdning. Var god och kontrollera din tjänst och mobilinställningar.';

$string['mobileuploadtokennotset'] = 'Din verifieringsnyckel för mobiluppladdning kan inte vara tom. Var god och kontrollera dina mobilinställningar och försök igen.';

$string['mobileuploadusernamenotset'] = 'Ditt användarnamn för mobiluppladdning kan inte vara tomt. Var god och kontrollera dina mobilinställningar och försök igen.';

$string['name'] = 'Webbtjänstnamn';

$string['noauthpluginconfigoptions'] = 'Det finns inga konfigureringsalternativ för den här pluginen.';

$string['nodataforinstance'] = 'Hittade inte data för auktoritetsinstansen.';

$string['parent'] = 'Högre auktoritet';

$string['primaryemaildescription'] = 'Primära e-postadressen. Du kommer att få ett meddelande som innehåller en klickbar länk - följ denna länk för att bekräfta e-postadressen och logga in till systemet';

$string['protocol'] = 'Protokoll';

$string['requiredfields'] = 'Obligatoriska profilfält';

$string['requiredfieldsset'] = 'Obligatoriska profilfält uppsatt';

$string['saveinstitutiondetailsfirst'] = 'Vänligen spara läroanstaltsinformationen förrän du konfigurerar autentiseringsplugins.';

$string['shortname'] = 'Kortnamn för din webbtjänst';

$string['ssodirection'] = 'SSO-riktning';

$string['theyautocreateusers'] = 'De skapar automatiskt användare';

$string['theyssoin'] = 'De använder SSO för att komma in';

$string['toomanytries'] = 'Du har överskridit maximimantalet inloggningsförsök. Kontot är låst i fem minuter.';

$string['unabletosigninviasso'] = 'Kunde inte logga in via SSO';

$string['updateuserinfoonlogin'] = 'Uppdatera användarinformation vid inloggning';

$string['updateuserinfoonlogindescription'] = 'Hämta användarinformation från fjärrservern och uppdatera din lokala användarinformation var gång användaren loggar in.';

$string['validationprimaryemailsent'] = 'Ett valideringsmeddelande har skickats till din e-post. Klicka på länken i meddelandet för att bekräfta e-postadressen';

$string['weautocreateusers'] = 'Vi skapar automatiskt användare';

$string['weimportcontent'] = 'Vi importerar innehåll';

$string['weimportcontentdescription'] = '(endast några program)';

$string['wessoout'] = 'Vi använder SSO för att komma ut';

$string['wwwroot'] = 'Webbrot';

$string['xmlrpccouldnotlogyouin'] = 'Tyvärr kunde du inte loggas in :(';

$string['xmlrpccouldnotlogyouindetail1'] = 'Tyvärr kunde vi inte logga in dig på %s. Var god och försök senare på nytt. Om problemet kvarstår, var god och kontakta din administratör.';

$string['xmlrpcserverurl'] = 'XML-RPC server URL';

