<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage artefact-epsp
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('JSON', 1);
define('INTERNAL', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'epsp');

$fieldid = param_integer('fieldid');
$comment = param_variable('comment');
$viewid = param_variable('viewid');
$field = new ArtefactTypeEpspField($fieldid);

try {
    $commentobj = $field->add_comment($comment, $viewid);
    $authorobj = new User();
    $authorobj->find_by_id($commentobj->get('author'));

    $smarty = smarty_core();
    $smarty->assign('comment', $commentobj->to_stdclass());
    $smarty->assign('authorname', display_name($authorobj->to_stdclass()));
    $smarty->assign('deletable', true);

    $html = $smarty->fetch('artefact:epsp:comment.tpl');

    json_reply(false, array('comment' => $html));

} catch (Exception $ex) {
    json_reply(true, $ex->getMessage());
}