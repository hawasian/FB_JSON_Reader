<?php
	$page_id = 'PAGE ID';
	$access_token = 'TOKEN';
	//Get the JSON
	$json_object = @file_get_contents('https://graph.facebook.com/'.$page_id.'/posts?access_token='.$access_token);
	//Interpret data
	$fbdata = json_decode($json_object);
	$posts = array();
	$n = 0;
	foreach ($fbdata->data as $post )
	{	$type = $post->type;
		if($type != 'status' && $type != 'photo' && $type != 'link' && $type != 'event' && $type != 'video'){
			continue;	
		}
		$id = $post->id;
		//Date Processing 
		$date_raw = $post->created_time; //2015-06-10T03:02:42+0000 June 9th @ 11:02pm
		$minute = $date_raw{14}.$date_raw{15};
		$hour_raw = $date_raw{11}.$date_raw{12};
		$day_adj = 0;
		if($hour_raw<4){
			$hour_raw+=24;
			$day_adj = -1;	
		}
		$hour = $hour_raw -4;
		$ampam='am';
		if($hour >= 12){
			$hour -= 12;
			$ampam = 'pm';
		}
		if($hour==0){$hour = 12;}
		$year = $date_raw{0}.$date_raw{1}.$date_raw{2}.$date_raw{3};
		$month = $date_raw{5}.$date_raw{6};
		$day = $date_raw{8}.$date_raw{9};
		$day += $day_adj;
		if($day<=0){
			$month -= 1;
			if($month <= 0){$year -=1; $month = 12;}
			if($month == 2 && $year%4!=0){$day = 28;}
			else if($month == 2 && $year%4==0){$day = 29;}
			else if($month == 4 || $month == 6 || $month == 9 || $month == 11){$day = 30;}
			else {$day = 31;}
		}
		$date = $month."/".$day."/".$year." ".$hour.":".$minute . $ampam;
		//Heading Processing
		$heading = "";
		if($type == 'status'){ $heading = 'Status Update:';}
		else{ $heading = 'Check out this '. ucfirst($type).':';}
		//Content Processing
		$content="";
		$content =$post->message;
		$clist = explode(' ',$content);
		for ($i=0; $i<sizeof($clist); $i+=1){
			if(strpos($clist[$i],'http')!==false){
				$clist[$i] = '<a href ="'.$clist[$i].'" class = "newslink">'.$clist[$i].'</a>';
			}
		}
		for ($i=0; $i<sizeof($clist); $i+=1){
			if(strpos($clist[$i],'#')!==false){
				$clist[$i] = '<a href ="https://www.facebook.com/hashtag/'.str_replace('#','',$clist[$i]).'" class = "news_hashtag">'.$clist[$i].'</a>';
			}
		}
		$content = implode(" ",$clist);
		$content = '<div class = "ncont">'.$content."</div>";
		$image='';
		if($type =='photo' || $type == 'video'){
			$image .= "<img src='".$post->picture."'/>";
			$image = '<div class = "nimage"><a href ="'.$post->link.'">'.$image.'</a></div';
		}
		$posts[$n] = '<div id = "'.$id.'" class = npost>'."\n";
		$posts[$n].= '<h1>'.$heading.'</h1>'."\n";
		$posts[$n].= '<h2>'. $date .' EST</h2>'."\n";
		$posts[$n].= $content."\n";
		$posts[$n].= $image."\n";
		$posts[$n].= '</div>'."\n";
		$n += 1;

	}
	
	//Display the posts
	echo"\n";
	echo "<ul id='nlist'>"."\n";
	foreach($posts as $x){
		echo "<li>".$x."</li>"."\n";	
	}
	echo "</ul>";
?>