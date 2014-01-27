<?php
/** 
 * FACEBOOK LIKES IN LEARNINGSTUDIO
 *
 * @author    Jeof Oyster <jeof.oyster@pearson.com>
 * @partner   Philippos Savvides <savvides@asu.edu>
 * @copyright 2014 Pearson 
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version   1.0
 * @date      2013-11-11
 * 
 * Please refer to the License file provided with this sample application 
 * for the full terms and conditions, attributions, copyrights and license details. 
 */
 
/**
 * TRACKER PIXEL PAGE 
 * This is a sample script that you can use to receive and parse the information from 
 * LearningStudio, use APIs to pull back information about the course, and then 
 * evaluate the course information to determine where the student is in the course. 
 *
 * You will need to add your own logic for storing this information. 
 * 
 * Note this script creates a number of helpful variables for you - read the comments
 * at the bottom for more detail. 
 * 
 * The script finishes by outputting a blank pixel image, since this script is invoked 
 * via an <img> tag.
 *
 */
 
// Including some functions for making OAuth 1 calls. 
include('libraries/functions.php'); 

// Gather the parameters sent to this page when the Like button was clicked. 
$Info = json_decode(rc4decrypt((((isset($_SERVER["HTTPS"]) && !empty($_SERVER["HTTPS"]))?'https://':'http://').$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]),base64_decode(rawurldecode(urlencode($_REQUEST['info']))))); 
$course_id  = $Info->course;
$user_id    = $Info->user;
$content_id = $Info->content_item;

// These are your institution's API Keys. They can be requested from your 
// client services consultant at Pearson. Keep these secure and safe. 
$oauth_application_id    = ''; 
$oauth_token_key_moniker = ''; 
$oauth_secret            = ''; 

// Make a few API calls. Note this is a streamlined implementation of calling 
// APIs for this example application. 
$user_info = doLearningStudioGET('/users/'.$user_id); 
$course_info = doLearningStudioGET('/courses/'.$course_id); 

// Extract some information about the course and user from the APIs.
$course_title = $course_info->courses[0]->title; 
$user_firstname = $user_info->users[0]->firstName; 
$user_lastname = $user_info->users[0]->lastName; 


// This call will give you a flattened list of the content items of the course, in order 
// (Unit pages get their own item before all the unit's child content items). 
$get_course_item_ids_in_order = doLearningStudioGET('/courses/'.$course_id.'/items'); 

// We will cycle through the course items to create an easier-to-use PHP array of the 
// Item IDs, and correlate those item IDs to the names of the content items and any relevant 
// parent item information. 
$course_item_ids_in_order = array();
foreach($get_course_item_ids_in_order->items as $item){ 
    $course_item_ids_in_order[] = $item->id; 

	if(substr($item->id,0,1)==1){ 
		foreach($item->links as $link){ 
			if(isset($link->title) && $link->title=='parent'){ 
				$exploded_parent_link = explode('/',$link->href); 
				$parent_id = $exploded_parent_link[count($exploded_parent_link)-2]; 
			}
		} 
	} else { 
		$parent_id = null; 
	} 

    $course_item_info[$item->id] = array('title'=>$item->title,'parent_id'=>$parent_id); 
	if(!empty($parent_id)) $course_item_info[$item->id]['parent_title']=$course_item_info[$parent_id]['title']; 

} 

// Using that ordered array of the content, we can sort out where the user currently is in the course.
$current_location_index = array_search($content_id,$course_item_ids_in_order); 

// This tells us how far in the course they are (i.e. the current page is 60% through the course)
$percent_complete = round((($current_location_index+1)/count($course_item_ids_in_order))*100); 


// This gives us the name of the content item they are current viewing
$current_page_name = $course_item_info[$content_id]['title']; 


// This gives us a hierarchical representation of the Unit > Content Item. 
// So if it's a content item, this will be something like  "Week 3 > Video" 
// But if it's a unit item, it will be something like "Week 3 Introduction"
$current_location_name = (empty($course_item_info[$content_id]['parent_id']))?
								$current_page_name.' Introduction' :
								$course_item_info[$content_id]['parent_title'].' > '.$current_page_name;


// This determines the next item in the course, based on the structure
$next_item_id = (($current_location_index+1)>=count($course_item_ids_in_order))?null:$course_item_ids_in_order[($current_location_index+1)]; 
$next_item_page_name = (is_null($next_item_id))?null:$course_item_info[$next_item_id]['title']; 
$next_item_location_name = (empty($course_item_info[$next_item_id]['parent_id']))?
								$next_item_page_name.' Introduction' :
								$course_item_info[$next_item_id]['parent_title'].' > '.$next_item_page_name;



// So at this point you have a number of variables you can use to determine the structure of the course and the 
// student's location in the course. For starters, you have the $content_item_info array, which if you used print_r to view,
// would look like the following snippet. Note that this array is IN THE ORDER OF THE COURSE. If the item is a Unit Header
// (i.e. Unit 1 or Week 1), then there is no Parent ID and no Parent Title. If the unit is a content item, you can see which 
// other content item is its parent. This is how we come up with the later variables.
// You could also use this array to create an index in your database of all the content items in the course. 
// Then you can mark which of the content items the student has been on the first time they appear on it. 
// This would lead to your own custom "percentage complete" logic. 
/*
Array
(
    [20050402541] => Array
        (
            [title] => Course Home
            [parent_id] => 
        )

    [100379197160] => Array
        (
            [title] => Syllabus
            [parent_id] => 20050402541
            [parent_title] => Course Home
        )
	...
)
*/

// The script above also created a number of other variables you can use. These variables do a lot of the parsing work 
// for you, interpreting $content_item_info for you. 

// $content_id = 100379197181
// This is the ID of the current content item. You can use this to match up to the content map, track their location, etc. 

// $current_location_index = 27
// You probably won't use this one as much, but what it is is the index of the current content item in a flattened
// list of content item IDs. We use it to determine the percentage complete of the course. The value is just a number of 
// the index in the $course_item_ids_in_order array above. 

// $percent_complete = 38
// This is the whole number percentage of how far the student is through the course. In this example, it's 38% complete. 
// Note that this only shows where the current content item appears in the course; it doesn't reflect any future content
// the student has already been to. To create a more intelligent percentage-complete index, you should come up with a way
// for your system to record whenever a student first visits the page, and then calculate the remaining pages in the class. 

// $current_page_name = "Discussion"; 
// This is the name of the content item the student is on. It is what is shown in the left menu of LearningStudio. 
// Note that if you have a lot of content items with the same name, for example "Discussion," this doesn't inherently give 
// you a lot of information about where they are in the course. 

// $current_location_name = "Week 2 > Discussion"
// This is the name of the unit title along with the name of the content item. This gives you a little more context 
// for where the student is in the course, at least as far as printing that information to the screen or log. You should use 
// item IDs for any real tracking purposes. If the current content item is a Unit page, then this will say something like 
// "Week 2 Introduciton"

// $next_item_id = 100379197183
// This is the content ID of the next item in the course, if you were to flatten the hiearchy to a single list (which is
// how this script views the course. 

// $next_item_page_name = "Quiz" 
// $next_item_location_name = "Week 2 > Quiz" 
// Just like the Current page variables above, these give you the content item name and the Unit + Content Item name. 



/********************************************************
 * ADD YOUR CUSTOM LOGIC HERE 
 * Okay, now below, you should add your custom logic for writing information to the database
 * or otherwise doing something that you need this information for. 
 ********************************************************/







/********************************************************/


// This code just renders a transparent pixel PNG image to close the loop. 
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.
header('Content-Type: image/png');
echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');