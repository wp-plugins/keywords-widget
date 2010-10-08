<?php
/*
Plugin Name: Keywords Widget
Plugin URI: http://www.blogseye.com
Description: Widget to display a list of recent search engine query keywords in a link to the wp search function.
Author: Keith P. Graham
Version: 1.1
Author URI: http://www.blogseye.com
Tested up to: 2.9

*/
function widget_kpg_collect_data_kww() {
	$ref='';
	if (array_key_exists('HTTP_REFERER',$_SERVER )) $ref=urldecode($_SERVER['HTTP_REFERER']);
	$q='';
	if ((strpos($ref,'google')>0||strpos($ref,'bing')>0 )&& strpos($ref,'&q=')>0) {
		// search engine using q=
		$q=substr($ref,strpos($ref,'&q=')+3);
		if (strpos($q,'&')>0) {
			$q=substr($q,0,strpos($q,'&'));
		}
	} else if (strpos($ref,'yahoo')>0&&strpos($ref,'&p=')>0) {
		$q=substr($ref,strpos($ref,'&p=')+3);
		if (strpos($q,'&')>0) {
			$q=substr($q,0,strpos($q,'&'));
		}
	} else if (strpos($ref,'yahoo')>0&&strpos($ref,'?p=')>0) {
		$q=substr($ref,strpos($ref,'?p=')+3);
		if (strpos($q,'&')>0) {
			$q=substr($q,0,strpos($q,'&'));
		}
	}
	$q=trim($q);
	if (empty($q)) return;
	// if there is a search from the search engines, then we need to add it to our list
	// q has a legit search in it.
	// get the results of a search based on the parsed entry
	$q=str_replace('_',' ',$q); // underscores should be space
	$q=str_replace('.',' ',$q); // periods should be space 
	$q=str_replace('-',' ',$q); // dashes are wrong
	$q=str_replace('+',' ',$q); // pluses are wrong
	$q=str_replace('"',' ',$q); // quotes are wrong
	$q=str_replace('  ',' ',$q); // double spaces may have crept in
	$q=str_replace('  ',' ',$q); 
	
	$q=trim($q);
	if (empty($q)) return;
	
	// insert the search string into options
// we will keep track of 5 recent searches for now - eventually make it an option
	// let's explode the list
	$qs=explode(' ',$q.' ');
	// get rid of common words - don'd need to search for these:
	$common="z able about act add after again air all also am an and animal answer any are as ask at back bad be been before being between big boy build but by call came can case cause change child city close come company could country cover cross day did differ different do does don't down draw each early earth end even every eye fact far farm father feel few find first follow food for form found four from get give go good government great group grow had hand hard has have he head help her here high him his home hot house how if important in into is it its just keep kind know land large last late learn leave left let life light like line little live long look low made make man many may me mean men might more most mother move mr mrs much must my name near need never new next night no north not now number of off office old on one only or other our out over own page part people person picture place plant play point port press problem public put read real right round run said same saw say school sea see seem self sentence set she should show side small so some sound spell stand start state still story study such sun take tell than that the their them then there these they thing think this thought three through time to too tree try turn two under up upon us use very want was water way we week well went were what when where which while who why will with woman word work world would write year you young your ";
	for ($j=count($qs)-1;$j>=0;$j--) {
		if (empty($qs[$j])||strlen(trim($qs[$j]))<2||strpos($common,' '.$qs[$j].' ')>0) {
			unset($qs[$j]);
		}
	}
	if (count($qs)==0) return;
	// time to find update the historu
	// now let's add these keywords to the list
	$options = (array) get_option('widget_kpg_kww');
	if (empty($options)) $options=array();
	$history=array();
	if (array_key_exists('history',$options)) $history=$options['history'];
	
	for ($j=0;$j<count($qs);$j++) {
		if (array_key_exists($qs[$j],$history)) {
			$history[$qs[$j]]++;
		} else {
			$history[$qs[$j]]=1;
		}
	}
	// now sort the array by the values in the array in descending order
	arsort($history);
	$options['history']=$history;
	update_option('widget_kpg_kww', $options);
		
	return; // done updating history
}
function widget_kpg_kww($args) {
	extract( $args );
	$options = (array) get_option('widget_kpg_kww');
	if (empty($options)) $options=array();
	$history=array();
	if (array_key_exists('history',$options)) $history=$options['history'];
	$title='';
	if (array_key_exists('title',$options)) $title = $options['title'];
	$history=$options['history'];
	$kww_count=30;
	if (array_key_exists('kww_count',$options)) $kww_count=$options['kww_count'];
	if ($kww_count<=0&&$kww_count>50) $kww_count=30;
	$kww_nofollow='Y';
	if (array_key_exists('kww_nofollow',$options)) $kww_nofollow=$options['kww_nofollow'];
	if ($kww_nofollow!='Y') $kww_nofollow='';
	$kww_style='Y';
	if (array_key_exists('kww_style',$options)) 	$kww_style=$options['kww_style'];
	if ($kww_style!='Y') $kww_style='';
	
	echo "\n\n<!-- start Keywords Widget -->\n\n";
	if (count($history)>0) {
		// calculate the total in the widget so we can do an average
		$lim=0;
		$tot=0;
		foreach ($history as $key=>$data) {
			if ($lim>$kww_count) break;
			$tot+=$data;
			$lim++;
		}
		echo $before_widget;
		if ($title!='') echo $before_title . $title . $after_title; 
		// display the recent searches
		echo '<br/>';
		$lim=0;
		foreach ($history as $key=>$data) {
			if ($lim>$kww_count)  break;
			$lim++;
			// link options
			$style="";
			if ($kww_style=='Y') {
				$style='style="';
				$fs=($data/$tot)*256;
				$fs=round(sqrt($fs))+6; 
				if ($fs>16) { $fs=16; }
				$style.='font-size:'.$fs.'pt;';
				if (($data/$tot)>.05) {
					$style.='font-weight:bold;';
				}
				$style.='"';
			}
			
			$nofollow="";
			if ($kww_nofollow=='Y') {
				$nofollow='rel="nofollow"';
			}

		?>
<a  href="<?php echo bloginfo('url'); ?>?s=<?php echo $key; ?>" class="kww_link" <?php echo $style; ?> <?php echo $nofollow; ?>><?php echo $key; ?></a>
<?php
		}
		echo '<br/>';
		echo $after_widget;
	}
	echo "\n\n<!-- End of Keywords Widget -->\n\n";
	return;
}


function widget_kpg_kww_control() {
	$options = (array) get_option('widget_kpg_kww');
	if (empty($options)) $options=array();
	if ( $_POST['kpg_kww_submit'] ) {
		$options['title'] = strip_tags(stripslashes($_POST['kpg_kww_title']));
		$options['kww_count'] = strip_tags(stripslashes($_POST['kpg_kww_count']));
		$options['kww_nofollow'] = strip_tags(stripslashes($_POST['kpg_kww_nofollow']));
		$options['kww_style'] = strip_tags(stripslashes($_POST['kpg_kww_style']));
		update_option('widget_kpg_kww', $options);
	}
	$history=array();
	if (array_key_exists('history',$options)) $history=$options['history'];
	$title='';
	if (array_key_exists('title',$options)) $title = $options['title'];
	$history=$options['history'];
	$kww_count=30;
	if (array_key_exists('kww_count',$options)) $kww_count=$options['kww_count'];
	if ($kww_count<=0&&$kww_count>50) $kww_count=30;
	$kww_nofollow='Y';
	if (array_key_exists('kww_nofollow',$options)) $kww_nofollow=$options['kww_nofollow'];
	if ($kww_nofollow!='Y') $kww_nofollow='';
	$kww_style='Y';
	if (array_key_exists('kww_style',$options)) 	$kww_style=$options['kww_style'];
	if ($kww_style!='Y') $kww_style='';

?>
<div style="text-align:right">
  <label for="kpg_kww_title" style="line-height:25px;display:block;">
  <?php _e('Widget title:', 'widgets'); ?>
  <input style="width: 200px;" type="text" id="kpg_kww_title" name="kpg_kww_title" value="<?php echo ($options['title'] ? wp_specialchars($title, true) : 'Keywords'); ?>" />
  </label>
  <label for="kpg_kww_count" style="line-height:25px;display:block;">
  <?php _e('Keyword Count (max 50):', 'widgets'); ?>
  <input style="width: 200px;" type="text" name="kpg_kww_count" 
						value="<?php echo $kww_count; ?>" />
  </label>
  <label for="kpg_kww_nofollow" style="line-height:25px;display:block;">
  <?php _e('Use NoFollow on links:', 'widgets'); ?>
  <input type="checkbox" name="kpg_kww_nofollow" 
						value="Y" <?php if ($kww_nofollow=='Y'){ echo 'checked'; }?>" />
  </label>
  <label for="kpg_kww_style" style="line-height:25px;display:block;">
  <?php _e('Use size styling for links:', 'widgets'); ?>
  <input type="checkbox" name="kpg_kww_style" 
						value="Y" <?php if ($kww_style=='Y'){ echo 'checked'; }?>" />
  </label>
  <input type="hidden" name="kpg_kww_submit" id="kpg_kww_submit" value="1" />
</div><small>Checking size styling shows some links bigger based on number of hits.</small><br />
<small>Checking nofollow will prevent indexing of search pages.</small><br />
<small>note: the widget will not display on a page until there has actually been a user arriving by a search engine query).</small>
<?php
}

// plugin options page to edit the keyword list

function  widget_kpg_kww_admin() {
	// this is the admin page
	$kww_key=$_POST['kww_key'];
	$options = (array) get_option('widget_kpg_kww');
	if ($options==null) $options=array();
	$history=$options['history'];
	if ($history==null) $history=array();
	if ($kww_key!=null&&$kww_key!='') {
	    if ($kww_key=='*') {
			// kill the history
			$history=array();
		} else if (strlen($kww_key)==1 && is_numeric($kww_key)) {
			// wander through the array
			foreach ($history as $key=>$data) {
				if ($data<=$kww_key) {
					unset($history[$key]);
				}
			}
		} else {
			// the key is to be removed from history
			unset($history[$kww_key]);
		}
		$options['history']=$history;
		update_option('widget_kpg_kww', $options);
	}

?>
<h2>Keyword Maintenance</h2>
<p>This plugin is in active development. I need feedback: "<a href="http://www.blogseye.com/" title="Wordpress plugin: Keywords Widget">program development pages</a>".</p>
<p>Other plugins:<br/>
<a href="http://wordpress.org/extend/plugins/permalink-finder/">Permalink Finder Plugin</a><br/>
<a href="http://wordpress.org/extend/plugins/outbound-click-tracker/">Outbound Link Tracker</a><br/>
<a href="http://wordpress.org/extend/plugins/open-in-new-window-plugin/">Open in New Window Plugin</a><br/>
<a href="http://wordpress.org/extend/plugins/recent-google-searches-widget/stats/">Recent Google Searches Widget</a><br/>
<a href="http://wordpress.org/extend/plugins/looser-search-plugin/">Looser Search</a><br/>
</p>
<p>Below is a list of keywords. You can <a href="#" onclick="kww_deleteItem('*')"><i>clear them all</i></a>, <a href="#" onclick="kww_deleteItem('2')"><i>delete keywords with 2 or less hits</i></a> delete keywords with 2 or less hits, or delete individual keywords from the list.</p>

<?php
	if (count($history)==0) {
		 ?>
<p>There are no keywords in the list, yet.</p>
<?php
		return;
	}
	// create the list
	?>
<table class="form-table">
  <tr>
    <td align="right">Keyword</td>
    <td align="center">Count</td>
    <td align="left">Action</td>
    <?php
	foreach ($history as $key=>$data) {
		?>
  <tr>
    <td align="right"><?php echo $key;?></td>
    <td align="center"><?php echo $data;?></td>
    <td align="left"><a href="#" onclick="kww_deleteItem('<?php echo $key;?>')"><i>delete</i></a></td>
  </tr>
  <?php
	
	}
?>
</table>
<script language="javascript" type="text/javascript">
// this is does the delete
function kww_deleteItem(kw) {
	var doit=document.getElementById('kww_key');
	doit.value=kw;
	document.kww_form.submit();
	return false;
}

</script>
<form name="kww_form" id="kww_form" action="" method="post">
  <input type="hidden" name="kww_key" id="kww_key" value="" />
  <input type="hidden" name="Submit" value="<?php _e('Save Changes') ?>" />
  <input type="hidden" name="action" value="update" />
  <input type="hidden" name="page_options" value="kww_key" />
</form>
<?php

}
function widget_kpg_kww_admin_menu() {
   add_options_page('Keywords Widget', 'Keywords Widget', 'manage_options','keywords_widget','widget_kpg_kww_admin' );
}

function widget_kpg_kww_init() {
	register_sidebar_widget(array('Keywords Widget', 'widgets'), 'widget_kpg_kww');
	register_widget_control(array('Keywords Widget', 'widgets'), 'widget_kpg_kww_control');
}


// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('widgets_init', 'widget_kpg_kww_init');
add_action('init', 'widget_kpg_collect_data_kww');
add_action('admin_menu', 'widget_kpg_kww_admin_menu');

?>
