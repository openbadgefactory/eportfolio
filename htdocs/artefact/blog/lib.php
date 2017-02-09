<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Users can create blogs and blog posts using this plugin.
 */
class PluginArtefactBlog extends PluginArtefact {

    public static function postinst($fromversion) {
        if ($fromversion == 0) {
            // New blog view type.
	  $obj = (object) array('type' => 'blog');
//	  $vtobj = (object) array(
//				  'blocktype' => 'blog',
//				  'viewtype' => 'blog'
//				  );
	  ensure_record_exists('view_type', $obj, $obj);
//	  ensure_record_exists('blocktype_installed_viewtype', $vtobj, $vtobj);
        }
    }

    public static function get_artefact_types() {
        return array(
            'blog',
            'blogpost',
        );
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'blog';
    }

    public static function is_active() {
        return get_field('artefact_installed', 'active', 'name', 'blog');
    }

    public static function menu_items() {
        global $USER;
        $tab = array(
            'path'   => 'content/blogs',
            'weight' => 40,
        );
        if ($USER->get_account_preference('multipleblogs')) {
            $tab['url']   = 'artefact/blog/index.php';
            $tab['title'] = get_string('blogs', 'artefact.blog');
        }
        else {
            $tab['url']   = 'artefact/blog/view/index.php';
            $tab['title'] = get_string('blog', 'artefact.blog');
        }
        return array('content/blogs' => $tab);
    }

    public static function get_cron() {
        return array();
    }


    public static function get_event_subscriptions() {
        return array(
            (object)array(
                'plugin'       => 'blog',
                'event'        => 'createuser',
                'callfunction' => 'create_default_blog',
            ),
        );
    }

    public static function block_advanced_options_element($configdata, $artefacttype) {
        $strartefacttype = get_string($artefacttype, 'artefact.blog');
        return array(
            'type' => 'fieldset',
            'name' => 'advanced',
            'collapsible' => true,
            'collapsed' => false,
            'legend' => get_string('moreoptions', 'artefact.blog'),
            'elements' => array(
                'copytype' => array(
                    'type' => 'select',
                    'title' => get_string('blockcopypermission', 'view'),
                    'description' => get_string('blockcopypermissiondesc', 'view'),
                    'defaultvalue' => isset($configdata['copytype']) ? $configdata['copytype'] : 'nocopy',
                    'options' => array(
                        'nocopy' => get_string('copynocopy', 'artefact.blog'),
                        'reference' => get_string('copyreference', 'artefact.blog', $strartefacttype),
                        'full' => get_string('copyfull', 'artefact.blog', $strartefacttype),
                    ),
                ),
            ),
        );
    }

    public static function create_default_blog($event, $user) {
        $name = display_name($user, null, true);
        $blog = new ArtefactTypeBlog(0, (object) array(
            'title'       => get_string('defaultblogtitle', 'artefact.blog', $name),
            'owner'       => $user['id'],
        ));
        $blog->commit();
    }

    public static function get_artefact_type_content_types() {
        return array(
            'blogpost' => array('text'),
        );
    }

    public static function progressbar_link($artefacttype) {
        return 'artefact/blog/view/index.php';
    }
}

/**
 * A Blog artefact is a collection of BlogPost artefacts.
 */
class ArtefactTypeBlog extends ArtefactType {

    /**
     * This constant gives the per-page pagination for listing blogs.
     */
    const pagination = 10;


    /**
     * We override the constructor to fetch the extra data.
     *
     * @param integer
     * @param object
     */
    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if (empty($this->id)) {
            $this->container = 1;
        }
    }

    /**
     * This function updates or inserts the artefact.  This involves putting
     * some data in the artefact table (handled by parent::commit()), and then
     * some data in the artefact_blog_blog table.
     */
    public function commit() {
        // Just forget the whole thing when we're clean.
        if (empty($this->dirty)) {
            return;
        }

        // We need to keep track of newness before and after.
        $new = empty($this->id);

        // Commit to the artefact table.
        parent::commit();

        $this->dirty = false;
    }

    /**
     * This function extends ArtefactType::delete() by deleting blog-specific
     * data.
     */
    public function delete() {
        if (empty($this->id)) {
            return;
        }

        // Delete the artefact and all children.
        parent::delete();
    }

    /**
     * Checks that the person viewing this blog is the owner. If not, throws an
     * AccessDeniedException. Used in the blog section to ensure only the
     * owners of the blogs can view or change them there. Other people see
     * blogs when they are placed in views.
     */
    public function check_permission() {
        global $USER;
        if ($USER->get('id') != $this->owner) {
            throw new AccessDeniedException(get_string('youarenottheownerofthisblog', 'artefact.blog'));
        }
    }


    public function describe_size() {
        return $this->count_children() . ' ' . get_string('posts', 'artefact.blog');
    }

    /**
     * Renders a blog.
     *
     * @param  array  Options for rendering
     * @return array  A two key array, 'html' and 'javascript'.
     */
    public function render_self($options) {
        $this->add_to_render_path($options);

        if (!isset($options['limit'])) {
            $limit = self::pagination;
        }
        else if ($options['limit'] === false) {
            $limit = null;
        }
        else {
            $limit = (int) $options['limit'];
        }
        $offset = isset($options['offset']) ? intval($options['offset']) : 0;

        if (!isset($options['countcomments'])) {
            // Count comments if this is a view
            $options['countcomments'] = (!empty($options['viewid']));
        }

        $posts = ArtefactTypeBlogpost::get_posts($this->id, $limit, $offset, $options);

        $template = 'artefact:blog:viewposts.tpl';

        $baseurl = get_config('wwwroot') . 'view/artefact.php?artefact=' . $this->id;
        if (!empty($options['viewid'])) {
            $baseurl .= '&view=' . $options['viewid'];
        }
        $pagination = array(
            'baseurl' => $baseurl,
            'id' => 'blogpost_pagination',
            'datatable' => 'postlist',
            'jsonscript' => 'artefact/blog/posts.json.php',
        );

        ArtefactTypeBlogpost::render_posts($posts, $template, $options, $pagination);

        $smarty = smarty_core();
        if (isset($options['viewid'])) {
            $smarty->assign('artefacttitle', '<a href="' . get_config('wwwroot') . 'view/artefact.php?artefact='
                                             . $this->get('id') . '&view=' . $options['viewid']
                                             . '">' . hsc($this->get('title')) . '</a>');
        }
        else {
            $smarty->assign('artefacttitle', hsc($this->get('title')));
        }

        if (!empty($options['details']) and get_config('licensemetadata')) {
            $smarty->assign('license', render_license($this));
        }
        else {
            $smarty->assign('license', false);
        }

        $options['hidetitle'] = true;
        $smarty->assign('options', $options);
        $smarty->assign('description', $this->get('description'));
        $smarty->assign('owner', $this->get('owner'));
        $smarty->assign('tags', $this->get('tags'));

        $smarty->assign_by_ref('posts', $posts);

        return array('html' => $smarty->fetch('artefact:blog:blog.tpl'), 'javascript' => '');
    }


    public static function get_icon($options=null) {
        global $THEME;
        return $THEME->get_url('images/journal.png', false);
    }

    public static function is_singular() {
        return false;
    }

    public static function collapse_config() {
    }

    /**
     * This function returns a list of the given user's blogs.
     *
     * @param User
     * @return array (count: integer, data: array)
     */
    public static function get_blog_list($limit, $offset) {
        global $USER;
        ($result = get_records_sql_array("
         SELECT b.id, b.title, b.description, b.locked, COUNT(p.id) AS postcount, b.mtime -- EKAMPUS
         FROM {artefact} b LEFT JOIN {artefact} p ON (p.parent = b.id AND p.artefacttype = 'blogpost')
         WHERE b.owner = ? AND b.artefacttype = 'blog'
         GROUP BY b.id, b.title, b.description, b.locked
         ORDER BY b.title", array($USER->get('id')), $offset, $limit))
            || ($result = array());

        foreach ($result as &$r) {
            if (!$r->locked) {
                $r->deleteform = ArtefactTypeBlog::delete_form($r->id, $r->title);
            }
        }

        $count = (int)get_field('artefact', 'COUNT(*)', 'owner', $USER->get('id'), 'artefacttype', 'blog');

        return array($count, $result);
    }

     // <EKAMPUS
    public static function get_blogs() {
        global $USER;
        $wwwroot = get_config('wwwroot');
        $list = self::get_blog_list(null, null);
        $blogs = $list[1];

        if (is_array($blogs)) {
            $blogviews = get_user_artefact_views('blog', $USER->id);
            $accesslists = View::get_accesslists($USER->id, null, null, array('blog'));
            $blogids = array();

            foreach ($blogs as &$blog) {
                $blogids[] = $blog->id;

                $blog->publicity = find_artefact_publicity($blog->id, $blogviews, $accesslists);
                $blog->view = find_artefact_view($blog->id, $blogviews);
                $blog->menuitems = array(
                    array(
                        'title' => get_string('menusettings', 'artefact.blog'),
                        'url' => $wwwroot . 'artefact/blog/settings/index.php?id=' . $blog->id,
                    ));

                // Blog is published (page has been created), show link to
                // access settings.
                if ($blog->view){
                    $blog->menuitems[] = array(
                        'title' => get_string('editaccess', 'view'),
                        'url' => $wwwroot . 'view/access.php?id=' . $blog->view.'&backto=artefact/blog',
                        'classes' => 'editaccess',
                    );
                }
                // Blog hasn't been published yet, show a link which creates
                // a page and then jumps to access settings.
                else {
                    $blog->menuitems[] = array(
                        'title' => get_string('editaccess', 'view'),
                        'url' => '#',
                        'classes' => 'create-view'
                    );
                }

                $blog->menuitems[] = array(
                    'title' => get_string('delete'),
                    'url' => '#',
                    'classes' => 'delete-blog'
                    //'form' => ArtefactTypeBlog::delete_form($blog->id, $blog->title),
                );
            }

            // Quick and dirty way to get the tags for each blog.
            $artefact_tags = array();

            if ($tags = ArtefactType::tags_from_id_list($blogids)) {
                foreach($tags as $at) {
                    if (!isset($artefact_tags[$at->artefact])) {
                        $artefact_tags[$at->artefact] = array();
                    }

                    $artefact_tags[$at->artefact][] = $at->tag;
                }
            }

            foreach ($blogs as &$blog) {
                $tags = isset($artefact_tags[$blog->id]) ? $artefact_tags[$blog->id] : array();
                $blog->jsontags = json_encode($tags, JSON_HEX_QUOT);
            }
        }

        return $blogs;
    }

    public static function get_blog_tags() {
        global $USER;

        return get_column_sql("
            SELECT DISTINCT(tag)
              FROM {artefact_tag}
             WHERE artefact IN (
                SELECT id
                  FROM {artefact}
                 WHERE owner = ? AND artefacttype IN (?, ?)
             )
             ORDER BY tag COLLATE utf8_swedish_ci ASC", array($USER->id, 'blog', 'blogpost'));
    }
    // EKAMPUS>

    public static function build_blog_list_html(&$blogs) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('blogs', $blogs);
        $blogs->tablerows = $smarty->fetch('artefact:blog:bloglist.tpl');
        $pagination = build_pagination(array(
            'id' => 'bloglist_pagination',
            'class' => 'center',
            'url' => get_config('wwwroot') . 'artefact/blog/index.php',
            'jsonscript' => 'artefact/blog/index.json.php',
            'datatable' => 'bloglist',
            'count' => $blogs->count,
            'limit' => $blogs->limit,
            'offset' => $blogs->offset,
            'firsttext' => '',
            'previoustext' => '',
            'nexttext' => '',
            'lasttext' => '',
            'numbersincludefirstlast' => false,
            'resultcounttextsingular' => get_string('blog', 'artefact.blog'),
            'resultcounttextplural' => get_string('blogs', 'artefact.blog'),
        ));
        $blogs->pagination = $pagination['html'];
        $blogs->pagination_js = $pagination['javascript'];
    }

    /**
     * This function creates a new blog.
     *
     * @param User
     * @param array
     */
    public static function new_blog(User $user, array $values) {
        $artefact = new ArtefactTypeBlog();
        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('owner', $user->get('id'));
        $artefact->set('tags', $values['tags']);
        if (get_config('licensemetadata')) {
            $artefact->set('license', $values['license']);
            $artefact->set('licensor', $values['licensor']);
            $artefact->set('licensorurl', $values['licensorurl']);
        }
        $artefact->commit();

        // <EKAMPUS
        // Return the new blog instance for later use.
        return $artefact;
        // EKAMPUS>
    }

    /**
     * This function updates an existing blog.
     *
     * @param User
     * @param array
     */
    public static function edit_blog(User $user, array $values) {
        if (empty($values['id']) || !is_numeric($values['id'])) {
            return;
        }

        $artefact = new ArtefactTypeBlog($values['id']);
        if ($user->get('id') != $artefact->get('owner')) {
            return;
        }

        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('tags', $values['tags']);
        if (get_config('licensemetadata')) {
            $artefact->set('license', $values['license']);
            $artefact->set('licensor', $values['licensor']);
            $artefact->set('licensorurl', $values['licensorurl']);
        }
        $artefact->commit();
    }

    public static function get_links($id) {
        $wwwroot = get_config('wwwroot');

        return array(
            '_default'                                  => $wwwroot . 'artefact/blog/view/index.php?id=' . $id,
            get_string('blogsettings', 'artefact.blog') => $wwwroot . 'artefact/blog/settings/index.php?id=' . $id,
        );
    }

    public function copy_extra($new) {
        $new->set('title', get_string('Copyof', 'mahara', $this->get('title')));
    }

    /**
     * Returns the number of posts in this blog that have been published.
     *
     * The result of this function looked up from the database each time, so
     * cache it if you know it's safe to do so.
     *
     * @return int
     */
    public function count_published_posts() {
        return (int)get_field_sql("
            SELECT COUNT(*)
            FROM {artefact} a
            LEFT JOIN {artefact_blog_blogpost} bp ON a.id = bp.blogpost
            WHERE a.parent = ?
            AND bp.published = 1", array($this->get('id')));
    }

    public static function delete_form($id, $title = '') {
        global $THEME;

        $confirm = get_string('deleteblog?', 'artefact.blog');

        // Check if this blog has posts.
        $postcnt = count_records_sql("
            SELECT COUNT(*)
            FROM {artefact} a
            INNER JOIN {artefact_blog_blogpost} bp ON a.id = bp.blogpost
            WHERE a.parent = ?
            ", array($id));
        if ($postcnt > 0) {
            $confirm = get_string('deletebloghaspost?', 'artefact.blog', $postcnt);

            // Check if this blog posts used in views.
            $viewscnt = count_records_sql("
                SELECT COUNT(DISTINCT(va.view))
                FROM {artefact} a
                INNER JOIN {view_artefact} va ON a.id = va.artefact
                WHERE a.parent = ? OR a.id = ?
                ", array($id, $id));
            if ($viewscnt > 0) {
                $confirm = get_string('deletebloghasview?', 'artefact.blog', $viewscnt);
            }
        }

        return pieform(array(
            'name' => 'delete_' . $id,
            'successcallback' => 'delete_blog_submit',
            'renderer' => 'oneline',
            'elements' => array(
                'delete' => array(
                    'type' => 'hidden',
                    'value' => $id,
                ),
                'submit' => array(
                    'type' => 'submit',
                    'confirm' => $confirm,
                    'value' => get_string('delete'),
                    'alt' => get_string('deletespecific', 'mahara', $title),
                    'elementtitle' => get_string('delete'),
                ),
            ),
        ));
    }
    //< EKAMPUS
    public static function delete_blog($blogid) {
        global $SESSION, $USER;
        $blog = new ArtefactTypeBlog($blogid);
        $blog->check_permission();
        if ($blog->get('locked')) {
            $SESSION->add_error_msg(get_string('submittedforassessment', 'view'));
        }
        else {
            $user_blog_pages = self::get_user_blog_views($USER->get('id'));
            $blogview = find_artefact_view($blogid, $user_blog_pages);

            db_begin();

            if ($blogview){
                // First delete published view...

                    $view = new View($blogview);
                    $view->delete();

            }
            // ... And then delete the blog itself.
            $blog->delete();
            db_commit();
        }
        return true;
    }
    // EKAMPUS>
    /**
     *'submit' => array(
                    /* < EKAMPUS
                    'type' => 'image',
                    'src' => $THEME->get_url('images/btn_deleteremove.png'),
                    'type' => 'submit',
                    /* EKAMPUS >
                    'alt' => get_string('deletespecific', 'mahara', $title),
                    'elementtitle' => get_string('delete'),
                    'confirm' => $confirm,
                    'value' => get_string('delete'),
                ),
     * During the copying of a view, we might be allowed to copy
     * blogs. Users need to have multipleblogs enabled for these
     * to be visible.
     */
    public function default_parent_for_copy(&$view, &$template, $artefactstoignore) {
        global $USER, $SESSION;

        $viewid = $view->get('id');

        try {
            $user = get_user($view->get('owner'));
            set_account_preference($user->id, 'multipleblogs', 1);
            $SESSION->add_ok_msg(get_string('copiedblogpoststonewjournal', 'collection'));
        }
        catch (Exception $e) {
            $SESSION->add_error_msg(get_string('unabletosetmultipleblogs', 'error', $user->username, $viewid, get_config('wwwroot') . 'account/index.php'), false);
        }

        try {
            $USER->accountprefs = load_account_preferences($user->id);
        }
        catch (Exception $e) {
            $SESSION->add_error_msg(get_string('pleaseloginforjournals', 'error'));
        }

        return null;
    }

    // <EKAMPUS
    public static function create_blog_form($blogid=null, array $config = array()) {
        global $USER;

        $form = array(
            'name'            => 'createblog',
            'method'          => 'post',
            'plugintype'      => 'core',
            'pluginname'      => 'view',
            'renderer'        => 'oneline',
            'successcallback' => 'ArtefactTypeBlog::createblog_submit',
            'elements' => array(
                'new' => array(
                    'type' => 'hidden',
                    'value' => true,
                ),
                'submitcollection' => array(
                    'type' => 'hidden',
                    'value' => false,
                ),
                'submit' => array(
                    'type' => 'submit',
                    'value' => get_string('createview', 'view'),
                ),
            )
        );

        //lets see is there any blog type views which has the same blog artefact on it
        $user_blog_pages = self::get_user_blog_views($USER->get('id'));
        $blogviewid = find_artefact_view($blogid, $user_blog_pages);

        $form['elements']['owner'] = array(
            'type' => 'hidden',
            'value' => $USER->get('id'),
        );
        if ($blogid !== null) {
            $form['elements']['blogid'] = array(
                'type'  => 'hidden',
                'value' => $blogid,
            );
            if (empty($blogviewid)){
                $form['elements']['submit']['value'] = get_string('publishblog', 'artefact.blog');
                $form['name'] .= $blogid;
            }
            else {
                unset($form['elements']['submit']);
                $form['elements']['blogviewid'] = array(
                    'type' => 'hidden',
                    'value' => $blogviewid,
                );
            }
        }
        if (isset($config['blogtitle'])) {
            $form['elements']['blogtitle'] = array(
                'type'  => 'hidden',
                'value' => $config['blogtitle'],
            );
        }
        if (isset($config['description'])) {
            $form['elements']['description'] = array(
                'type'  => 'hidden',
                'value' => $config['description'],
            );
        }
        if (isset($config['tags'])) {
            $form['elements']['tags'] = array(
                'type'  => 'hidden',
                'value' => $config['tags'],
            );
        }
        return $form;
    }
     public static function get_user_blog_views($userid = null) {
        if (is_null($userid)) {
            global $USER;
            $userid = $USER->get('id');
        }

        $resumeviews = get_records_sql_assoc("
            SELECT v.id, b.id as 'bid', b.blocktype, b.configdata
              FROM {view} v
         LEFT JOIN {block_instance} b ON (v.id = b.view)
	     WHERE v.owner = ? AND v.type = ?
          ORDER BY v.id", array($userid, 'blog'));

        return is_array($resumeviews) ? $resumeviews : array();
    }

    /**
     * Creates a new page type=blog and inserts the entireblogfield into it
     * like it would be from import, then goes to access page.
     */
    public static function createblog_submit(Pieform $form, $values) {
//        if (isset($values['owner'])) {
//            $userid = $values['owner'];
//        }
//        if (isset($values['blogid'])) {
            $artefactid = $values['blogid'];
//        }

//        $title = isset($values['blogtitle']) ? $values['blogtitle'] : get_string('blog', 'artefact.blog');
//        $description = isset($values['description']) ? $values['description'] : '';

//        $tags = isset($values['tags']) ? $values['tags'] : array();

        $view = self::create_blog_view($artefactid);
        redirect(get_config('wwwroot') . 'view/access.php?new=1&id=' . $view->get('id').'&backto=artefact/blog');
    }

    /**
     * Creates a view with the blog artefact.
     *
     * @param int $blogid The id of the blog artefact.
     * @return View The created view instance.
     * @throws AccessDeniedException If the blog doesn't belong to the current
     *      user.
     */
    public static function create_blog_view($blogid) {
        global $USER;

        $blog = new ArtefactTypeBlog($blogid);
        $owner = $blog->get('owner');

        if ($USER->get('id') !== $owner) {
            throw new AccessDeniedException('Only own blogs can be published.');
        }

        $config = array(
            'title' => $blog->get('title'),
            'description' => $blog->get('description'),
            'type' => 'blog',
            'approvecomments' => '1',
            'layout' => '1',
            'tags' => ArtefactType::artefact_get_tags($blogid),
            'numrows' => 1,
            'owner' => $owner,
            'ownerformat' => 6,
            'rows' => array(
                1 => array(
                    'columns' => array(
                        1 => array(
                            1 => array(
                                'type' => 'blog',
                                'title' => '',
                                'config' => array(
                                    'artefactid' => $blogid,
                                    'count' => '5',
                                    'atomfeed' => true,
                                    'atomfeedtoken' => get_random_key(20),
                                    'copytype' => 'reference',
                                    'retractable' => false,
                                    'retractedonload' => false
                                )
                            )
                        )
                    )
                )
            )
        );

        $view = View::import_from_config($config, $owner, $format='');

        return $view;
    }
    // EKAMPUS>
}

/**
 * BlogPost artefacts occur within Blog artefacts
 */
class ArtefactTypeBlogPost extends ArtefactType {

    /**
     * This defines whether the blogpost is published or not.
     *
     * @var boolean
     */
    protected $published = false;

    /**
     * We override the constructor to fetch the extra data.
     *
     * @param integer
     * @param object
     */
    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if ($this->id) {
            if ($bpdata = get_record('artefact_blog_blogpost', 'blogpost', $this->id)) {
                foreach($bpdata as $name => $value) {
                    if (property_exists($this, $name)) {
                        $this->$name = $value;
                    }
                }
            }
            else {
                // This should never happen unless the user is playing around with blog post IDs in the location bar or similar
                throw new ArtefactNotFoundException(get_string('blogpostdoesnotexist', 'artefact.blog'));
            }
        }
        else {
            $this->allowcomments = 1; // Turn comments on for new posts
        }
    }

    /**
     * This method extends ArtefactType::commit() by adding additional data
     * into the artefact_blog_blogpost table.
     *
     * This method also works out what blockinstances this blogpost is in, and
     * informs them that they should re-check what artefacts they have in them.
     * The post content may now link to different artefacts. See {@link
     * PluginBlocktypeBlogPost::get_artefacts for more information}
     */
    public function commit() {
        if (empty($this->dirty)) {
            return;
        }

        db_begin();
        $new = empty($this->id);

        parent::commit();

        $this->dirty = true;

        $data = (object)array(
            'blogpost'  => $this->get('id'),
            'published' => ($this->get('published') ? 1 : 0)
        );

        if ($new) {
            insert_record('artefact_blog_blogpost', $data);
        }
        else {
            update_record('artefact_blog_blogpost', $data, 'blogpost');
        }

        // We want to get all blockinstances that contain this blog post. That is currently:
        // 1) All blogpost blocktypes with this post in it
        // 2) All blog blocktypes with this posts's blog in it
        //
        // With these, we tell them to rebuild what artefacts they have in them,
        // since the post content could have changed and now have links to
        // different artefacts in it
        $blockinstanceids = (array)get_column_sql('SELECT block
            FROM {view_artefact}
            WHERE artefact = ?
            OR artefact = ?', array($this->get('id'), $this->get('parent')));
        if ($blockinstanceids) {
            require_once(get_config('docroot') . 'blocktype/lib.php');
            foreach ($blockinstanceids as $id) {
                $instance = new BlockInstance($id);
                $instance->rebuild_artefact_list();
            }
        }

        db_commit();
        $this->dirty = false;
    }

    /**
     * This function extends ArtefactType::delete() by also deleting anything
     * that's in blogpost.
     */
    public function delete() {
        if (empty($this->id)) {
            return;
        }

        db_begin();
        $this->detach(); // Detach all file attachments
        delete_records('artefact_blog_blogpost', 'blogpost', $this->id);

        parent::delete();
        db_commit();
    }

    public static function bulk_delete($artefactids) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select('artefact_blog_blogpost', 'blogpost IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
        db_commit();
    }


    /**
     * Checks that the person viewing this blog is the owner. If not, throws an
     * AccessDeniedException. Used in the blog section to ensure only the
     * owners of the blogs can view or change them there. Other people see
     * blogs when they are placed in views.
     */
    public function check_permission() {
        global $USER;
        if ($USER->get('id') != $this->owner) {
            throw new AccessDeniedException(get_string('youarenottheownerofthisblogpost', 'artefact.blog'));
        }
    }

    public function describe_size() {
        return $this->count_attachments() . ' ' . get_string('attachments', 'artefact.blog');
    }

    public function render_self($options) {
        $smarty = smarty_core();
        $artefacturl = get_config('wwwroot') . 'view/artefact.php?artefact=' . $this->get('id');
        if (isset($options['viewid'])) {
            $artefacturl .= '&view=' . $options['viewid'];
        }
        $smarty->assign('artefacturl', $artefacturl);
        if (empty($options['hidetitle'])) {
            if (isset($options['viewid'])) {
                $smarty->assign('artefacttitle', '<a href="' . $artefacturl . '">' . hsc($this->get('title')) . '</a>');
            }
            else {
                $smarty->assign('artefacttitle', hsc($this->get('title')));
            }
        }

        // We need to make sure that the images in the post have the right viewid associated with them
        $postcontent = $this->get('description');
        if (isset($options['viewid'])) {
            safe_require('artefact', 'file');
            $postcontent = ArtefactTypeFolder::append_view_url($postcontent, $options['viewid']);
            if (isset($options['countcomments']) && $this->allowcomments) {
                safe_require('artefact', 'comment');
                $empty = array();
                $ids = array($this->id);
                $commentcount = ArtefactTypeComment::count_comments($empty, $ids);
                $smarty->assign('commentcount', $commentcount ? $commentcount[$this->id]->comments : 0);
            }
        }
        $smarty->assign('artefactdescription', $postcontent);
        $smarty->assign('artefact', $this);
        if (!empty($options['details']) and get_config('licensemetadata')) {
            $smarty->assign('license', render_license($this));
        }
        else {
            $smarty->assign('license', false);
        }

        $attachments = $this->get_attachments();
        if ($attachments) {
            $this->add_to_render_path($options);
            require_once(get_config('docroot') . 'artefact/lib.php');
            foreach ($attachments as &$attachment) {
                $f = artefact_instance_from_id($attachment->id);
                $attachment->size = $f->describe_size();
                $attachment->iconpath = $f->get_icon(array('id' => $attachment->id, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0));
                $attachment->viewpath = get_config('wwwroot') . 'view/artefact.php?artefact=' . $attachment->id . '&view=' . (isset($options['viewid']) ? $options['viewid'] : 0);
                $attachment->downloadpath = get_config('wwwroot') . 'artefact/file/download.php?file=' . $attachment->id;
                if (isset($options['viewid'])) {
                    $attachment->downloadpath .= '&view=' . $options['viewid'];
                }
            }
            $smarty->assign('attachments', $attachments);
        }
        $smarty->assign('postedbyon', get_string('postedbyon', 'artefact.blog',
                                                 display_name($this->owner),
                                                 format_date($this->ctime)));
        return array('html' => $smarty->fetch('artefact:blog:render/blogpost_renderfull.tpl'),
                     'javascript' => '');
    }


    public function can_have_attachments() {
        return true;
    }


    public static function get_icon($options=null) {
        global $THEME;
        return $THEME->get_url('images/journal_entry.png', false);
    }

    public static function is_singular() {
        return false;
    }

    public static function collapse_config() {
    }

    /**
     * This function returns a list of posts in a given blog.
     *
     * @param integer
     * @param integer
     * @param integer
     * @param array
     */
    public static function get_posts($id, $limit, $offset, $viewoptions=null) {

        $results = array(
            'limit'  => $limit,
            'offset' => $offset,
        );

        // If viewoptions is null, we're getting posts for the my blogs area,
        // and we should get all posts & show drafts first.  Otherwise it's a
        // blog in a view, and we should only get published posts.

        $from = "
            FROM {artefact} a LEFT JOIN {artefact_blog_blogpost} bp ON a.id = bp.blogpost
            WHERE a.artefacttype = 'blogpost' AND a.parent = ?";

        if (!is_null($viewoptions)) {
            if (isset($viewoptions['before'])) {
                $from .= " AND a.ctime < '{$viewoptions['before']}'";
            }
            $from .= ' AND bp.published = 1';
        }

        $results['count'] = count_records_sql('SELECT COUNT(*) ' . $from, array($id));

        $data = get_records_sql_assoc('
            SELECT
                a.id, a.title, a.description, a.author, a.authorname, ' .
                db_format_tsfield('a.ctime', 'ctime') . ', ' . db_format_tsfield('a.mtime', 'mtime') . ',
                a.locked, bp.published, a.allowcomments ' . $from . '
            ORDER BY bp.published ASC, a.ctime DESC, a.id DESC',
            array($id),
            $offset, $limit
        );

        if (!$data) {
            $results['data'] = array();
            return $results;
        }

        // Get the attached files.
        $postids = array_map(create_function('$a', 'return $a->id;'), $data);
        $files = ArtefactType::attachments_from_id_list($postids);
        if ($files) {
            safe_require('artefact', 'file');
            foreach ($files as &$file) {
                // <EKAMPUS
                // Fix missing icons when browsing other user's blogs.
                $options = array('id' => $file->attachment);

                if (!empty($viewoptions['viewid'])) {
                    $options['viewid'] = $viewoptions['viewid'];
                }

                $file->icon = call_static_method(generate_artefact_class_name($file->artefacttype), 'get_icon', $options);
                // EKAMPUS>
                $data[$file->artefact]->files[] = $file;
            }
        }

        if ($tags = ArtefactType::tags_from_id_list($postids)) {
            foreach($tags as &$at) {
                $data[$at->artefact]->tags[] = $at->tag;
            }
        }

        // Get comment counts
        if (!empty($viewoptions['countcomments'])) {
            safe_require('artefact', 'comment');
            $viewids = array();
            $commentcounts = ArtefactTypeComment::count_comments($viewids, array_keys($data));
        }

        foreach ($data as &$post) {
            // Format dates properly
            if (is_null($viewoptions)) {
                // My Blogs area: create forms for changing post status & deleting posts.
                $post->changepoststatus = ArtefactTypeBlogpost::changepoststatus_form($post->id, $post->published);
                $post->delete = ArtefactTypeBlogpost::delete_form($post->id, $post->title);
            }
            else {
                $by = $post->author ? display_default_name($post->author) : $post->authorname;
                $post->postedby = get_string('postedbyon', 'artefact.blog', $by, format_date($post->ctime));
                if (isset($commentcounts)) {
                    $post->commentcount = isset($commentcounts[$post->id]) ? $commentcounts[$post->id]->comments : 0;
                }
            }
            $post->ctime = format_date($post->ctime, 'strftimedaydatetime');
            $post->mtime = format_date($post->mtime);

            // Ensure images in the post have the right viewid associated with them
            if (!empty($viewoptions['viewid'])) {
                safe_require('artefact', 'file');
                $post->description = ArtefactTypeFolder::append_view_url($post->description, $viewoptions['viewid']);
            }
        }

        $results['data'] = array_values($data);

        return $results;
    }

    /**
     * This function renders a list of posts as html
     *
     * @param array posts
     * @param string template
     * @param array options
     * @param array pagination
     */
    public function render_posts(&$posts, $template, $options, $pagination) {
        $smarty = smarty_core();
        $smarty->assign('options', $options);
        $smarty->assign('posts', $posts['data']);

        $posts['tablerows'] = $smarty->fetch($template);

        if ($posts['limit'] && $pagination) {
            $pagination = build_pagination(array(
                'id' => $pagination['id'],
                'class' => 'center',
                'datatable' => $pagination['datatable'],
                'url' => $pagination['baseurl'],
                'jsonscript' => $pagination['jsonscript'],
                'count' => $posts['count'],
                'limit' => $posts['limit'],
                'offset' => $posts['offset'],
                'numbersincludefirstlast' => false,
                'resultcounttextsingular' => get_string('post', 'artefact.blog'),
                'resultcounttextplural' => get_string('posts', 'artefact.blog'),
            ));
            $posts['pagination'] = $pagination['html'];
            $posts['pagination_js'] = $pagination['javascript'];
        }
    }

    /**
    /**
     * This function creates a new blog post.
     *
     * @param User
     * @param array
     */
    public static function new_post(User $user, array $values) {
        $artefact = new ArtefactTypeBlogPost();
        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('published', $values['published']);
        $artefact->set('owner', $user->get('id'));
        $artefact->set('parent', $values['parent']);
        $artefact->commit();
        return true;
    }

    /**
     * This function updates an existing blog post.
     *
     * @param User
     * @param array
     */
    public static function edit_post(User $user, array $values) {
        $artefact = new ArtefactTypeBlogPost($values['id']);
        if ($user->get('id') != $artefact->get('owner')) {
            return false;
        }

        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('published', $values['published']);
        $artefact->set('tags', $values['tags']);
        if (get_config('licensemetadata')) {
            $artefact->set('license', $values['license']);
            $artefact->set('licensor', $values['licensor']);
            $artefact->set('licensorurl', $values['licensorurl']);
        }
        $artefact->commit();
        return true;
    }

    public static function changepoststatus_form($id, $published = null) {
        //Get current post status from database
        if ($published === null) {
            $post = new ArtefactTypeBlogPost($id);
            $published = $post->published;
        }
        if ($published) {
            $strchangepoststatus = get_string('unpublish', 'artefact.blog');
        }
        else {
            $strchangepoststatus = get_string('publish', 'artefact.blog');
        }
        return pieform(array(
            'name' => 'changepoststatus_' . $id,
            'jssuccesscallback' => 'changepoststatus_success',
            'successcallback' => 'changepoststatus_submit',
            'jsform' => true,
            'renderer' => 'oneline',
            'elements' => array(
                'changepoststatus' => array(
                    'type' => 'hidden',
                    'value' => $id,
                ),
                'currentpoststatus' => array(
                    'type' => 'hidden',
                    'value' => $published,
                ),'submit' => array(
                    'type' => 'submit',
                    'class' => 'publish',
                    'value' => $strchangepoststatus,
                    'help' => true,
                ),
            ),
        ));
    }

    public static function delete_form($id, $title = '') {
        global $THEME;
        return pieform(array(
            'name' => 'delete_' . $id,
            'successcallback' => 'delete_submit',
            'jsform' => true,
            'jssuccesscallback' => 'delete_success',
            'renderer' => 'oneline',
            'elements' => array(
                'delete' => array(
                    'type' => 'hidden',
                    'value' => $id,
                    'help' => true,
                ),
                'submit' => array(
                    'type' => 'image',
                    'src' => $THEME->get_url('images/btn_deleteremove.png'),
                    'alt' => get_string('deletespecific', 'mahara', $title),
                    'elementtitle' => get_string('delete'),
                    'confirm' => get_string('deleteblogpost?', 'artefact.blog'),
                    'value' => get_string('delete'),
                ),
            ),
        ));
    }

    /**
     * This function changes the blog post status.
     *
     * @param $newpoststatus: boolean 1=published, 0=draft
     * @return boolean
     */
    public function changepoststatus($newpoststatus) {
        if (!$this->id) {
            return false;
        }

        $data = (object)array(
                'blogpost'  => $this->id,
                'published' => (int) $newpoststatus
        );

        if (get_field('artefact_blog_blogpost', 'COUNT(*)', 'blogpost', $this->id)) {
            update_record('artefact_blog_blogpost', $data, 'blogpost');
        }
        else {
            insert_record('artefact_blog_blogpost', $data);
        }
        return true;
    }

    public static function get_links($id) {
        $wwwroot = get_config('wwwroot');

        return array(
            '_default'                                  => $wwwroot . 'artefact/blog/post.php?blogpost=' . $id,
        );
    }

    public function update_artefact_references(&$view, &$template, &$artefactcopies, $oldid) {
        parent::update_artefact_references($view, $template, $artefactcopies, $oldid);
        // Attach copies of the files that were attached to the old post.
        // Update <img> tags in the post body to refer to the new image artefacts.
        $regexp = array();
        $replacetext = array();
        if (isset($artefactcopies[$oldid]->oldattachments)) {
            foreach ($artefactcopies[$oldid]->oldattachments as $a) {
                if (isset($artefactcopies[$a])) {
                    $this->attach($artefactcopies[$a]->newid);
                }
                $regexp[] = '#<img([^>]+)src="' . get_config('wwwroot') . 'artefact/file/download.php\?file=' . $a . '"#';
                $replacetext[] = '<img$1src="' . get_config('wwwroot') . 'artefact/file/download.php?file=' . $artefactcopies[$a]->newid . '"';
            }
            $this->set('description', preg_replace($regexp, $replacetext, $this->get('description')));
        }
    }

    /**
     * During the copying of a view, we might be allowed to copy
     * blogposts but not the containing blog.  We need to create a new
     * blog to hold the copied posts.
     */
    public function default_parent_for_copy(&$view, &$template, $artefactstoignore) {
        static $blogids;
        global $USER, $SESSION;

        $viewid = $view->get('id');

        if (isset($blogids[$viewid])) {
            return $blogids[$viewid];
        }

        $blogname = get_string('viewposts', 'artefact.blog', $viewid);
        $data = (object) array(
            'title'       => $blogname,
            'description' => get_string('postscopiedfromview', 'artefact.blog', $template->get('title')),
            'owner'       => $view->get('owner'),
            'group'       => $view->get('group'),
            'institution' => $view->get('institution'),
        );
        $blog = new ArtefactTypeBlog(0, $data);
        $blog->commit();

        $blogids[$viewid] = $blog->get('id');

        try {
            $user = get_user($view->get('owner'));
            set_account_preference($user->id, 'multipleblogs', 1);
            $SESSION->add_ok_msg(get_string('copiedblogpoststonewjournal', 'collection'));
        }
        catch (Exception $e) {
            $SESSION->add_error_msg(get_string('unabletosetmultipleblogs', 'error', $user->username, $viewid, get_config('wwwroot') . 'account/index.php'), false);
        }

        try {
            $USER->accountprefs = load_account_preferences($user->id);
        }
        catch (Exception $e) {
            $SESSION->add_error_msg(get_string('pleaseloginforjournals', 'error'));
        }

        return $blogids[$viewid];
    }

    /**
     * Looks through the blog post text for links to download artefacts, and
     * returns the IDs of those artefacts.
     */
    public function get_referenced_artefacts_from_postbody() {
        return artefact_get_references_in_html($this->get('description'));
    }

    public static function is_countable_progressbar() {
        return true;
    }
}
