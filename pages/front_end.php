<?php 
//connect and require


$categoriesResp = MDB::find('event_categories', array());
$allCategories = $categoriesResp['data']['rows'];
$currentTime = new MongoDate(time());
$categorykeyValues = array();
$generatorCollectionOne = isset($_POST['event']) ? $_POST['event'] : array();
$eventsResp = MDB::find('event_project', array('start_date'=>array('$gt'=>$currentTime), 'status'=>'active'));
$events = $eventsResp['data']['rows'];

// cache category names for generating select
foreach($allCategories as $category){
	$id = (string)$category['_id'];
	$categorykeyValues[$id] = $category['name'];	
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

	<title>Events</title>

</head>

<body>
	<div role="document" class="container-fluid">
<?php print $navbar; ?>
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<h1>
					<form method="POST" action="front_end.php">
						<?php print generate_select($categorykeyValues, 'filter', $generatorCollectionOne); ?>
						<button type="submit" class="btn btn-primary">Filter</button>
					</form>
				</h1>
			</div>
			<div class="col-md-8 col-md-offset-2">
				<h1>Current Events</h1><hr>
<?php 
foreach($events as $event){

if(isset($_POST['event']['filter'])){
	if(!in_array($_POST['event']['filter'], $event['categories'])){
		continue;
	}
}
	$details = !empty($event['details']) ? $event['details'] : 'There are no details for this event';
	$eventCategoryIds = $event['categories'];
	
	$html =  '<h2>'.$event['title'].'</h2>'.
					'<p>Details: '.$event['details'].'</p>'.
					'<p>Start Date: '. date('m/d/Y',$event['start_date']->sec).'</p>'.
					'<p>End Date: '. date('m/d/Y',$event['end_date']->sec).'</p>'.	
					'Categories:<ul>';
	// ouput corresponding categories
	foreach($allCategories as $category){
		if(in_array($category['_id'], $eventCategoryIds)){
			$html.= '<li>'.$category['name'].'</li>';
		}
	}	
	$html .='</ul>';
					
	print $html;
}
?>
			</div>
		</div>
	</div>
	

	<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
	<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>	
</body>
