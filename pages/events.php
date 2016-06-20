<?php
// require and connect

$statuses = array('active','inactive');
$categories_resp = MDB::find('event_categories',array());
$event_categories = $categories_resp['data']['rows'];
$event_category_names = array();
$error_msgs = array();
$required_fields = array('title','status','start_date','end_date','categories');
$valid = true;
$success = false;

function _edit(){
	print '<form method="POST" action="events.php">'.
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

function save($record){
	$resp = MDB::insert('event_project', $record);
	
	if(!$resp['error']){
		$GLOBALS['success'] = true;
	} else {
		$GLOBALS['error_msgs'][] = '<p>Something went wrong when saving, please try again!</p>';	
	}
}

function generate_select( $fields, $inputName, $collection=''){
	$html = '<select class="form-control" name="event['.$inputName.']"><option value="">Select One</option>';
	foreach($fields as $field){
		$selected = (isset($_POST['event'][$inputName]) && ($_POST['event'][$inputName] == $field)) ? $selected = 'selected="selected"': '';
		
		$html .= !empty($collection) ? "<option value='.$collection[$field].' $selected>$field</option>" : "<option value='$field' $selected >$field</option>";
	}
	$html .='</select>';

	return $html;
}

function generate_checkbox($fieldName){
	$checked = (isset($_POST['event']['categories']) && in_array($fieldName, $_POST['event']['categories'])) ? 'checked="checked"' : '';
	$html = '<label class="form-control">'.$fieldName.'<input type="checkbox" name="event[categories][]" value="'.$fieldName.'" '.$checked.' /></label>';
	return $html;
}

// get the existing value for a field if it exists
function get_existing_field($fieldName){
	$value = '';
	if(isset($_POST['event'][$fieldName])){
		$value = $_POST['event'][$fieldName];
	}
	return $value;
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
	} elseif(isset($_POST['event']['title']) && $field == 'title'){
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
	if(!$success){
		_edit();
	} else {
		print '<div class="alert alert-success"><h4>Saved Successfully!</h4></div>';	
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
