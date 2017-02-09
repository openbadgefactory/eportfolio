<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-assessment
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeAssessment extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.assessment');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.assessment');
    }

    public static function single_only() {
        return true;
    }

    public static function get_categories() {
        return array('internal');
    }

    public static function get_viewtypes() {
        return array('dashboard');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;
        $userid = $instance->get_view()->get('owner');
        if (!$userid) {
            return '';
        }

        $smarty = smarty_core();

        safe_require('interaction', 'learningobject');
        safe_require('interaction', 'forum'); // For relative_date function.

        $assessments = self::get_all_teachers_comments($USER);

        foreach ($assessments as $key => &$object){
            if ($object->onview){
                if (!$viewrecord = get_record('view', 'id', $object->onview)) {
                    throw new ViewNotFoundException(get_string('viewnotfound', 'error', $object->onview));
                }
                $object->title = $viewrecord->title;
                $object->url = 'view/view.php?id=' . $object->onview;
            }
            else {
                if ($object->viewid){
                    if (!$viewrecord = get_record('view', 'id', $object->viewid)) {
                        throw new ViewNotFoundException(get_string('viewnotfound', 'error', $object->viewid));
                    }
                    $onartefact = $object->onartefact;
                    $artefactinstance = artefact_instance_from_id($onartefact);
                    $object->url = 'view/artefact.php?artefact=' . $onartefact . '&view='. $object->viewid;
                    $title = $artefactinstance->get('title');
                    if ($parent = $artefactinstance->get('parent')){
                        $parenttitle = artefact_instance_from_id($parent)->get('title');
                        $title = $parenttitle .': '.$title;
                    }
                    $object->title = $title;
                }
                else {
                    // <EKAMPUS
                    // ePSP field (artefacts) comments do not store the viewid.
                    // Let's find the views that contain the artefact and then
                    // find the latest view that the comment author has access
                    // privileges.
                    $artefactid = $object->onartefact;
                    $artefact = artefact_instance_from_id($artefactid);
                    $views = $artefact->get_views_instances();
                    $found = false;

                    // If the commented field is inside entireepsp-block,
                    // get_views_instances() doesn't find it. Let's find the
                    // entireepsp-artefact and the corresponding views.
                    //
                    // PENDING: This probably should be done after the following
                    // foreach-loop (if $found ends up being false).
                    if (!is_array($views) || count($views) === 0) {
                            $parentid = $artefact->get('parent');

                            if ($parentid) {
                                $parent = artefact_instance_from_id($parentid);
                                $views = $parent->get_views_instances();
                            }
                        }

                    if (is_array($views)) {
                        foreach ($views as $view) {
                            // Author can view this view, the comment can be
                            // found from here. NB:
                            if (can_view_view($view, $object->author)) {
                                $found = true;
                                $object->url = 'view/view.php?id=' . $view->get('id') .
                                        '&artefact=' . $artefactid . '&showcomment=' . $object->artefact;
                                $object->title = $view->get('title') . ': ' . $artefact->get('title');
                                break;
                            }
                        }
                    }

                    if (!$found) {
                        unset($assessments[$key]);
                    }
                    // EKAMPUS>

                }
            }
            $object->prev_date = relative_date(get_string('strftimerecentrelative', 'interaction.forum'), get_string('strftimerecentfull'), strtotime($object->mtime));
        }
        $smarty->assign('assessments', $assessments);
        return $smarty->fetch('blocktype:assessment:assessment.tpl');
    }

    public static function get_all_teachers_comments(User $user){
        $records = get_records_sql_array("
            SELECT acc.*, a.*, u.username, u.lastname, u.firstname, u.preferredname, u.email
              FROM {artefact_comment_comment} acc
         LEFT JOIN {artefact} a ON acc.artefact = a.id
         LEFT JOIN {usr} u ON a.author = u.id
         LEFT JOIN {usr_institution} ui ON u.id = ui.usr
             WHERE a.owner = ? and a.author != ? AND acc.deletedby IS NULL
                    AND (ui.staff = 1 OR ui.admin = 1)
          GROUP BY acc.artefact
          ORDER BY a.mtime DESC
             LIMIT 100", array($user->get('id'), $user->get('id')));

        return is_array($records) ? $records : array();
    }

    public static function has_instance_config() {
    //< EKAMPUS
        return true;
    }
    public static function instance_config_form($instance) {
    $configdata = $instance->get('configdata');
     $elements = array(
        'count' => array(
                'type' => 'text',
                'title' => get_string('itemstoshow', 'blocktype.blog/othersrecentposts'),
                'description'   => get_string('betweenxandy', 'mahara', 1, 100),
                'defaultvalue' => isset($configdata['count']) ? $configdata['count'] : 10,
                'size' => 3,
                'rules' => array('integer' => true, 'minvalue' => 1, 'maxvalue' => 100),
            ),
        'titlelinkurl' => array(
                'type' => 'hidden',
                'value'   => '',
            ),
        );

    return $elements;
    }
    // EKAMPUS >
    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Only makes sense for personal views
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }
    public static function get_instance_title(BlockInstance $instance) {
        return get_string('title', 'blocktype.assessment');
    }

}
