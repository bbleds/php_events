<?php

/**
 * generate_select()
 *
 * Returns a select element with specified option elements
 *
 * @access public
 *
 * @param array $fields
 * @param string $inputName
 * @param array $collectionOne
 * @param array $collectionTwo
 *
 * @return $html
 */
function generate_select( $fields, $inputName, $collectionOne=array(), $collectionTwo=array()){
	$html = '<select class="form-control" name="event['.$inputName.']"><option value="">Select One</option>';
	foreach($fields as $k=>$v){
		$selected = '';
		if(isset($collectionOne[$inputName]) && $collectionOne[$inputName] == $k){
			$selected = 'selected="selected"';
		} elseif(isset($collectionTwo[$inputName]) && $collectionTwo[$inputName] == $k){
			$selected = 'selected="selected"';
		}
		$html .= "<option value='$k' $selected >$v</option>";
	}
	$html .='</select>';

	return $html;
}

/**
 * generate_checkbox()
 *
 * Returns a checkbox input with a specified value attribute 
 *
 * @access public
 *
 * @param string $key
 * @param string $value
 *
 * @return $html
 */
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

/**
 * get_existing_field()
 *
 * Returns the existing value for a field if it exists
 *
 * @access public
 *
 * @param string $fieldName
 *
 * @return $value
 */
function get_existing_field($fieldName){
	$value = '';
	if(isset($_POST['event'][$fieldName])){
		$value = $_POST['event'][$fieldName];
	} elseif(!empty($GLOBALS['eventToEdit'])){
		$value = ($fieldName == 'start_date' || $fieldName == 'end_date') ? date('m/d/Y', $GLOBALS['eventToEdit'][$fieldName]->sec) : $GLOBALS['eventToEdit'][$fieldName];
	}
	return $value;
}


/**
 * validateId()
 *
 * Returns conditional boolean based on valid or invalid mongo id
 *
 * @access public
 *
 * @param string $id
 *
 * @return $valid
 */
function validateId($id){
	$valid = true;
	try {
		$id = new MongoId($id);
	}	catch (MongoException $e) {
		$valid = false;
	}
	
	return $valid;
}

/**
 * save()
 *
 * Saves or updates a saved record in the db
 *
 * @access public
 *
 * @param string $collectionName
 * @param obj $id
 * @param string $action
 * @param array $record
 *
 * @return $success
 */
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

/**
 * get_admin_options()
 *
 * Returns an html element containing edit and delete buttons for admin functionality
 *
 * @access public
 *
 * @param bool $success
 * @param array $records
 * @param string $page
 *
 * @return $html
 */
function get_admin_options($success, $records, $page){
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
