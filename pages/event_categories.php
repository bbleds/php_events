<?php 
// require and connect

$category = (isset($_POST['category'])) ? strtolower(htmlspecialchars(trim($_POST['category']))) : '';
$errorMsgs = array();
$path = explode('/', $_SERVER['PHP_SELF']);
$action = $path[count($path)-1];
$id = isset($_GET['id']) ? $_GET['id'] : '';
$categories = MDB::find('event_categories', array());
$valid = true;
$success = false;
$validId= false;

function _edit($id=''){
	$actionUrl = empty($id) ? 'event_categories.php' : 'event_categories.php?id='.$GLOBALS['id'].'';
	$header = !empty($id) ? '<h1>Edit Category</h1><hr>' : '<h1> Add Category </h1>';
	$value = (isset($GLOBALS['record']) && !$GLOBALS['success']) ? $GLOBALS['record']['name'] : '';
	
	print $header.'<div id="error_box">';
	print_r((isset($GLOBALS['errorMsgs']) && !empty($GLOBALS['errorMsgs'])) ? implode('',$GLOBALS['errorMsgs']) : '');
	print '</div>';
	if($GLOBALS['success']){
		print "<p class='alert alert-success'>Updated Successfully!</p>";	
	} else {
	print '<form method="post" action="'.$actionUrl.'">'.
					'<label>Category Name</label>'.
					'<input class="form-control" type="text" name="category" value="'.$value.'"/>'.
					'<button type="submit" class="btn btn-primary">Add</button>'.
				'</form>';
	}
}

function _delete(){
	if($GLOBALS['success']){
		print '<h4 class="alert alert-success">Deleted Category Successfully!</h4>';
	} else {
		print '<form method="post" action="event_categories.php/delete?id='.$GLOBALS['id'].'">'.
						'<h1>Are you sure you want to delete '.$GLOBALS['record']['name'].'?</h1>'.
						'<input type="hidden" name="delete" value="true">'.
						'<button type="submit" class="btn btn-danger">Yes</button>'.
					'</form>';
	}
}


// validate id
if(!empty($id)){
	try {
		$id = new MongoId($_GET['id']);
		$resp = MDB::find('event_categories', array('_id'=>$id));
		if(!$resp['empty']){
			$validId = true;
			$record = $resp['data']['rows'][0];
		}
	}	catch (MongoException $e) {
		die('<p>Invalid Id, please try again!</p><a href="event_categories.php"><button> Try Again</button></a>');
	}	
}

// On post, be sure user provided a category
if(empty($category) && isset($_POST['category']) && $action != 'delete'){
	$valid = false;
	$errorMsgs[] = '<p>Please Enter a Category!</p>';
}

// check db for category name
if(!empty($category) && $action != 'delete'){
	$resp = MDB::find('event_categories', array('name'=>$category));

	if(!$resp['empty']){
		$errorMsgs[] = 'This cateogry already exists, please enter another!';
		$valid = false;		
	}
}

// if valid, perform action on db 
if(($valid && !empty($category))  || ($action == 'delete' && isset($_POST['delete']))){
	// if edit mode, edit record
	if(!empty($id)){
		$resp = MDB::update('event_categories', array('_id'=>$id), array('$set'=>array('name'=>$category)));
	} elseif($action == 'delete'){
		$resp = MDB::delete('event_categories', array('_id'=>$id));
	} else {
		// default
		$resp = MDB::insert('event_categories', array('name'=>$category));	
	}
	
	if($resp['error']){
		$errorMsgs[] = '<p>Could not add category, please try again</p>';
	} else {
		$success = true;	
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

	<title>Add Categories</title>

</head>

<body>
	<div role="document" class="container-fluid">
<?php print $navbar ?>
		<div class="row">
			<div class="col-md-6 col-md-offset-3">
<?php
	foreach($categories['data']['rows'] as $category){
		print $category['name'].' <a href="event_categories.php?id='.$category['_id'].'"><button class="btn btn-primary">Edit</button></a> <a href="event_categories.php/delete?id='.$category['_id'].'"><button class="btn btn-danger">delete</button></a><br>';	
	}
?>

<?php
if($validId && $action != 'delete'){
	_edit($id);
} elseif($validId && $action == 'delete'){
	_delete();
} else {
	_edit();	
}


?>


	<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
	<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>	
</body>
