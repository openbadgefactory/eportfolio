<?php

define('INTERNAL', 1);
define('JSON', 1);

require dirname(dirname(dirname(__FILE__))) . '/init.php';
safe_require('artefact', 'multiresume');

form_validate(param_alphanum('sesskey', null));

$id = param_integer('id',0);
$rec = null;
if ($id) {
    $rec = get_record_sql(
        "SELECT f.* FROM {artefact_multiresume_field} f INNER JOIN {artefact} a ON f.artefact = a.id
        WHERE a.owner = ? AND f.id = ?", array($USER->id, $id));
}

$res = array('success' => 0);

db_begin();
switch (param_alpha('action')) {
    case 'save':
        if ($rec) {
            $obj = unserialize($rec->value);
            $obj->update_self($_POST);
            $rec->title = param_variable('title');
            $rec->value = $obj;
            update_record('artefact_multiresume_field', $rec);
            $res['success'] = 1;
        }
        break;

    case 'deletefield':
        if ($rec) {
            $obj = unserialize($rec->value);
            delete_records('artefact_multiresume_field', 'id', $id);
            $res['success'] = 1;
        }
        break;

    case 'deleterow':
        if ($rec) {
            $obj = unserialize($rec->value);
            unset($obj->rows[param_integer('row')]);
            $obj->rows = array_values($obj->rows);
            $rec->value = $obj;
            update_record('artefact_multiresume_field', $rec);
            $res['success'] = 1;
        }
        break;

    case 'reorderrows':
        if ($rec) {
            $obj = unserialize($rec->value);            
            $idx = param_integer('row');

            $other = param_alpha('direction') == 'up' ? $idx - 1 : $idx + 1;
            if (isset($obj->rows[$other]) && isset($obj->rows[$idx])) {
                $tmp = $obj->rows[$other];
                $obj->rows[$other] = $obj->rows[$idx];
                $obj->rows[$idx] = $tmp;
                $rec->value = $obj;
                update_record('artefact_multiresume_field', $rec);
                $res['success'] = 1;
            }
        }
        break;

    case 'reorderfields':
        if ($rec) {
            $max = get_field_sql(
                "SELECT MAX(`order`) FROM {artefact_multiresume_field} WHERE artefact = ?", array($rec->artefact));
            $idx = $rec->order;
            $other = param_alpha('direction') == 'up' ? $idx - 1 : $idx + 1;
            if ($other >= 0 && $other <= $max) {
                db_begin();
                execute_sql("UPDATE {artefact_multiresume_field} SET `order` = ? WHERE artefact = ? AND `order` = ?",
                    array($idx, $rec->artefact, $other)
                );
                $rec->order = $other;
                update_record('artefact_multiresume_field', $rec);
                db_commit();
                $res['success'] = 1;
            }
        }
        break;
    case 'orderfields':
        $orderlist = param_variable('order');
        if ($orderlist){
            $orderlist = explode(',', $orderlist);
            
            $count = 0;
            foreach($orderlist as $order){
                
                $orders = explode('_', $order);
                
                db_begin();
                execute_sql("UPDATE {artefact_multiresume_field} SET `order` = ? WHERE `id` = ?",
                        array($count, $orders[1]));
                db_commit();
                    
                $count++;
            }
            $res['success'] = 1;
        }   
        break;
}
db_commit();

header('Content-Type: application/json');
echo json_encode($res);
