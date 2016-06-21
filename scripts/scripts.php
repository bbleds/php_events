<?php

function generate_select( $fields, $inputName){
	$html = '<select class="form-control" name="event['.$inputName.']"><option value="">Select One</option>';
	$selected = '';
	foreach($fields as $field){
		if(isset($_POST['event'][$inputName]) && $_POST['event'][$inputName] == $field){
			$selected = 'selected="selected"';
		} elseif(isset($GLOBALS['eventToEdit'][$inputName]) && $GLOBALS['eventToEdit'][$inputName] == $field){
			$selected = 'selected="selected"';
		}
		$html .= "<option value='$field' $selected >$field</option>";
	}
	$html .='</select>';

	return $html;
}

function generate_checkbox($key,$value){
	$checked = '';
	if(isset($_POST['event']['categories']) && in_array($key, $_POST['event']['categories'])){
		$checked = 'checked="checked"';
	} elseif(isset($GLOBALS['eventToEdit']['categories']) && in_array($key, $GLOBALS['eventToEdit']['categories'])){
		$checked = 'checked="checked"';
	}
	$html = '<label class="form-control"> '.$value.' <input type="checkbox" name="event[categories][]" value="'.$key.'" '.$checked.' /></label>';
	return $html;
}

// get the existing value for a field if it exists
function get_existing_field($fieldName){
	$value = '';
	if(isset($_POST['event'][$fieldName])){
		$value = $_POST['event'][$fieldName];
	} elseif(!empty($GLOBALS['eventToEdit'])){
		$value = ($fieldName == 'start_date' || $fieldName == 'end_date') ? date('m/d/Y', $GLOBALS['eventToEdit'][$fieldName]->sec) : $GLOBALS['eventToEdit'][$fieldName];
	}
	return $value;
}

function validateId($id){
	$valid = true;
	try {
		$id = new MongoId($id);
	}	catch (MongoException $e) {
		$valid = false;
	}
	
	return $valid;
}

function save($collectionName, $id, $action, $record){
	$success = true;
	
	// update
	if(!empty($id)){
		$resp = MDB::update($collectionName, array('_id'=>$id), array('$set'=>$record));;
	} elseif($action != 'delete') {
	// default
		$resp =  MDB::insert($collectionName, $record);
	}
	
	if($resp['error']){
		$success = false;
	} 
	
	return $success;
}

function getAdminOptions($success, $records, $page){
	$html = '<div>';
	if(!$success){	
		foreach($records as $record){
			if(isset($record['start_date'])){
				$html .= '<p>'. $record['title'].' - '.date('m/d/Y',$record['start_date']->sec).' <a href="'.$page.'?id='.$record['_id'].'"><button class="btn btn-primary">Edit</button></a> <a href="'.$page.'/delete?id='.$record['_id'].'"><button class="btn btn-danger">delete</button></a><br></p>';	
			} else {
				$html .= '<p>'. $record['name'].' <a href="'.$page.'?id='.$record['_id'].'"><button class="btn btn-primary">Edit</button></a> <a href="'.$page.'/delete?id='.$record['_id'].'"><button class="btn btn-danger">delete</button></a><br></p>';		
			}
			
		}
	}
	$html .= '</div>';
	return $html;
}

?>
