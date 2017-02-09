<?php

function pieform_element_multiresumefield(Pieform $form, $element) {
    global $USER;

    $artefacts = get_records_sql_array(
        "SELECT id, title, description FROM {artefact} WHERE artefacttype = 'multiresume' AND owner = ?",
        array($USER->id)
    );
    if (empty($artefacts)) {
        return get_string('youhavenoresumes', 'artefact.multiresume');
    }

    $singular = @$element['singular'];

    $available = array('singular' => '');
    if (!$singular) {
        $available = get_languages();
    }

    $default = array();

    $languages = array();
    foreach ($available AS $key => $value) {
        $lang = str_replace('.', '_', $key);
        $languages[$lang] = $value;

        if (!empty($element['defaultvalue'][$lang]['artefact'])) {
            $default[$lang]['artefact'] = $element['defaultvalue'][$lang]['artefact'];
        }

        if (!empty($element['defaultvalue'][$lang]['field'])) {
            $default[$lang]['field'] = $element['defaultvalue'][$lang]['field'];
        }

        if (!empty($element['defaultvalue'][$lang]['rows'])) {
            $default[$lang]['rows'] = $element['defaultvalue'][$lang]['rows'];
        }
        else {
            $default[$lang]['rows'] = array();
        }


        if (empty($default[$lang]['artefact'])) {
            $default[$lang]['artefact'] = $artefacts[0]->id;
        }

        $fields = array(0 => '--');
        $rows   = array();
        foreach ($artefacts AS $a) {
            $res = get_records_sql_array(
                "SELECT id, title, value FROM {artefact_multiresume_field} WHERE artefact = ? ORDER BY `order`", array($a->id)
            );
            if (empty($default[$lang]['field'])) {
                $default[$lang]['field'] = 0;
            }
            $fs = array();
            foreach ($res AS $r) {
                $fs[$r->id] = array('id' => $r->id, 'title' => $r->title);
                $obj = unserialize($r->value);
                if (!empty($obj->rows)) {
                    $rows[$r->id] = $obj->rows;
                }
            }
            $fields[$a->id] = $fs;
        }
    }

    $smarty = smarty_core();
    $smarty->assign('name', $element['name']);

    $smarty->assign('singular', $singular);
    $smarty->assign('languages', $languages);
    $smarty->assign('default', $default);
    $smarty->assign('artefacts', $artefacts);
    $smarty->assign('fields', $fields);
    $smarty->assign('rows', $rows);
    return $smarty->fetch('artefact:multiresume:form/multiresumefield.tpl');
}

function pieform_element_multiresumefield_get_value(Pieform $form, $element) {
    $name = $element['name'];
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    $singular = @$element['singular'];

    $available = array('singular' => '');
    if (!$singular) {
        $available = get_languages();
    }

    $result = array();
    foreach ($available AS $key => $value) {
        $lang = str_replace('.', '_', $key);

        $artefact = (int) $global[$name . '_artefact_' . $lang];
        $field    = (int) $global[$name . '_artefact_' . $artefact . '_fields_' . $lang];
        $rows     = array();
        if (!empty($global[$name . '_artefactfield_' . $field . '_rows_' . $lang])) {
            $rows = array_map('intval', $global[$name . '_artefactfield_' . $field . '_rows_' . $lang]);
        }
        $result[$lang] = array(
            'artefact' => $artefact,
            'field'    => $field,
            'rows'     => $rows
        );
    }
    return $result;
}

