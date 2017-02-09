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
 * @subpackage TODO
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
if (isset($_GET['RUN']) && $_GET['RUN'] == 1) {
    define('INTERNAL', 1);
    define('ADMIN', 1);

    require_once('../init.php');

    create_walls();

    $SESSION->add_ok_msg('Walls created successfully. Log out and then in if '
            . 'you are still seeing the old wall or getting an error about '
            . 'missing view.');
    redirect('/index.php');
}

function create_walls() {
    require_once(get_config('libroot') . 'view.php');
    require_once(get_config('docroot') . 'blocktype/lib.php');

    db_begin();

    // Our elegant version control.
    $record = get_record('view', 'owner', 0, 'type', 'dashboard');
    $version = !empty($record) ? intval($record->description) : 0;

    // The first version
    if ($version === 0) {
        // Delete the old dashboard
        if ($record) {
            $olddashboard = new View($record->id);
            $olddashboard->delete();
        }

        // ... And create our own wall.
        $version = 2014081400;
        $title = get_string('dashboardviewtitle', 'view');
        $wall = View::create(array(
                    'type' => 'dashboard',
                    'owner' => 0,
                    'numcolumns' => 2,
                    'numrows' => 1,
                    'description' => $version,
                    'columnsperrow' => array((object) array('row' => 1, 'columns' => 2)),
                    'ownerformat' => FORMAT_NAME_PREFERREDNAME,
                    'title' => $title,
                    'template' => 1
        ));

        $wall->set_access(array(array('type' => 'loggedin')));

        $blocktypes = array(
            array(
                'blocktype' => 'assessment',
                'title' => get_string('title', 'blocktype.assessment'),
                'row' => 1,
                'column' => 1,
                'config' => array('count' => 100)
            ),
            array(
                'blocktype' => 'assignment',
                'title' => get_string('title', 'blocktype.assignment'),
                'row' => 1,
                'column' => 1,
                'config' => array('count' => 100)
            ),
            array(
                'blocktype' => 'returnedassignment',
                'title' => get_string('title', 'blocktype.returnedassignment'),
                'row' => 1,
                'column' => 1,
                'config' => array('count' => 100)
            ),
            array(
                'blocktype' => 'othersviews',
                'title' => get_string('title', 'blocktype.othersviews'),
                'row' => 1,
                'column' => 1,
                'config' => array('limit' => 100)
            ),
            array(
                'blocktype' => 'announcement',
                'title' => get_string('title', 'blocktype.announcement'),
                'row' => 1,
                'column' => 2,
                'config' => array('maxitems' => 100)
            ),
            array(
                'blocktype' => 'recentforumposts',
                'title' => get_string('title', 'blocktype.recentforumposts'),
                'row' => 1,
                'column' => 2,
                'config' => array('limit' => 100)
            ),
            array(
                'blocktype' => 'mygroups',
                'title' => get_string('title', 'blocktype.mygroups'),
                'row' => 1,
                'column' => 2,
                'config' => array('count' => 100)
            ),
            array(
                'blocktype' => 'studymaterial',
                'title' => get_string('title', 'blocktype.studymaterial'),
                'row' => 1,
                'column' => 2,
                'config' => array('count' => 100)
            ),
            array(
                'blocktype' => 'othersrecentposts',
                'artefactplugin' => 'blog',
                'title' => get_string('title',
                        'blocktype.blog/othersrecentposts'),
                'row' => 1,
                'column' => 2,
                'config' => array('count' => 100)
            )
        );

        $installed = get_column_sql('SELECT name FROM {blocktype_installed}');
        $weights = array(1 => 0, 2 => 0);
        $walltemplateid = $wall->get('id');

        foreach ($blocktypes as $blocktype) {
            if (in_array($blocktype['blocktype'], $installed)) {
                $weights[$blocktype['column']] ++;
                $newblock = new BlockInstance(0,
                        array(
                    'blocktype' => $blocktype['blocktype'],
                    'title' => $blocktype['title'],
                    'view' => $walltemplateid,
                    'row' => $blocktype['row'],
                    'column' => $blocktype['column'],
                    'order' => $weights[$blocktype['column']],
                    'configdata' => $blocktype['config']
                ));

                $newblock->commit();
            }
        }

        // Delete previous dashboards
        update_dashboards($wall);
    }

    if ($version < 2014092901) {
        $record = get_record('view', 'owner', 0, 'type', 'dashboard');
        // Remove titles from system dashboard so new users get blocks with
        // automatically generated, translatable default titles.
        $dashboards = get_column('view', 'id', 'type', 'dashboard');
        $ids = implode(', ', $dashboards);

        set_field_select('block_instance', 'title', '', "view IN ($ids)",
                array());

        $dashboard = new View($record->id);
        $dashboard->set('description', 2014092901);
        $dashboard->commit();
    }

    if ($version < 2014121200) {
        $record = get_record('view', 'owner', 0, 'type', 'dashboard');
        // Add assigned learning objects -block.
        if ($record) {
            $blockconfig = array(
                'blocktype' => 'assignedlearningobject',
                'row' => 1,
                'column' => 1,
                'order' => 1,
                'returndata' => false,
                'configdata' => array(
                    'count' => 100
                )
            );

            // Update dashboards
            $dashboardviewids = get_column('view', 'id', 'type', 'dashboard');

            foreach ($dashboardviewids as $viewid) {
                $dashboardview = new View($viewid);
                $dashboardview->addblocktype($blockconfig);

                unset($dashboardview);
            }

            $dashboardtemplate = new View($record->id);
            $dashboardtemplate->set('description', 2014121200);
            $dashboardtemplate->commit();
        }
    }

    if ($version < 2014121201) {
        $record = get_record('view', 'owner', 0, 'type', 'dashboard');
        // Add returned to me -block.
        if ($record) {
            $blockconfig = array(
                'blocktype' => 'returnedtome',
                'row' => 1,
                'column' => 1,
                'order' => 1,
                'returndata' => false,
                'configdata' => array(
                    'count' => 100
                )
            );

            // Update dashboards
            $dashboardviewids = get_column('view', 'id', 'type', 'dashboard');

            foreach ($dashboardviewids as $viewid) {
                $dashboardview = new View($viewid);
                $dashboardview->addblocktype($blockconfig);

                unset($dashboardview);
            }

            $dashboardtemplate = new View($record->id);
            $dashboardtemplate->set('description', 2014121201);
            $dashboardtemplate->commit();
        }
    }

    db_commit();
}

function update_dashboards(View $dashboardtemplate) {
    $dashboardviewids = get_column('view', 'id', 'type', 'dashboard');
    $title = $dashboardtemplate->get('title');
    $templateid = $dashboardtemplate->get('id');

    foreach ($dashboardviewids as $viewid) {
        $dashboard = new View($viewid);
        $owner = $dashboard->get('owner');

        if ($owner > 0) {
            $dashboard->delete();

            View::create_from_template(array(
                'template' => 0,
                'owner' => $owner,
                'title' => $title,
                'description' => '',
                'type' => 'dashboard'
                    ), $templateid, $owner, false, false);
        }
    }
}
