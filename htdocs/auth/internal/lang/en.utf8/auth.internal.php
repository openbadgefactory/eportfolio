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


$string['approvalemailmessagehtml'] = '<p>Hi %s,</p>
<p>Thank you for requesting an account on %s. The institution adminstrator
has been notified of your application. You will receive another email as
soon as your application has been considered.</p>

<pre>--
Regards,
The %s Team</pre>';

$string['approvalemailmessagetext'] = 'Hi %s,

Thank you for requesting an account on %s. The institution administrator
has been notified of your application. You will receive another email as
soon as your application has been considered.

--
Regards,
The %s Team';

$string['approvalemailsubject'] = 'Request for registration at %s received';

$string['completeregistration'] = 'Complete registration';

$string['confirmcancelregistration'] = 'Are you sure you want to cancel this registration? Doing so will result in your request being removed from the system.';

$string['confirmemailsubject'] = 'Confirm email for registration at %s';

$string['description'] = 'Authenticate against Mahara\'s database';

$string['emailalreadytaken'] = 'This email address has already been registered here.';

$string['emailconfirmedok'] = '<p>You have successfully confirmed your email. You will be notified with further registration details soon.</p>';

$string['iagreetothetermsandconditions'] = 'I agree to the Terms and Conditions.';

$string['internal'] = 'Internal';

$string['passwordformdescription'] = 'Your password must be at least six characters long. Passwords are case sensitive and must be different from your username.<br/>
For good security, consider using a passphrase. A passphrase is a sentence rather than a single word. Consider using a favourite quote or listing two (or more!) of your favourite things separated by spaces.';

$string['passwordinvalidform'] = 'Your password must be at least six characters long. Passwords are case sensitive and must be different from your username.<br/>
For good security, consider using a passphrase. A passphrase is a sentence rather than a single word. Consider using a favourite quote or listing two (or more!) of your favourite things separated by spaces.';

$string['pendingregistrationadminemailhtml'] = '<p>Hi %s,</p>
<p>A new user has requested to join the institution \'%s\'.</p>
<p>Because you are listed as an administrator of this institution you need to approve or deny this registration request. To do this, select the following link: <a href=\'%s\'>%s</a></p>
<p>You will need to approve or deny this registration request within %s.</p>
<p>Details of the registration request follows:</p>
<p>Name: %s</p>
<p>Email: %s</p>
<p>Registration reason:</p>
<p>%s</p>
<pre>--
Regards,
The %s Team</pre>';

$string['pendingregistrationadminemailsubject'] = 'New user registration for institution \'%s\' at %s.';

$string['pendingregistrationadminemailtext'] = 'Hi %s,

A new user has requested to join the institution \'%s\'.

Because you are listed as an administrator of this institution you need to approve or deny this registration request. To do this, select the following link: %s

You will need to approve or deny this registration request within %s.

Details of the registration request follow:

Name: %s
Email: %s
Registration reason:
%s

--
Regards,
The %s Team';

$string['recaptcharegisterdesc'] = 'Please enter the words you see in the box, in order and separated by a space. Doing so helps prevent automated programs from abusing this service.';

$string['recaptcharegistertitle'] = 'reCAPTCHA challenge';

$string['registeredemailmessagehtml'] = '<p>Hi %s,</p>
<p>Thank you for registering an account on %s. Please follow this link
to complete the signup process:</p>
<p><a href="%sregister.php?key=%s">%sregister.php?key=%s</a></p>
<p>The link will expire in 24 hours.</p>

<pre>--
Regards,
The %s Team</pre>';

$string['registeredemailmessagetext'] = 'Hi %s,

Thank you for registering an account on %s. Please follow this link to
complete the signup process:

%sregister.php?key=%s

The link will expire in 24 hours.

--
Regards,
The %s Team';

$string['registeredemailsubject'] = 'You have registered at %s';

$string['registeredok'] = '<p>You have successfully registered. Please check your email account for instructions on how to activate your account</p>';

$string['registeredokawaitingemail2'] = 'You have successfully submitted your application for registration. The institution adminstrator has been notified, and you will receive an email as soon as your application has been processed.';

$string['registrationcancelledok'] = 'You have successfully cancelled your registration application.';

$string['registrationconfirm'] = 'Confirm registration?';

$string['registrationconfirmdescription'] = 'Registration must be approved by institution administrators.';

$string['registrationdeniedemailsubject'] = 'Registration attempt at %s denied.';

$string['registrationdeniedmessage'] = 'Hello %s,

We have received your application for joining our institution on %s and
decided not to grant you access.

If you think that this decision was incorrect, please get in touch with me
via email.

Regards
%s';

$string['registrationdeniedmessagereason'] = 'Hello %s,

We have received your application for joining our institution on %s and decided
not to grant you access for the following reason:

%s

If you think that this decision was incorrect, please get in touch with me
via email.

Regards
%s';

$string['registrationexpiredkey'] = 'Sorry, your key has expired. Perhaps you waited longer than 24 hours to complete your registration? Otherwise, it might be our fault.';

$string['registrationnosuchid'] = 'Sorry, this registration key does not exist. Perhaps it is already activated?';

$string['registrationnosuchkey1'] = 'Sorry, we don\'t have a key that matches your link. Perhaps your email program mangled it?';

$string['registrationreason'] = 'Registration reason';

$string['registrationreasondesc1'] = 'The reason for requesting registration with your chosen institution and any other details you think might be useful for the administrator in processing your application.';

$string['registrationunsuccessful'] = 'Sorry, your registration attempt was unsuccessful. This is our fault, not yours. Please try again later.';

$string['title'] = 'Internal';

$string['usernamealreadytaken'] = 'Sorry, this username is already taken.';

$string['usernameinvalidadminform'] = 'Usernames may contain letters, numbers and most common symbols and must be from 3 to 236 characters long. Spaces are not allowed.';

$string['usernameinvalidform'] = 'Usernames may contain letters, numbers and most common symbols and must be from 3 to 30 characters long. Spaces are not allowed.';

$string['youmaynotregisterwithouttandc'] = 'You may not register unless you agree to abide by the <a href="#user_acceptterms">Terms and Conditions</a>.';

