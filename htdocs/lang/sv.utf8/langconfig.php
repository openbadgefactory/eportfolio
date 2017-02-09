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


$string['locales'] = 'sv_SE.utf8';

$string['pluralfunction'] = 'plural_sv_utf8';

$string['pluralrule'] = 'n != 1';

$string['strfdateofbirth'] = '%%Y/%%m/%%d';

$string['strfdaymonthyearshort'] = '%%d.%%m.%%Y';

$string['strftimedate'] = '%%d.%%m.%%Y';

$string['strftimedateshort'] = '%%d.%%m.%%Y';

$string['strftimedatetime'] = '%%d.%%m.%%Y %%H:%%M';

$string['strftimedatetimeshort'] = '%%d.%%m.%%Y %%H:%%M';

$string['strftimedaydate'] = '%%d.%%m.%%Y';

$string['strftimedaydatetime'] = '%%d.%%m.%%Y %%H:%%M';

$string['strftimedayshort'] = '%%d.%%m.%%Y';

$string['strftimedaytime'] = '%%d.%%m.%%Y, %%k:%%M';

$string['strftimemonthyear'] = '%%d.%%m.%%Y';

$string['strftimenotspecified'] = 'Inte specificerad';

$string['strftimerecent'] = '%%d.%%m.%%Y, %%H:%%M';

$string['strftimerecentfull'] = '%%d.%%m.%%Y, %%H:%%M';

$string['strftimerecentyear'] = '%%d %%b %%Y, %%k:%%M';

$string['strftimetime'] = '%%H:%%M';

$string['strftimew3cdate'] = '%%Y-%%m-%%d';

$string['strftimew3cdatetime'] = '%%Y-%%m-%%dT%%H:%%M:%%S%%z';

$string['thislanguage'] = 'Svenska';


// Plural forms, added by language pack generator
$string['pluralrule'] = '(n != 1)';
$string['pluralfunction'] = 'plural_sv_utf8';
function plural_sv_utf8($n) {
    return ($n != 1);
}
