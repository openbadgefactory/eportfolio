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


$string['attributename'] = 'LDAP attribuutin nimeä käytetty ryhmien synkronointiin perustuen sen arvoihin (vaadittu ja tapaus tulee huomioida)';

$string['cannotconnect'] = 'Ei voi yhdistää mihinkään LDAP isäntään';

$string['cannotdeleteandsuspend'] = 'Ei voi määritellä -d ja -s samaan aikaan.';

$string['cli_info_sync_groups'] = 'This command line PHP script will attempt to synchronize an institution list of groups with an LDAP directory.
Missing groups will be created and named as \'institution name : LDAP group name\'.';

$string['cli_info_sync_groups_attribute'] = 'This command line PHP script will attempt to synchronize an institution list of groups with an LDAP directory
based on the different values of an LDAP attribute.
Missing groups will be created and named as \'institution name : LDAP attribute value\'';

$string['cli_info_sync_users'] = 'This command line PHP script will attempt to synchronize an institution list of Mahara accounts with an LDAP directory.';

$string['contexts'] = 'Konteksti';

$string['description'] = 'LDAP autentiointi';

$string['distinguishedname'] = 'Edustusnimi';

$string['dodelete'] = 'Delete accounts not anymore in LDAP';

$string['dosuspend'] = 'Suspend accounts not anymore in LDAP';

$string['doupdate'] = 'Update existing accounts with LDAP data (long)';

$string['dryrun'] = 'Tyhmä suoritus. Älä suorita mitään tietokanta operaatioita. ';

$string['excludelist'] = 'Exclude LDAP groups matching these regular expressions in their names';

$string['extrafilterattribute'] = 'Additional LDAP filter to restrict user searching';

$string['grouptype'] = 'Mahara ryhmän tyyppi luomiseen; oletus on "standardi"';

$string['hosturl'] = 'Hostin URLi';

$string['includelist'] = 'Process only LDAP groups matching these regular expressions in their names';

$string['institutionname'] = 'Oppilaitoksen nimi käsiteltäväksi (vaadittu)';

$string['ldapfieldforemail'] = 'LDAP-kenttä sähköpostille';

$string['ldapfieldforfirstname'] = 'LDAP-kenttä etunimelle';

$string['ldapfieldforpreferredname'] = 'LDAP field for display name';

$string['ldapfieldforstudentid'] = 'LDAP field for student ID';

$string['ldapfieldforsurname'] = 'LDAP-kenttä sukunimelle';

$string['ldapversion'] = 'LDAP versio';

$string['nocreate'] = 'Älä luo uusia käyttäjätilejä';

$string['nocreatemissinggroups'] = 'Do not create LDAP groups if they are not already set up in the institution.';

$string['nomatchingauths'] = 'No LDAP authentication plugin found for this institution';

$string['notusable'] = 'Ole hyvä ja asenna PHP LDAP laajennus';

$string['password'] = 'Salasana';

$string['searchcontexts'] = 'Restrict searching in these contexts (override values set in authentication plugin)';

$string['searchsubcontexts'] = 'Etsi alakonteksteja';

$string['searchsubcontextscliparam'] = 'Search (1) or not (0) in sub contexts (override values set in authentication plugin)';

$string['starttls'] = 'TLS salaus';

$string['syncgroupsautocreate'] = 'Auto-create missing groups';

$string['syncgroupsbyclass'] = 'Sync groups stored as LDAP objects';

$string['syncgroupsbyuserfield'] = 'Sync groups stored as user attributes';

$string['syncgroupscontexts'] = 'Sync groups in these contexts only';

$string['syncgroupscontextsdesc'] = 'Leave blank to default to user authentication contexts';

$string['syncgroupscron'] = 'Sync groups automatically via cron job';

$string['syncgroupsexcludelist'] = 'Exclude LDAP groups with these names';

$string['syncgroupsgroupattribute'] = 'Ryhmäattribuutti';

$string['syncgroupsgroupclass'] = 'Ryhmäluokka';

$string['syncgroupsgrouptype'] = 'Roolityypit automaattisesti luoduissa ryhmissä';

$string['syncgroupsincludelist'] = 'Include only LDAP groups with these names';

$string['syncgroupsmemberattribute'] = 'Group member attribute';

$string['syncgroupsmemberattributeisdn'] = 'Jäsenattribuutti on dn?';

$string['syncgroupsnestedgroups'] = 'Process nested group';

$string['syncgroupssettings'] = 'Group sync';

$string['syncgroupsuserattribute'] = 'User attribute group name is stored in';

$string['syncgroupsusergroupnames'] = 'Only these group names';

$string['syncgroupsusergroupnamesdesc'] = 'Leave empty to accept any value. Separate group names by comma.';

$string['syncuserscreate'] = 'Auto-create users in cron';

$string['syncuserscron'] = 'Sync users automatically via cron job';

$string['syncusersextrafilterattribute'] = 'Additional LDAP filter for sync';

$string['syncusersgonefromldap'] = 'Jos käyttäjä ei ole enää läsnä LDAP:ssa';

$string['syncusersgonefromldapdelete'] = 'Poista käyttäjän tili ja kaikki sisältö';

$string['syncusersgonefromldapdonothing'] = 'Älä tee mitään';

$string['syncusersgonefromldapsuspend'] = 'Jäädytetyn käyttäjän tili';

$string['syncuserssettings'] = 'Käyttäjän synkronointi';

$string['syncusersupdate'] = 'Päivitä käyttäjän tiedot croniin';

$string['title'] = 'LDAP';

$string['updateuserinfoonlogin'] = 'Päivitä käyttäjätieto kirjautumisessa';

$string['updateuserinfoonloginadnote'] = 'Huomio: tämän käyttöönotto saattaa estää joitain MS ActiveDirectoryn käyttäjiä kirjautumasta Maharaan.';

$string['userattribute'] = 'Käyttäjäominaisuus';

$string['usertype'] = 'Käyttäjätyyppi';

$string['weautocreateusers'] = 'Luomme automaattisesti käyttäjiä';

