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


$string['accessdenied'] = 'Du har inte rättigheter till denna resurs';

$string['accessdeniedexception'] = 'Du har inte rättigheter till denna sida.';

$string['apcstatoff'] = 'Din server verkar köra APC med apc.stat=0. Mahara stöder inte den här konfigurationen. Du måste ställa in apc.stat=1 i php.ini filen.

 Om du har en delad värd, är det sannolikt att det inte finns mycket du kan göra för att få apc.stat aktiverat, annat än fråga din tjänstevärd. Kanske du borde flytta till en annan värd.';

$string['artefactnotfound'] = 'Elementet med id %s fanns inte';

$string['artefactnotfoundmaybedeleted'] = 'Elementet med id %s fanns inte (kanske den har raderats redan?)';

$string['artefactnotinview'] = 'Element %s finns inte på sidan %s';

$string['artefactonlyviewableinview'] = 'Element av den här typen är endast synliga på en sida';

$string['artefactpluginmethodmissing'] = 'Elementets plugin %s måste genomföra %s och gör inte det';

$string['artefacttypeclassmissing'] = 'Elementtyperna måste alla genomföra en klass. Saknar %s.';

$string['artefacttypemismatch'] = 'Elementtyperna passar inte ihop. Du försöker använda %s som %s.';

$string['artefacttypenametaken'] = 'Elementtypen %s är redan upptagen av en annan plugin (%s).';

$string['blockconfigdatacalledfromset'] = 'Konfigurerings data borde inte appliceras direkt, använd PluginBlocktype::instance_config_save istället.';

$string['blockinstancednotfound'] = 'Blockinstans med id %s hittades inte.';

$string['blocktypelibmissing'] = 'Saknar lib.php för blocket %s i elementpluginen %s.';

$string['blocktypemissingconfigform'] = 'Blocktypen %s måste genomföra instance_config_form.';

$string['blocktypenametaken'] = 'Blocktypen %s är redan upptagen av en annan plugin (%s).';

$string['blocktypeprovidedbyartefactnotinstallable'] = 'Det här installeras som en del av installationen av elementpluginen %s.';

$string['classmissing'] = 'klass %s för typen %s i pluginen %s saknades.';

$string['couldnotmakedatadirectories'] = 'För någon orsak kunde core data-katalogerna inte skapas. Det här borde inte hända, eftersom Mahara tidigare upptäckte att dataroten kan skrivas på. Vänligen kontrollera tillstånden för datarotskatalogen.';

$string['curllibrarynotinstalled'] = 'Din serverkonfiguration innehåller inte curl-tillägget. Mahara kräver det här för integration av Moodle och för att hämta externa feeds. Vänligen kontrollera att curl är laddat i php.ini eller installera det ifall det inte är installerat.';

$string['datarootinsidedocroot'] = 'Du har lagt upp din datarot inom din dokumentrot. Det här är en stor säkerthetsrisk, eftersom vem som helst kan komma över sessionsdata (för att kidnappa andra användares sessioner) eller filer uppladdade av andra, vilka de inte har användarrättigheter till. Vänligen konfigurera datarot, så att den ligger utanför dokumentroten.';

$string['datarootnotwritable'] = 'Datarotskatalogen, %s, kan inte skrivas på. Det här betyder att ingen sessionsdata, inga användarfiler och inget annat, som behöver uppladdas, heller kan sparas på din server. Vänligen skapa katalogen ifall den inte existerar eller bevilja webbservern ägarrättigheter.';

$string['dbconnfailed'] = 'Mahara kunde inte koppla upp till programdatabasen.

* Om du använder Mahara, vänta en stund och försök igen
* Om du är administratören, vänligen kontrollera dina databasinställningar och försäkra dig om, att databasen är tillgänglig.

Felet, som uppstod, var:
';

$string['dbnotutf8'] = 'Du använder inte en UTF-8-databas. Mahara sparar internt all data som UTF-8. Vänligen återskapa din databas med UTF-8-kodning.';

$string['dbversioncheckfailed'] = 'Din databasserverversion är inte tillräckligt ny för att köra Mahara. Din server är %s %s, men Mahara kräver åtminstone version %s.';

$string['domextensionnotloaded'] = 'Din serverkonfiguration innehåller inte dom-tillägget. Mahara kräver det här för att plocka åt sig XML-data från en mängd av källor.';

$string['gdextensionnotloaded'] = 'Din serverkonfiguration innehåller inte gd-tillägget. Mahara kräver det här bl.a. för att redigera bildstorleken på uppladdade bilder. Vänligen kontrollera att det är laddat i php.ini eller installera det ifall det inte är installerat.';

$string['gdfreetypenotloaded'] = 'Din serverkonfiguration i gd-tillägget innehåller inte Freetype support. Vänligen kontrollera att gd är konfigurerat med den.';

$string['gdlibrarylacksgifsupport'] = 'Installerade PHP GD biblioteket stöder inte skapande och läsande av GIF bilder. För att ladda upp GIF bilder behövs fullt stöd.';

$string['gdlibrarylacksjpegsupport'] = 'Installerade PHP GD biblioteket stöder inte JPEG/JPG bilder. För att ladda upp JPEG/JPG bilder behövs fullt stöd.';

$string['gdlibrarylackspngsupport'] = 'Installerade PHP GD biblioteket stöder inte PNG bilder. För att ladda upp PNG bilder behövs fullt stöd.';

$string['interactioninstancenotfound'] = 'Aktivitetsinsatsen med id %s hittas inte.';

$string['invaliddirection'] = 'Ogiltig riktning %s.';

$string['invalidlayoutselection'] = 'Du har försökt välja en layout som inte existerar.';

$string['invalidnumrows'] = 'Du har försökt skapa en layout med ett radantal som överskrider det tillåtna antalet av rader. (Det här borde inte vara möjligt; kontakta din webb administratör.)';

$string['invalidviewaction'] = 'Sidkontrollsaktionen %s går inte att genomföra';

$string['jsonextensionnotloaded'] = 'Din serverkonfiguration innehåller inte JSON-tillägget. Mahara kräver det här för att skicka data till och från webbläsaren. Vänligen kontrollera att det är laddat i php.ini eller installera det ifall det inte är installerat.';

$string['magicquotesgpc'] = 'Du har farliga PHP-inställningar: magic_quotes_gpc är påknäppt. Mahara försöker arbeta sig runt problemet, men du borde verkligen fixa det. Om du använder en delad värd och din värd tillåter det, lägg in följande rad i din .htaccess fil:
php_flag magic_quotes_gpc off';

$string['magicquotesruntime'] = 'Du har farliga PHP-inställningar: magic_quotes_runtime är påknäppt. Mahara försöker arbeta sig runt problemet, men du borde verkligen fixa det. Om du använder en delad värd och din värd tillåter det, lägg in följande rad i din .htaccess fil:
php_flag magic_quotes_runtime off';

$string['magicquotessybase'] = 'Du har farliga PHP-inställningar: magic_quotes_sybase är påknäppt. Mahara försöker arbeta sig runt problemet, men du borde verkligen fixa det. Om du använder en delad värd och din värd tillåter det, lägg in följande rad i din .htaccess fil:
php_flag magic_quotes_sybase off';

$string['mbstringneeded'] = 'Om du har användare med UTF-8 tecken i sina användarnamn måste mbstring-tillägget för php installeras. Annars är det möjligt att användarna inte kan logga in.';

$string['missingparamblocktype'] = 'Försök först välja en blocktyp att lägga till.';

$string['missingparamcolumn'] = 'Saknar kolumnspecificering';

$string['missingparamid'] = 'Id saknas';

$string['missingparamorder'] = 'Ingen ordning har specificerats';

$string['missingparamrow'] = 'Rad specifikationer saknas';

$string['mysqldbextensionnotloaded'] = 'Din serverkonfiguration innehåller inte mysqli eller mysql-tillägget. Mahara kräver det här för att spara data i relationsdatabaser. Vänligen kontrollera att det är laddat i php.ini eller installera det ifall det inte är installerat.';

$string['mysqlnotriggerprivilege'] = 'Mahara kräver tillstånd att skapa databas triggers men det lyckas inte. Vänligen kontrollera att denna förmån gäller till lämpliga användare i din MySQL installation. Instruktioner hittar du här https://wiki.mahara.org/index.php/System_Administrator\'s_Guide/Granting_Trigger_Privilege';

$string['nopasswordsaltset'] = 'Inget allmänt lösenordsalt för sidan har angivits. Redigera din config.php och ställ in "passwordsaltmain" parametern till en förnuftig hemlig fras.';

$string['noreplyaddressmissingorinvalid'] = 'Noreply adressen har lämnats tom eller så är e-postadressen felaktig. Kolla konfigurationerna i <a href="%s">sid alternativen i e-post inställningarna</a>.';

$string['notartefactowner'] = 'Du äger inte detta element.';

$string['notenoughsessionentropy'] = 'Din PHP session.entropy_length inställning är för liten. Ställ in den till åtminstone 16 i din php.ini för att försäkra att session ID:n är tillräckligt slumpmässiga och oförutsägbara.';

$string['notfound'] = 'Fanns inte';

$string['notfoundexception'] = 'Sidan du sökte fanns inte.';

$string['notproductionsite'] = 'Sidan är inte i produktions-mode. Det kan hända att all data inte är tillgänglig och/eller en del data kan vara föråldrad.';

$string['onlyoneblocktypeperview'] = 'Kan inte lägga till mer än en %s blocktyp på en sida.';

$string['onlyoneprofileviewallowed'] = 'Du kan endast ha en profilsida.';

$string['openbasedirenabled'] = 'Din server har php open_basedir begränsningen aktiverad.';

$string['openbasedirpaths'] = 'Mahara kan endast öppna filer med följande stig(ar): %s.';

$string['openbasedirwarning'] = 'Några begäran för externa sidor kanske misslyckas. Detta kan bl.a. orsaka att vissa feeds inte uppdateras.';

$string['parameterexception'] = 'En obligatorisk parameter saknades.';

$string['passwordsaltweak'] = 'Sidans allmänna lösenordsalt är inte tillräckligt säker. Redigera config.php och ställ in "passwordsaltmain" parametern till en längre hemlig fras.';

$string['pgsqldbextensionnotloaded'] = 'Din serverkonfiguration innehåller inte pgsql-tillägget. Mahara kräver det här för att spara data i relationsdatabaser. Vänligen kontrollera att det är laddat i php.ini eller installera det ifall det inte är installerat.';

$string['phpversion'] = 'Mahara kan inte köras på PHP < %s. Var god och uppgradera din PHP version eller flytta Mahara till en annan värd.';

$string['pleaseloginforjournals'] = 'Du måste logga ut och logga in på nytt för att se alla dina bloggar och inlägg.';

$string['plpgsqlnotavailable'] = 'PL/pgSQL språket är inte aktiverat i din Postgres installation och Mahara kan inte aktivera det. Installera PL/pgSQL i din databas manuellt. Instruktioner hittar du här https://wiki.mahara.org/index.php/System_Administrator\'s_Guide/Enabling_Plpgsql';

$string['postmaxlessthanuploadmax'] = 'Din inställning för PHP post_max_size (%s) är mindre än din uppladdade inställning för upload_max_filesize (%s). Uppladdningar som är större än %s kommer att misslyckas utan att visa felmeddelande. Oftast bör post_max_size vara mycket större än upload_max_filesize.';

$string['previewimagegenerationfailed'] = 'Vi beklagar, det uppstod ett problem med förhandsvisning av bilden.';

$string['registerglobals'] = 'Du har farliga PHP-inställningar: register_globals är påknäppt. Mahara försöker arbeta sig runt problemet, men du borde verkligen fixa det. Om du använder en delad värd och din värd tillåter det, lägg in följande rad i din .htaccess fil:
php_flag register_globals off';

$string['safemodeon'] = 'Det verkar som om din server körs i felsäkert läge. Mahara kan inte köras i felsäkert läge. Du måste stänga av det antingen i din php.ini-fil eller i webbtjänstens apache config.
Om du delar ditt webbhotell, kan du sannolikt inte stänga av felsäkra läget utan att kontakta din webbhotells administratör. Du vill kanske överväga att flytta till ett annat webbhotell.';

$string['sessionextensionnotloaded'] = 'Din serverkonfiguration innehåller inte sessionstillägget. Mahara kräver det här för att tillåta användare att logga in. Vänligen försäkra dig om att det är laddat i php.ini eller installera det ifall det inte är installerat.';

$string['sessionpathnotwritable'] = 'Din sessionsdata katalog, %s, kan inte skrivas på. Skapa katalogen om en sådan inte redan finns eller ge ägarskap av katalogen till webbserver användaren om den finns.';

$string['smallpostmaxsize'] = 'Din inställning för PHP post_max_size (%s) är mycket liten. Uppladdningar som är större än %s kommer att misslyckas utan att visa felmeddelande.';

$string['switchtomysqli'] = '<strong>mysqli</strong> PHP tilläget är inte installerad på din server. P.g.a. använder Mahara det originala <strong>mysql</strong> PHP tillägget. Vi rekommenderar att installera <a href="http://php.net/manual/en/book.mysqli.php">mysqli</a>.';

$string['themenameinvalid'] = 'Namnet på temat \'%s\' innehåller förbjudna tecken.';

$string['timezoneidentifierunusable'] = 'PHP hos din webbtjänstsvärd returnerar inte ett användbart värde för identifiering av tidszon (%%z) - vissa datumformat, såsom Leap2A export, kommer att gå sönder. %%z är en PHP formateringskod för datum. Det här problemet uppstår ofta då PHP körs på Windows.';

$string['unabletosetmultipleblogs'] = 'Aktiverar flera bloggar för användaren %s då kopiering av sidan %s misslyckats. Det här kan aktiveras manuellt på <a href="%s">konto inställningar</a> sidan.';

$string['unknowndbtype'] = 'Din serverkonfiguration hänvisar till en okänd databastyp. Giltiga värden är "postgres" och "mysql". Vänligen ändra på databasinställningen i config.php.';

$string['unrecoverableerror'] = 'Ett oåterkalleligt problem har uppstått. Det här betyder troligtvis att du har stött på ett systemfel.';

$string['unrecoverableerrortitle'] = '%s - Webbtjänsten otillgänglig';

$string['versionphpmissing'] = 'Pluginen %s %s saknar version.php.';

$string['viewnotfound'] = 'Sidan med id %s hittades inte.';

$string['viewnotfoundbyname'] = 'Sidan %s av %s hittades inte.';

$string['viewnotfoundexceptionmessage'] = 'Du försökte öppna en sida som inte finns.';

$string['viewnotfoundexceptiontitle'] = 'Sidan hittades inte';

$string['wwwrootnothttps'] = 'Din definierade www-rot, %s, är inte HTTPS. Andra inställningar (som t.ex. sslproxy) för din installation kräver att din www-rot är en HTTPS adress.

Uppdatera din www-rot inställning till en HTTPS adress eller ändra på de inkorrekta inställningarna.';

$string['xmlextensionnotloaded'] = 'Din serverkonfiguration innehåller inte %s-tillägget. Mahara kräver det här för att plocka fram XML-data från flera olika källor. Vänligen kontrollera att det är laddat i php.ini eller installera tillägget om det inte är installerat.';

$string['youcannotviewthisusersprofile'] = 'Du kan inte se den här användarens profil.';

