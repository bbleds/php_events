<?php
// connect and require

$statuses = array('active','inactive');
$event_category_names = array();
$error_msgs = array();
$path = explode('/', $_SERVER['PHP_SELF']);
$action = $path[count($path)-1];
$required_fields = array('title','status','start_date','end_date','categories');
$categories_resp = MDB::find('event_categories',array());
$event_categories = $categories_resp['data']['rows'];
$id = (isset($_GET['id'])) ? $_GET['id'] : '';
$eventToEdit = '';
$valid = true;
$success = false;

function _edit(){
	if($GLOBALS['success'] == true){
		print '<div class="alert alert-success"><h4>Saved Successfully!</h4></div>';	
	} else {
		$submitTo = !empty($GLOBALS['id']) ? 'events.php?id='.$GLOBALS['id'] : 'events.php';
		print '<form method="POST" action="'.$submitTo.'">'.
					'<label>Title</label>'.
					'<input type="text" class="form-control" name="event[title]" value="'.get_existing_field('title').'" />'.
					'<label>Details</label>'.
					'<textarea class="form-control" name="event[details]">'.get_existing_field('details').'</textarea>'.	
					'<label>Status</label>'.
					generate_select($GLOBALS['statuses'],'status').	
					'<label>Start Date</label>'.
					'<input id="start_date" class="form-control" type="text" name="event[start_date]" value="'.get_existing_field('start_date').'" />'.
					'<label>End Date</label>'.
					'<input id="end_date" class="form-control" type="text" name="event[end_date]" value="'.get_existing_field('end_date').'" />'.
					'<label>Event Category</label>';
		foreach($GLOBALS['event_category_names'] as $category){
			print generate_checkbox($category);
		}	
		print	'<button type="submit" class="btn btn-primary">Submit</button>'.
			'</form>';
	}
}

function _delete(){
	if($GLOBALS['success']){
		print '<div class="alert alert-success">Removed Record Successfully</div>';
	} else {
		print '<form method="POST" action="events.php/delete?id='.$GLOBALS['id'].'">'.
					'<h3>Are you sure you want to delete '.$GLOBALS['eventToEdit']['title'].'?</h3>'.
					'<input type="hidden" name="delete" value="1"/>'.
					'<button type="submit" class="btn btn-danger">Yes</button> '.
				'</form>';
		print '<button class="btn btn-primary">Cancel</button>';
	}
}

function save($record){
	// update
	if(!empty($GLOBALS['id'])){
		$resp = MDB::update('event_project', array('_id'=>$GLOBALS['mongoId']), array('$set'=>$record));
	} else {
		// default
		$resp = MDB::insert('event_project', $record);
	}
	
	if(!$resp['error']){
		$GLOBALS['success'] = true;
	} else {
		$GLOBALS['error_msgs'][] = '<p>Something went wrong when saving, please try again!</p>';	
	}
}

function generate_select( $fields, $inputName, $collection=''){
	$html = '<select class="form-control" name="event['.$inputName.']"><option value="">Select One</option>';
	$selected = '';
	foreach($fields as $field){
		if(isset($_POST['event'][$inputName]) && $_POST['event'][$inputName] == $field){
			$selected = 'selected="selected"';
		} elseif(isset($GLOBALS['eventToEdit'][$inputName]) && $GLOBALS['eventToEdit'][$inputName] == $field){
			$selected = 'selected="selected"';
		}
		$html .= !empty($collection) ? "<option value='.$collection[$field].' $selected>$field</option>" : "<option value='$field' $selected >$field</option>";
	}
	$html .='</select>';

	return $html;
}

function generate_checkbox($fieldName){
	$checked = (isset($_POST['event']['categories']) && in_array($fieldName, $_POST['event']['categories'])) ? 'checked="checked"' : '';
	if(isset($_POST['event']['categories']) && in_array($fieldName, $_POST['event']['categories'])){
		$checked = 'checked="checked"';
	} elseif(isset($GLOBALS['eventToEdit']['categories']) && in_array($fieldName, $GLOBALS['eventToEdit']['categories'])){
		$checked = 'checked="checked"';
	}
	$html = '<label class="form-control"> '.$fieldName.' <input type="checkbox" name="event[categories][]" value="'.$fieldName.'" '.$checked.' /></label>';
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

// validate id
if(!empty($id)){
	try {
		$mongoId = new MongoId($id);
		$resp = MDB::find('event_project', array('_id'=>$mongoId));
		if(!$resp['empty']){
			$validId = true;
			$eventToEdit = $resp['data']['rows'][0];
		}
	}	catch (MongoException $e) {
		die('<p>Invalid Id, please try again!</p><a href="events.php"><button> Try Again</button></a>');
	}	
}

if(isset($_POST['delete'])){
	$resp = MDB::delete('event_project', array('_id'=>$mongoId));
	if(!$resp['error']){
		$success = true;
	} else {
		$error_msgs[] = '<p>There was a problem deleting that record, please try again!</p>';	
	}
}

// store all event category names into $event_category_names array for checkbox output
foreach($event_categories as $category){
	$event_category_names[] = $category['name'];
}

// validate required fields
foreach($required_fields as $field){
	if(isset($_POST['event']) && (!isset($_POST['event'][$field]) || empty($_POST['event'][$field]))){
		$error_msgs[] = "<p>Please enter $field!</p>";	
		$valid = false;	
	} elseif(isset($_POST['event']['title']) && $field == 'title' && empty($id)){
		$resp = MDB::find('event_project', array('title'=>$_POST['event']['title']));
		if(!$resp['empty']){
			$error_msgs[] = '<p> An event already exists with this title, please enter a different title!</p>';
			$valid = false;
		}
	}
}

// format data
if ($valid && isset($_POST['event'])){
	
	$newRecord = $_POST['event'];
	foreach($newRecord as $key=>$value){
		if(!is_array($value)){
			$newRecord[$key] = strtolower(htmlspecialchars(trim($value)));
		}
	}
	$newRecord['start_date'] = new MongoDate(strtotime($newRecord['start_date']));
	$newRecord['end_date'] = new MongoDate(strtotime($newRecord['end_date']));
	
	// save data
	if (isset($_POST['event']) && $valid){
		save($newRecord);
	}	
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="keywords" content="ben" />
	<meta name="description" content="Ben." />
	<meta name="author" content="Ben" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no" />

	<link type="text/css" rel="stylesheet" href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css'/>
	<link type="text/css" rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery.ui.all.css"/>

	<title>Events</title>

</head>

<body>
	<div role="document" class="container-fluid">
	<?php print $navbar; ?>
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<h1>Events</h1>
			<div id="error_box">
<?php 
if(!empty($error_msgs)){
	print implode('',$error_msgs);
}
?>
			</div>
		</div>
		<div class="col-md-8 col-md-offset-2">
<?php 
	if($action == 'delete' && !empty($id)){
		_delete();
	} else {
		_edit();
	}
?>
		</div>
	</div>
	</div>


	<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
	<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script type="text/javascript"  src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
	<script type="text/javascript">
	$("#start_date").datepicker();
	$("#end_date").datepicker();
	</script>	
</body>
