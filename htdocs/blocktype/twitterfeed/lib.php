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
 * @subpackage blocktype-twitterfeed
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2011-2013 Discendum Ltd, http://discendum.com
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeTwitterFeed extends SystemBlocktype {

    public static function single_only() {
        return false;
    }

    public static function get_title() {
        return get_string('title', 'blocktype.twitterfeed');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.twitterfeed');
    }

    public static function get_categories() {
        return array('external');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');

        if (empty($configdata) || !isset($configdata['embed'])) {
            return;
        }

        $dom = DOMDocument::loadHTML($configdata['embed']);

        foreach ($dom->getElementsByTagName('a') as $node) {

            $href = $node->getAttribute('href');

            $href  = hsc(preg_replace('/.*\//', '', $href));

            $class = hsc($node->getAttribute('class'));
            $id    = hsc($node->getAttribute('data-widget-id'));
            $txt   = hsc($node->textContent);

            $out  = '<script id="twitter-wjs" src="https://platform.twitter.com/widgets.js"></script>';
            $out .= "<a class='$class' href='https://twitter.com/$href' data-widget-id='$id'>$txt</a>";

            return $out;
        }

        return;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        return array(
            'embed' => array(
                'type' => 'textarea',
                'title' =>  get_string('embedcode', 'blocktype.twitterfeed'),
                'rows' => 5,
                'cols' => 60,
                'help' => true,
                'defaultvalue' => @$configdata['embed'],
                'rules' => array( 'required' => true )
            )
        );
    }

    public static function default_copy_type() {
        return 'full';
    }

}
