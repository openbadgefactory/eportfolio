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


$string['approvalemailmessagehtml'] = '<p>Hej %s,</p>
<p>Tack för din begäran om ett konto på %s. Läroanstaltsadministratören
har meddelats om din ansökan. Du kommer att få ett e-postmeddelande då
din ansökan har avgjorts.</p>

<pre>--
Med vänliga hälsningar,
%s</pre>';

$string['approvalemailmessagetext'] = 'Hej %s,

Tack för din begäran om ett konto på %s. Institutitonsadministratören
har meddelats om din ansökan. Du kommer att få ett e-postmeddelande då
din ansökan har avgjorts.

--
Med vänliga hälsningar,
%s';

$string['approvalemailsubject'] = 'Begäran om registrering vid %s mottagen';

$string['completeregistration'] = 'Komplettera registreringen';

$string['confirmcancelregistration'] = 'Är du säker på att du vill avbryta registreringen? Det kommer att ta bort din begäran i systemet.';

$string['confirmemailsubject'] = 'Bekräfta e-post för registrering vid %s';

$string['description'] = 'Autentisera mot Maharas databas';

$string['emailalreadytaken'] = 'Den här e-postadressen är redan registrerad här.';

$string['emailconfirmedok'] = '<p>Du har bekräftat din e-postadress. Du kommer att få vidare registreringsinformation snarast.</p>';

$string['iagreetothetermsandconditions'] = 'Jag godkänner användarvillkoren.';

$string['internal'] = 'Intern';

$string['passwordformdescription'] = 'Ditt lösenord måste vara åtminstone sex tecken långt. Lösenord är skiftlägeskänsliga och de måste skilja sig från användarnamnet.<br/>';

$string['passwordinvalidform'] = 'Ditt lösenord måste vara åtminstone sex tecken långt. Lösenord är skiftlägeskänsliga och de måste skilja sig från användarnamnet.';

$string['pendingregistrationadminemailhtml'] = '<p>Hej %s,</p>
<p>En ny användare har bett om att få gå med i läroanstalten \'%s\'.</p>
<p>Eftersom du är listad som administratör av institutionen bör du godkänna eller förkasta registreringsansökan. För att göra detta, välj följande länk: <a href=\'%s\'>%s</a></p>
<p>Du bör godkänna eller förkasta ansökan inom två veckor.</p>
<p>Information om registreringsansökan:</p>
<p>Namn: %s</p>
<p>E-postadress: %s</p>
<p>Orsak till registrering:</p>
<p>%s</p>
<pre>--
Med vänliga hälsningar,
%s</pre>';

$string['pendingregistrationadminemailsubject'] = 'Ny användarregistrering för läroanstalten \'%s\' vid %s.';

$string['pendingregistrationadminemailtext'] = 'Hej %s,

En ny användare har bett om att få gå med i läroanstalten \'%s\'.

Eftersom du är listad som administratör av läroanstalten bör du godkänna eller förkasta registreringsansökan. För att göra detta, välj följande länk: %s

Du bör godkänna eller förkasta ansökan inom två veckor.

Information om registreringsansökan:

Namn: %s
E-postadress: %s
Orsak till registrering:
%s

--
Med vänliga hälsningar,
%s';

$string['recaptcharegisterdesc'] = 'Var vänlig och fyll i orden som visas i lådan i rätt ordning med ett mellanslag mellan orden. Detta hjälper skydda tjänsten från skadeprogram.';

$string['recaptcharegistertitle'] = 'reCAPTCHA utmaning';

$string['registeredemailmessagehtml'] = '<p>Hej %s,</p> <p>Tack för att du registrerade ett konto på %s. Vänligen följ denna länk för att komplettera registreringen:</p> <p><a href="%sregister.php?key=%s">%sregister.php?key=%s</a></p> <p>Länken upphör efter 24 timmar.</p> <pre>-- Hälsningar, %s-teamet</pre>';

$string['registeredemailmessagetext'] = 'Hej %s, Tack för att du registrerade ett konto på %s. Vänligen följ denna länk för att komplettera registreringen: %sregister.php?key=%s Länken upphör efter 24 timmar. Hälsningar, %s-teamet</pre>';

$string['registeredemailsubject'] = 'Du har registrerat dig på %s';

$string['registeredok'] = '<p>Din registrering lyckades. Vänligen kontrollera din e-post för instruktioner över hur du aktiverar ditt konto</p>';

$string['registeredokawaitingemail2'] = 'Din registreringsansökan har skickats in. Läroanstaltsadministratören har meddelats och du kommer att få ett e-postmeddelanden när din ansökan har behandlats.';

$string['registrationcancelledok'] = 'Du har avbrutit din registreringsbegäran.';

$string['registrationconfirm'] = 'Bekräfta registrering?';

$string['registrationconfirmdescription'] = 'Registreringen måste godkännas av läroanstalens administratörer.';

$string['registrationdeniedemailsubject'] = 'Registreringsförsök vid %s godkändes inte.';

$string['registrationdeniedmessage'] = 'Hej %s,

Vi har mottagit din ansökan om att gå med i vår läroanstalten på %s och
beslöt att inte bevilja dig tillgång till läroanstalten.

Om du anser att beslutet var felaktigt, var vänlig och kontakta mig 
 via e-post.

Med vänliga hälsningar,
%s';

$string['registrationdeniedmessagereason'] = 'Hej %s,

Vi har mottagit din ansökan om att gå med i vår läroanstalten på %s och beslöt
att inte bevilja dig tillgång på grund av följande orsak:

%s

Om du anser att beslutet var felaktigt, var vänlig och kontakt mig
via e-postl.

Med vänliga hälsningar,
%s';

$string['registrationexpiredkey'] = 'Vi beklagar, din nyckel har förfallit. Kanske tog det längre än 24 timmar att slutföra din registrering?';

$string['registrationnosuchid'] = 'Vi beklagar, denna registreringsnyckel existerar inte. Kanske har den redan aktiverats?';

$string['registrationnosuchkey1'] = 'Vi beklagar, vi har ingen nyckel som passar din länk. Kanske har ditt e-post program manglat länken?';

$string['registrationreason'] = 'Orsak till registrering';

$string['registrationreasondesc1'] = 'Orsak till registrering vid utvald läroanstalt och annan information du tror kan vara nyttigt för administratören att veta.';

$string['registrationunsuccessful'] = 'Tyvärr misslyckades din registrering. Det här är vårt fel, inte ditt. Vänligen försök igen senare.';

$string['title'] = 'Intern';

$string['usernamealreadytaken'] = 'Tyvärr är användarnamnet redan upptaget.';

$string['usernameinvalidadminform'] = 'Användarnamn kan innehålla bokstäver, siffror och de vanligaste specialtecknen och måste vara 3 till 236 tecken långa. Mellanslag är inte tillåtna.';

$string['usernameinvalidform'] = 'Användarnamn kan innehålla bokstäver, siffror och de vanligaste symbolerna och måste vara 3 till 30 tecken långt. Mellanslag är inte tillåtna.';

$string['youmaynotregisterwithouttandc'] = 'Du kan inte registrera dig ifall du inte godkänner <a href="terms.php">Användarvillkoren</a>.';

