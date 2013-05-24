<?php
/*
Plugin Name: Vina Categories Treeview Widget
Plugin URI: http://VinaThemes.biz
Description: Great plugin allows display categories of the post in a tree view, like a file explorer.
Version: 1.0
Author: VinaThemes
Author URI: http://VinaThemes.biz
Author email: mr_hiennc@yahoo.com
Demo URI: http://VinaDemo.biz
Forum URI: http://VinaForum.biz
License: GPLv3+
*/

//Defined global variables
if(!defined('VINA_TREEVIEW_DIRECTORY')) 	define('VINA_TREEVIEW_DIRECTORY', dirname(__FILE__));
if(!defined('VINA_TREEVIEW_INC_DIRECTORY')) define('VINA_TREEVIEW_INC_DIRECTORY', VINA_TREEVIEW_DIRECTORY . '/includes');
if(!defined('VINA_TREEVIEW_URI')) 			define('VINA_TREEVIEW_URI', get_bloginfo('url') . '/wp-content/plugins/vina-treeview-widget');
if(!defined('VINA_TREEVIEW_INC_URI')) 		define('VINA_TREEVIEW_INC_URI', VINA_TREEVIEW_URI . '/includes');

//Include library
if(!defined('TCVN_FUNCTIONS')) {
    include_once VINA_TREEVIEW_INC_DIRECTORY . '/functions.php';
    define('TCVN_FUNCTIONS', 1);
}
if(!defined('TCVN_FIELDS')) {
    include_once VINA_TREEVIEW_INC_DIRECTORY . '/fields.php';
    define('TCVN_FIELDS', 1);
}

class TreeView_Widget extends WP_Widget 
{
	function TreeView_Widget()
	{
		$widget_ops = array(
			'classname' => 'treeview_widget',
			'description' => __("Great plugin allows display categories of the post in a tree view, like a file explorer.")
		);
		$this->WP_Widget('treeview_widget', __('Vina Categories Treeview Widget'), $widget_ops);
	}
	
	function form($instance)
	{
		$instance = wp_parse_args( 
			(array) $instance, 
			array( 
				'title' 			=> '',
				'categoryId' 		=> '0',
				'emptyCategory' 	=> 'yes',
				'ItemCounter' 		=> 'no',
				'ordering' 			=> 'id',
				'orderingDirection' => 'desc',
				
				'mStyle' 		=> '',
				'showControl' 	=> 'yes',
				'speed' 		=> 'normal',
				'collapsed' 	=> 'yes',
				'unique' 		=> 'yes',
				'persist' 		=> 'cookie',
			)
		);

		$title				= esc_attr($instance['title']);
		$categoryId			= esc_attr($instance['categoryId']);
		$emptyCategory		= esc_attr($instance['emptyCategory']);
		$ItemCounter		= esc_attr($instance['ItemCounter']);
		$ordering			= esc_attr($instance['ordering']);
		$orderingDirection	= esc_attr($instance['orderingDirection']);
		$mStyle				= esc_attr($instance['mStyle']);
		$showControl		= esc_attr($instance['showControl']);
		$speed				= esc_attr($instance['speed']);
		$collapsed			= esc_attr($instance['collapsed']);
		$unique				= esc_attr($instance['unique']);
		$persist			= esc_attr($instance['persist']);
		?>
        <div id="tcvn-treeview" class="tcvn-plugins-container">
            <div id="tcvn-tabs-container">
                <ul id="tcvn-tabs">
                    <li class="active"><a href="#basic"><?php _e('Basic'); ?></a></li>
                    <li><a href="#display"><?php _e('Display'); ?></a></li>
                </ul>
            </div>
            <div id="tcvn-elements-container">
                <!-- Basic Block -->
                <div id="basic" class="tcvn-telement" style="display: block;">
                    <p><?php echo eTextField($this, 'title', 'Title', $title); ?></p>
                    <p><?php echo eSelectOption($this, 'categoryId', 'Parent Category', buildCategoriesList('Select Parent Categories.'), $categoryId); ?></p>
                    <p><?php echo eSelectOption($this, 'emptyCategory', 'Show Empty Category', array('yes'=>'Yes', 'no'=>'No'), $emptyCategory); ?></p>
                    <p><?php echo eSelectOption($this, 'ItemCounter', 'Show Counter', array('yes'=>'Yes', 'no'=>'No'), $ItemCounter); ?></p>
                    <p><?php echo eSelectOption($this, 'ordering', 'Post Field to Order By', 
						array('id'=>'ID', 'title'=>'Title', 'comment_count'=>'Comment Count', 'post_date'=>'Published Date'), $ordering); ?></p>
                    <p><?php echo eSelectOption($this, 'orderingDirection', 'Ordering Direction', 
						array('asc'=>'Ascending', 'desc'=>'Descending'), $orderingDirection); ?></p>
                </div>
                <!-- Display Block -->
                <div id="display" class="tcvn-telement">
                	<p><?php echo eSelectOption($this, 'mStyle', 'Widget Style', 
						array(''=>'Default', 'filetree'=>'File Tree', 'treeview-red'=>'Tree View Red'), $mStyle); ?></p>
                   	<p><?php echo eSelectOption($this, 'showControl', 'Show Control', array('yes'=>'Yes', 'no'=>'No'), $showControl); ?></p>
                    <p><?php echo eSelectOption($this, 'speed', 'Animated Speed', 
						array('slow'=>'Slow', 'normal'=>'Normal', 'fast'=>'Fast'), $speed); ?></p>
                    <p><?php echo eSelectOption($this, 'collapsed', 'Collapsed', array('yes'=>'Yes', 'no'=>'No'), $collapsed); ?></p>
                    <p><?php echo eSelectOption($this, 'unique', 'Unique', array('yes'=>'Yes', 'no'=>'No'), $unique); ?></p>
                    <p><?php echo eSelectOption($this, 'persist', 'Persist', array('location'=>'Location', 'cookie'=>'Cookie'), $unique); ?></p>
                </div>
            </div>
        </div>
		<script>
			jQuery(document).ready(function($){
				var prefix = '#tcvn-treeview ';
				$(prefix + "li").click(function() {
					$(prefix + "li").removeClass('active');
					$(this).addClass("active");
					$(prefix + ".tcvn-telement").hide();
					
					var selectedTab = $(this).find("a").attr("href");
					$(prefix + selectedTab).show();
					
					return false;
				});
			});
        </script>
		<?php
	}
	
	function update($new_instance, $old_instance) 
	{
		return $new_instance;
	}
	
	function getChildCategories($instance, $cid = 0, $level = 0, $begin = false)
	{
		static $output;
		if($begin) $output = '';
		
		$emptyCategory	= getConfigValue($instance, 'emptyCategory','yes');
		$ItemCounter	= getConfigValue($instance, 'ItemCounter',	'yes');
		$mStyle			= getConfigValue($instance, 'mStyle',		'');
		
		$ordering			= getConfigValue($instance, 'ordering',	'id');
		$orderingDirection	= getConfigValue($instance, 'orderingDirection',	'desc');
				
		$args = array(
			'parent'        => $cid,
			'orderby'       => $ordering,
			'order'         => $orderingDirection,
			'hide_empty'    => ($emptyCategory == 'yes') ? '0' : 1,
			'pad_counts'    => ($ItemCounter == 'yes') ? true : false,
		);
		
		$categories = get_categories($args);
		
		$output .= ($level) ? '<ul class="sub-menu level'.$level.'">' : '<ul id="vina-categories-treeview" class="level'.$level.' '.$mStyle.'">';
		
		foreach($categories as $row) {
			$numOfPost = ($ItemCounter == 'yes') ? " ({$row->count})" : '';
			
			if(count(get_categories(array('parent' => $row->term_id)))) {
				$output .= '<li><a href="' . get_category_link($row->term_id) . 
				'" title="' . sprintf(__("View all posts in %s"), $row->name ) . '" ' . '>' . 
				'<span class="catTitle'.(($mStyle == 'filetree') ? ' folder' : '').'">' .$row->name . $numOfPost . '</span></a>';
				$this->getChildCategories($instance, $row->term_id, $level + 1, false);
				$output .= '</li>';
			} else {
				$output .= '<li><a href="' . get_category_link($row->term_id) . 
				'" title="' . sprintf(__("View all posts in %s"), $row->name ) . '" ' . '>' . 
				'<span class="catTitle'.(($mStyle == 'filetree') ? ' file' : '').'">' .$row->name . $numOfPost . '</span></a></li>';
			}
		}
		
		$output .= '</ul>';
		
		return $output;
	}
	
	function widget($args, $instance) 
	{
		extract($args);
		
		$title 		 = getConfigValue($instance, 'title', '');
		$categoryId	 = getConfigValue($instance, 'categoryId',	'0');
		$showControl = getConfigValue($instance, 'showControl',	'yes');
		$speed		 = getConfigValue($instance, 'speed',		'normal');
		$collapsed	 = getConfigValue($instance, 'collapsed',	'yes');
		$unique		 = getConfigValue($instance, 'unique',	'yes');
		$persist	 = getConfigValue($instance, 'persist',	'cookie');
		
		$output 	 = $this->getChildCategories($instance, $categoryId, 0, true);
		
		echo $before_widget;
		
		if($title) echo $before_title . $title . $after_title;
		
		if(!empty($output)) :
		?>
        <div class="vina-categories-treeview">
        	<?php if($showControl == 'yes') : ?>
            <div id="vina-categories-treecontrol" class="treecontrol">
                <a href="#" title="<?php _e('Collapse the entire tree below'); ?>"><?php _e('Collapse All'); ?></a> | 
                <a href="#" title="<?php _e('Expand the entire tree below'); ?>"><?php _e('Expand All'); ?></a> | 
                <a href="#" title="<?php _e('Toggle the tree below, opening closed branches, closing open branches'); ?>"><?php echo _e('Toggle All'); ?></a>
            </div>
            <?php endif; ?>
            <?php echo $output; ?>
        </div>
        <div id="tcvn-copyright">
        	<a href="http://vinathemes.biz" title="Free download Wordpress Themes, Wordpress Plugins - VinaThemes.biz">Free download Wordpress Themes, Wordpress Plugins - VinaThemes.biz</a>
        </div>
        <script type="text/javascript">
			jQuery(document).ready(function($){
				$("#vina-categories-treeview").treeview({
					animated: 	"<?php echo $speed; ?>",
					persist: 	"<?php echo $persist; ?>",
					collapsed: 	<?php echo ($collapsed == 'yes') ? "true" : "false"; ?>,
					unique:		<?php echo ($unique == 'yes') ? "true" : "false"; ?>,
					<?php if($showControl == 'yes') { ?>
					control: "#vina-categories-treecontrol",
					<?php } ?>
				});
			});
		</script>
		<?php
		endif;
		
		echo $after_widget;
	}
}

add_action('widgets_init', create_function('', 'return register_widget("TreeView_Widget");'));
wp_enqueue_style('vina-admin-css', VINA_TREEVIEW_INC_URI . '/admin/css/style.css', '', '1.0', 'screen');
wp_enqueue_script('vina-tooltips', VINA_TREEVIEW_INC_URI . '/admin/js/jquery.simpletip-1.3.1.js', 'jquery', '1.0', true);

wp_enqueue_style('vina-post-treeview-css', 	VINA_TREEVIEW_INC_URI . '/css/jquery.treeview.css', '', '1.0', 'screen');
wp_enqueue_script('vina-cookie', 			VINA_TREEVIEW_INC_URI . '/js/jquery.cookie.js', 'jquery', '1.0', true);
wp_enqueue_script('vina-treeview', 			VINA_TREEVIEW_INC_URI . '/js/jquery.treeview.js', 'jquery', '1.0', true);
?>