<?php

/**
 * Pre- and post-install hooks for local database customisations.
 */

function localpreinst() {
}

function localpostinst() {
    // Create empty default study journal template. If this is a clean install,
    // the template cannot be created in plugin's postinst, because the
    // zero-user doesn't exist yet.
    $time = db_format_timestamp(time());
    $template = (object) array('artefacttype' => 'studyjournaltemplate',
                'owner' => 0,
                'ctime' => $time,
                'mtime' => $time,
                'atime' => $time,
                'title' => 'notemplate',
                'note' => 'notemplate',
                'author' => 0);
    $id = insert_record('artefact', $template, 'id', true);
    insert_record('artefact_study_journal_field',
            (object) array(
                'artefact' => $id,
                'title' => '',
                'weight' => 0,
                'type' => 'text',
    ));

    $obj = (object) array(
        'name' => 'studyjournal',
        'sort' => 6
    );

    $epsp_obj = (object) array(
        'name' => 'epsp',
        'sort' => 7
    );

    $btcategories = array(
        array('blocktype' => 'studyjournal', 'category' => 'studyjournal'),
        array('blocktype' => 'entireepsp', 'category' => 'epsp'),
        array('blocktype' => 'singleepspfield', 'category' => 'epsp')
    );

    ensure_record_exists('blocktype_category', $obj, $obj);
    ensure_record_exists('blocktype_category', $epsp_obj, $epsp_obj);

    foreach ($btcategories as $item) {
        $catobj = (object) $item;
        ensure_record_exists('blocktype_installed_category', $catobj, $catobj);
    }

    set_config('dropdownmenu', 1);
    set_config('sitename', 'ePortfolio');
    set_config('theme', 'eportfolio');
    set_config('lang', 'fi.utf8');
    set_config('homepageinfo', 0);
}
