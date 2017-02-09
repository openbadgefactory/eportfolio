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


$string['addauthority'] = 'Add an authority';

$string['application'] = 'Application';

$string['authloginmsg2'] = 'When you have not chosen a parent authority, enter a message to display when a user tries to log in via the login form';

$string['authname'] = 'Authority name';

$string['cannotjumpasmasqueradeduser'] = 'You cannot jump to another application whilst masquerading as another user.';

$string['cannotremove'] = 'We cannot remove this authentication plugin, as it is the only 
plugin that exists for this institution.';

$string['cannotremoveinuse'] = 'We cannot remove this authentication plugin, as it is being used by some users.
You must update their records before you can remove this plugin.';

$string['cantretrievekey'] = 'An error occurred while retrieving the public key from the remote server.<br>Please ensure that the Application and WWW root fields are correct and that networking is enabled on the remote host.';

$string['changepasswordurl'] = 'Password-change URL';

$string['duplicateremoteusername'] = 'This external authentication username is already in use by the user %s. External authentication usernames must be unique within an authentication method.';

$string['duplicateremoteusernameformerror'] = 'External authentication usernames must be unique within an authentication method.';

$string['editauthority'] = 'Edit an authority';

$string['errnoauthinstances'] = 'We do not seem to have any authentication plugin instances configured for the host at %s.';

$string['errnoxmlrpcinstances'] = 'We do not seem to have any XML-RPC authentication plugin instances configured for the host at %s.';

$string['errnoxmlrpcuser1'] = 'We were unable to authenticate you at this time. Possible reasons might be:

    * Your SSO session might have expired. Go back to the other application and click the link to sign into %s again.
    * You may not be allowed to SSO to %s. Please check with your administrator if you think you should be allowed to.';

$string['errnoxmlrpcwwwroot'] = 'We do not have a record for any host at %s.';

$string['errorcertificateinvalidwwwroot'] = 'This certificate claims to be for %s, but you are trying to use it for %s.';

$string['errorcouldnotgeneratenewsslkey'] = 'Could not generate a new SSL key. Are you sure that both openssl and the PHP module for openssl are installed on this machine?';

$string['errornotvalidsslcertificate'] = 'This is not a valid SSL certificate.';

$string['host'] = 'Hostname or address';

$string['hostwwwrootinuse'] = 'WWW root already in use by another institution (%s).';

$string['ipaddress'] = 'IP address';

$string['mobileuploadnotenabled'] = 'Sorry mobile uploads are not enabled.';

$string['mobileuploadtokennotfound'] = 'Sorry that mobile upload token was not found. Please check your site and mobile application settings.';

$string['mobileuploadtokennotset'] = 'Your mobile upload token cannot be blank. Please check your mobile application settings and try again.';

$string['mobileuploadusernamenotset'] = 'Your mobile upload username cannot be blank. Please check your mobile application settings and try again.';

$string['name'] = 'Site name';

$string['noauthpluginconfigoptions'] = 'There are no configuration options associated with this plugin.';

$string['nodataforinstance'] = 'Could not find data for authentication instance ';

$string['parent'] = 'Parent authority';

$string['primaryemaildescription'] = 'The primary email address. You will receive an email containing a clickable link – follow this to validate the address and log in to the system';

$string['protocol'] = 'Protocol';

$string['requiredfields'] = 'Required profile fields';

$string['requiredfieldsset'] = 'Required profile fields set';

$string['saveinstitutiondetailsfirst'] = 'Please save the institution details before configuring authentication plugins.';

$string['shortname'] = 'Short name for your site';

$string['ssodirection'] = 'SSO direction';

$string['theyautocreateusers'] = 'They auto-create users';

$string['theyssoin'] = 'They SSO in';

$string['toomanytries'] = 'You have exceeded the maximum login attempts. This account has been locked for up to 5 minutes.';

$string['unabletosigninviasso'] = 'Unable to sign in via SSO.';

$string['updateuserinfoonlogin'] = 'Update user info on login';

$string['updateuserinfoonlogindescription'] = 'Retrieve user info from the remote server and update your local user record each time the user logs in.';

$string['validationprimaryemailsent'] = 'A validation email has been sent. Please click the link inside this to validate the address';

$string['weautocreateusers'] = 'We auto-create users';

$string['weimportcontent'] = 'We import content';

$string['weimportcontentdescription'] = '(some applications only)';

$string['wessoout'] = 'We SSO out';

$string['wwwroot'] = 'WWW root';

$string['xmlrpccouldnotlogyouin'] = 'Sorry, we could not log you in.';

$string['xmlrpccouldnotlogyouindetail1'] = 'Sorry, we could not log you into %s at this time. Please try again shortly. If the problem persists, contact your administrator.';

$string['xmlrpcserverurl'] = 'XML-RPC server URL';

