<?php

/**
 * Upgrades for local customisations.
 */

defined('INTERNAL') || die();

function xmldb_local_upgrade($oldversion=0) {
    $success = false;

    if ($oldversion < 2015050700) {
        require_once('skin.php');

        $skins = Skin::get_site_skins();

        if (is_array($skins)) {
            foreach ($skins as $skin) {
                $skinobj = new Skin($skin->id);
                $viewskin = $skinobj->get('viewskin');

                if ($viewskin['view_button_normal_color'] == '#DDDDDD') {
                    $viewskin['view_button_normal_color'] = '#028fd1';

                    if ($viewskin['view_button_hover_color'] == '#CCCCCC') {
                        $viewskin['view_button_hover_color'] = '#00415f';
                    }

                    if ($viewskin['view_button_text_color'] == '#000000') {
                        $viewskin['view_button_text_color'] = '#ffffff';
                    }
                }

                $skinobj->set('viewskin', $viewskin);
                $skinobj->commit();
            }
        }

        $success = true;
    }

    return $success;
}
