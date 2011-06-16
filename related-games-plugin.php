<?php
/*
Plugin Name: Related Games
Plugin URI: http://www.games.com/
Description: A customizable widget which displays the latest related Games Tag/Category Based from http://www.games.com/.
Version: 1.0
Author: AOL Games
Author URI: http://www.games.com/
License: GPL3
Developer: Dhanu Gupta
*/
require_once("XMLParser.php");

function relatedgames()
{
  $options = get_option("widget_relatedgames");
  if (!is_array($options)){
    $options = array(
      'title' => 'Games News',
      'news' => '3',
      'chars' => '30'
    );
  }
  
  global $post; //Define the global post object to access the post tags and categories
  
  if($taxonomy == '') { $taxonomy = 'post_tag'; }
	
	$tags = wp_get_post_terms($post->ID, $taxonomy);
	$category = wp_get_post_terms($post->ID, 'category');
	if($category){$first_category = $category[0]->slug;}
	if($tags) { $first_tag 	= $tags[0]->slug; }

    $query_term = "";
    if(strlen($first_tag) >0) { $query_term = $first_tag;} else { $query_term =$first_category;}
  
  //Feed URL
 	$url = "http://www.games.com/feeds/gamesBrowse/?q=".$query_term;
	$xml = file_get_contents($url);
	//Set up the parser object
	$parser = new xmlToArrayParser($xml);
	$domArr = $parser->array; 
     
    if(count($domArr['rss']['channel']['item']) == 7) {
    $url = "http://www.games.com/feeds/gamesBrowse/?q=".$query_term."+games";
	$xml = file_get_contents($url);
	//Set up the parser object
	$parser = new xmlToArrayParser($xml);
	$domArr = $parser->array;
    }
    
    if(count($domArr['rss']['channel']['item']) ==0) {
    $url = "http://www.games.com/feeds/gamesBrowse/";
	$xml = file_get_contents($url);
	//Set up the parser object
	$parser = new xmlToArrayParser($xml);
	$domArr = $parser->array;
    }
   
    //Stylesheet
    echo "<link rel='stylesheet' type='text/css' href='wp-content/plugins/related-games/style.css' />";
	
	if(count($domArr) >0) {
 		echo '<ul>';
		$itemarr = $domArr['rss']['channel']['item'];
		
			for ($i=0;$i<$options['news'];$i++){
				$titl= $domArr['rss']['channel']['item'][$i]['title'];
				$desc= $domArr['rss']['channel']['item'][$i]['description'];
				$link= $domArr['rss']['channel']['item'][$i]['link'];
				$desc = str_replace("<br/>","",$desc);
				$desc = str_replace("Play Free Online","",$desc);
				echo '<li><a href="'.$link.'" title="Play '.$titl.' Game">'.$desc.'</a></li>';
			}
			//echo '<p class="sitelink">powered by <a href="http://games.com" title="AOL Games"><img src="http://o.aolcdn.com/os/games/images/logos/gamesdotcom" alt="games.com"/></a></p>'; 
			echo '</ul>';
		}
	}

function widget_relatedgames($args)
{
  extract($args);
  
  $options = get_option("widget_relatedgames");
  if (!is_array($options)){
    $options = array(
      'title' => 'Related Games',
      'news' => '3',
      'chars' => '30'
    );
  }
  
  echo $before_widget;
  echo $before_title;
  echo $options['title'];
  echo $after_title;
  relatedgames();
  echo $after_widget;
}

function relatedgames_control()
{
  $options = get_option("widget_relatedgames");
  if (!is_array($options)){
    $options = array(
      'title' => 'Related Games',
      'news' => '5',
      'chars' => '30'
    );
  }
  
  if($_POST['relatedgames-Submit'])
  {
    $options['title'] = htmlspecialchars($_POST['relatedgames-WidgetTitle']);
    $options['news'] = htmlspecialchars($_POST['relatedgames-NewsCount']);
    $options['chars'] = htmlspecialchars($_POST['relatedgames-CharCount']);
    update_option("widget_relatedgames", $options);
  }
?> 
  <p>
    <label for="relatedgames-WidgetTitle">Widget Title: </label>
    <input type="text" id="relatedgames-WidgetTitle" name="relatedgames-WidgetTitle" value="<?php echo $options['title'];?>" />
    <br /><br />
    <label for="relatedgames-NewsCount">Max. News: </label>
    <input type="text" id="relatedgames-NewsCount" name="relatedgames-NewsCount" value="<?php echo $options['news'];?>" />
    <br /><br />
    <label for="relatedgames-CharCount">Max. Characters: </label>
    <input type="text" id="relatedgames-CharCount" name="relatedgames-CharCount" value="<?php echo $options['chars'];?>" />
    <br /><br />
    <input type="hidden" id="relatedgames-Submit"  name="relatedgames-Submit" value="1" />
  </p>
  
<?php
}

function relatedgames_init()
{
  register_sidebar_widget(__('Related Games'), 'widget_relatedgames');    
  register_widget_control('Related Games', 'relatedgames_control', 300, 200);
}
//plugin loaded
add_action("plugins_loaded", "relatedgames_init");
?>