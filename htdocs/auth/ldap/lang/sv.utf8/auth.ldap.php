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


$string['attributename'] = 'Namnet på LDAP attributen som används för att synkronisera grupper baserat på dess värden (obligatorisk)';

$string['cannotconnect'] = 'Kan ej kopplas till LDAP hosts';

$string['cannotdeleteandsuspend'] = 'Kan inte specificera -d och -s samtidigt.';

$string['cli_info_sync_groups'] = 'Denna kommandosekvensens PHP skript kommer att försöka synkronisera en läroanstalts lista av grupper med en LDAP katalog.
Grupper som saknas kommer att skapas och namnges som \'läroanstalts namn : LDAP grupp namn\'.';

$string['cli_info_sync_groups_attribute'] = 'Denna kommandosekvensens PHP skript kommer att försöka synkronisera en läroanstalts lista av grupper med en LDAP katalog
baserad på de olika värdena av en LDAP attribut.
Grupper som saknas kommer att skapas med namnet \'läroanstalts namn : LDAP attribut värde\'';

$string['cli_info_sync_users'] = 'Denna kommandosekvensens PHP skript kommer att försöka synkronisera en läroanstalts lista av Mahara användarkonton med en LDAP katalog.';

$string['contexts'] = 'Kontext';

$string['description'] = 'Autentisera mot en LDAP-server';

$string['distinguishedname'] = 'Namn, som känns igen';

$string['dodelete'] = 'Radera konton som inte längre finns i LDAP';

$string['dosuspend'] = 'Suspendera användarkonton som inte längre finns i LDAP';

$string['doupdate'] = 'Uppdatera existerande konton med LDAP data (long)';

$string['dryrun'] = 'Dummy execution. Utför inte databas funktioner.';

$string['excludelist'] = 'Uteslut LDAP grupper som innehåller dessa uttryck i sina namn';

$string['extrafilterattribute'] = 'Ytterligare LDAP filtrar för att begränsa användare sökning';

$string['grouptype'] = 'Vilken typ av Mahara grupp som skapas; standarden är "standard"';

$string['hosturl'] = 'Värd-URL';

$string['includelist'] = 'Behandla endast LDAP grupper som innehåller dessa uttryck i sina namn';

$string['institutionname'] = 'Namnet för läroanstalten som skall behandlas (obligatorisk)';

$string['ldapfieldforemail'] = 'LDAP-fält för e-post';

$string['ldapfieldforfirstname'] = 'LDAP-fält för förnamn';

$string['ldapfieldforpreferredname'] = 'LDAP-fält för alias';

$string['ldapfieldforstudentid'] = 'LDAP-fält för studietid';

$string['ldapfieldforsurname'] = 'LDAP-fält för efternamn';

$string['ldapversion'] = 'LDAP-version';

$string['nocreate'] = 'Skapa inte nya användarkonton';

$string['nocreatemissinggroups'] = 'Skapa inte nya LDAP grupper ifall de inte redan är etablerade i läroanstalten.';

$string['nomatchingauths'] = 'Ingen LDAP verifieringens plugin hittades för denna läroanstalt';

$string['notusable'] = 'Vänligen installera PHP-LDAP-tillägget';

$string['password'] = 'Lösenord';

$string['searchcontexts'] = 'Begränsa sökande i dessa kontexter (överkörande värden definieras i verifieringens plugin)';

$string['searchsubcontexts'] = 'Sök i innehållet';

$string['searchsubcontextscliparam'] = 'Sök (1) eller inte (0) i underliggande meningar (Överkörande värden ställda i verifieringens plugin)';

$string['starttls'] = 'TLS kryptering';

$string['syncgroupsautocreate'] = 'Skapa automatiskt grupper som fattas';

$string['syncgroupsbyclass'] = 'Synkronisera grupper sparade som LDAP objekt';

$string['syncgroupsbyuserfield'] = 'Synkronisera grupper sparade som användarattribut';

$string['syncgroupscontexts'] = 'Synkronisera endast grupper i dessa kontexter';

$string['syncgroupscontextsdesc'] = 'Lämna tom för standard avändarautentiserings kontext';

$string['syncgroupscron'] = 'Synkronisera grupper automatiskt via cron job';

$string['syncgroupsexcludelist'] = 'Exkludera LDAP grupper med dessa namn';

$string['syncgroupsgroupattribute'] = 'Grupp attributer';

$string['syncgroupsgroupclass'] = 'Grupp klass';

$string['syncgroupsgrouptype'] = 'Rolltyper i grupper som skapats automatiskt';

$string['syncgroupsincludelist'] = 'Inkludera enbart LDAP grupper med dessa namn';

$string['syncgroupsmemberattribute'] = 'Gruppmedlems attribut';

$string['syncgroupsmemberattributeisdn'] = 'Medlemsattribut är en dn?';

$string['syncgroupsnestedgroups'] = 'Behandla kapslade grupper';

$string['syncgroupssettings'] = 'Grupp synkronisering';

$string['syncgroupsuserattribute'] = 'Användarattribut gruppnamn är sparat i';

$string['syncgroupsusergroupnames'] = 'Endast dessa gruppnamn';

$string['syncgroupsusergroupnamesdesc'] = 'Lämna tom för att acceptera alla värden. Skilj gruppnamn med kommatecken.';

$string['syncuserscreate'] = 'Skapa automatiskt användare i cron';

$string['syncuserscron'] = 'Synkronisera användare automatiskt via cron job';

$string['syncusersextrafilterattribute'] = 'Ytterligare LDAP filter för synkronisering';

$string['syncusersgonefromldap'] = 'Om en användare inte längre finns i LDAP';

$string['syncusersgonefromldapdelete'] = 'Radera användarens konto och all innehåll';

$string['syncusersgonefromldapdonothing'] = 'Gör inget';

$string['syncusersgonefromldapsuspend'] = 'Suspendera användarens användarkonto';

$string['syncuserssettings'] = 'Användare synkronisering';

$string['syncusersupdate'] = 'Uppdatera användare info i cron';

$string['title'] = 'LDAP';

$string['updateuserinfoonlogin'] = 'Uppdatera användarinformation vid inloggning';

$string['updateuserinfoonloginadnote'] = 'Obs: Genom att aktivera det här kan hindra en del av MS ActiveDirectory tjänster/användare att logga in senare';

$string['userattribute'] = 'Användarattribut';

$string['usertype'] = 'Användartyp';

$string['weautocreateusers'] = 'Vi skapar automatiskt användare';

