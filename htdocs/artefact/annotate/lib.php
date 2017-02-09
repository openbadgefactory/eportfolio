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
 * @subpackage artefact-annotate
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

require_once('activity.php');


class PluginArtefactAnnotate extends PluginArtefact {

    public static function get_artefact_types() {
        return array('annotate');
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'annotate';
    }

    public static function before_page_render() {
        global $HEADDATA, $THEME;

        // TODO: This is just a quick check, implement me properly.
        $isartefactpage = defined('SECTION_PAGE') && SECTION_PAGE == 'artefact';
        $dataname = 'artefact.' . self::get_plugin_name();

        if (!isset($HEADDATA[$dataname]) && $isartefactpage) {
            $scriptbase = get_config('wwwroot') . 'artefact/annotate/js/';
            $plugincss = $THEME->get_url('style/style.css', true, 'artefact/annotate');
            $cssfile = array_pop($plugincss);
            $strarr = array(
                'view' => array('back'),
                'mahara' => array('submit', 'cancel'),
                'artefact.annotate' => array('annotate', 'confirmdelete', 'annotatedby', 'annotationlinktext'));

            $strings = array();

            foreach ($strarr as $section => $keys) {
                foreach ($keys as $key) {
                    $strings[$key] = get_string($key, $section);
                }
            }

            $stringsjson = json_encode($strings);
            $isloggedin = (int) is_logged_in();
            $html = <<<HTML
<link rel="stylesheet" type="text/css" href="$cssfile" />
<script type="text/javascript" src="{$scriptbase}rangy.js"></script>
<script type="text/javascript" src="{$scriptbase}json2.js"></script>
<script type="text/javascript" src="{$scriptbase}annotate.js"></script>
<script type="text/javascript">
window.strings = window.strings || {};
jQuery.extend(window.strings, $stringsjson);
jQuery(document).ready(function () {
    initAnnotate($isloggedin);
});
</script>
HTML;

            $HEADDATA[$dataname] = $html;
        }
    }

    public static function menu_items() {
        return array();
    }

    public static function can_be_disabled() {
        return true;
    }
}

class ArtefactTypeAnnotate extends ArtefactType {

    public static function is_singular() {
        return false;
    }

    public static function get_icon($options=null) { }

    public static function get_links($id) { }

}
