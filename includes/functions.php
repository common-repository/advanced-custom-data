<?php
/**
 * Advanced Custom Data
 */
defined('ABSPATH') or die();

/*
 * Since 1.0.0
 * 
 * params @string : path
 * 
 * return @string : url
 */
function acd_url($path = '')
{
	return plugins_url($path, acd_plugin_index());
}

/*
 * Since 1.0.0
 * 
 * params @string : path
 * 
 * return @string : path
 */
function acd_path($path = '')
{
	return dirname(acd_plugin_index()) . (substr($path, 0, 1) !== '/' ? '/' : '') . $path;
}

/*
 * Since 1.0.0
 * 
 * params @string : path
 * params @array : query
 * 
 * return @string : url
 */
function acd_pbone_url($path = '', $query = [])
{
	$path = wp_normalize_path($path);

	$url = 'https://photoboxone.com';

	if ($path && is_string($path)) {
		$url .= '/' . ltrim($path, '/');
	}

	return esc_url(add_query_arg($query, $url));
}

/*
 * Since 1.0.0
 * 
 * Updated 1.0.11
 * 
 * params @array : atts
 * 
 * return @string : content
 */
function acd_data_do_shortcode($atts = array())
{
	extract(shortcode_atts(array(
		'id' 	=> 0,
		'name' 	=> '',
		'type' 	=> '',
		'size' 	=> 'full'
	), $atts));

	$content = '';

	if ($name != '' && $id == 0) {
		$id = acd_data_get_id_by_name($name, $id);
	} else {
		$id = (int) $id;
	}

	if ($id > 0) {
		if ($type == '') {
			$type = sanitize_text_field(get_post_meta($id, '_acd_type', true));
		}

		$acd_data = acd_data_get_content($id, ($type == 'script' ? 'content' : 'textarea'));

		if ($type == 'list') {
			$list = array();
			foreach (explode("\n", $acd_data) as $value) {
				$list[] = '<li>' . sanitize_text_field($value) . '</li>';
			}
			$content = "<ul>" . implode($list) . '</ul>';
		} else if ($type == 'thumbnail') {
			if ($type == 'url') {
				$content = get_the_post_thumbnail_url($id, $size);
			} else {
				$content = get_the_post_thumbnail($id, $size);
			}
		} else if ($type == 'br') {
			$content = nl2br($acd_data);
		} else {
			$content = $acd_data;
		}
	}

	return $content;
}
add_shortcode('acd-data', 'acd_data_do_shortcode');

/*
 * Since 1.0.3
 * 
 * params @string : data
 * params @array : tags_more
 * 
 * return @string : data
 */
function acd_data_strip_tags($data = '', $tags_more = array())
{
	$html_tags = add_filter('acd_data_html_tags', 'ul,ol,li,span,strong,em,p,b,i,u');

	$allowed_tags = explode(',', $html_tags);

	if (count($tags_more)>0) {
		$allowed_tags = array_merge($allowed_tags, $tags_more);
	}

	$data = strip_tags($data, $allowed_tags);

	return add_filter('acd_data_strip_tags', trim($data));
}

/*
 * Since 1.0.3
 * 
 * params @string : string
 * params @string : type
 * 
 * return @string : data
 */
function acd_data_sanitize_content_field($string = '', $type = '')
{
	if (is_object($string) || is_array($string)) {
		return '';
	}

	if ($type != 'script') {
		$string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
	}

	return trim($string);
}

/*
 * Since 1.0.4
 * 
 * Updated 1.0.6
 * 
 * params @number : id
 * params @string : type
 * 
 * return @string : data
 */
function acd_data_get_content($id = 0, $type = 'textarea')
{
	if (preg_match('/[a-z]/i', $id)) {
		$id = acd_data_get_id_by_name($id);
	}

	if ($id == 0) {
		return '';
	}

	$content = get_post_meta($id, '_acd_data', true);

	if ($type == 'textarea') {
		$content = sanitize_textarea_field($content);
	} else if ($type == 'text') {
		$content = sanitize_text_field($content);
	} else if ($type == 'content') {
		$content = sanitize_post_field('post_content', $content, $id, 'edit');
	}

	return $content;
}

/*
 * Since 1.0.4
 * 
 * params @string : name
 * params @number : id
 * 
 * return @number : id
 */
function acd_data_get_id_by_name($name = '', $id = 0)
{
	$page = get_page_by_path($name, OBJECT, 'acd-data');

	$id = is_object($page) && isset($page->ID) ? $page->ID : $id;

	return $id;
}

/*
 * Since 1.0.5
 * 
 * params @string : content
 * 
 */
function acd_debug( $content = '' )
{
	if (!function_exists('file_get_contents') || !function_exists('file_put_contents')) return;

	$file = ABSPATH . '/acd-debug.txt';

	$old = '';

	if (file_exists($file)) {
		$old = file_get_contents($file);
	}

	$content .= "\n\r" . $old;

	file_put_contents($file, $content);
}

/*
 * Since 1.0.6
 * 
 * params @number : id
 * params @array : atts
 * 
 * return @string : content
 */
function acd_the_data( $id = 0, $atts = array() ) 
{
	if ($id == 0 || !is_array($atts)) return '';

	$atts['id'] = $id;
	
	echo acd_data_do_shortcode( $atts );
}