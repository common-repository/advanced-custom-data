<?php
/**
 * Advanced Custom Data
 * 
 * Support to Contact Form 7
 */
defined('ABSPATH') or die();

/*
 * Since 1.0.0
 * 
 * Updated 1.0.4
 * 
 * return 
 * 
 * @array : scanned_tag
 */
function acd_data_wpcf7_form_tag($scanned_tag, $replace)
{
	if (count($scanned_tag['options']) == 0) {
		return $scanned_tag;
	}

	$types = array("select", "checkbox", "radio");

	foreach ($types as $value) {
		$types[] = $value . '*';
	}

	if (in_array($scanned_tag['type'], $types)) {
		$acd_data_id = 0;

		foreach ($scanned_tag['options'] as $opt) {
			if (preg_match('/(acd-data)/i', $opt)) {
				$a = explode(':', $opt);

				// get id or name 
				$acd_data_id = isset($a[1]) ? $a[1] : 0;
				break;
			}
		}

		if ($acd_data_id === 0) {
			return $scanned_tag;
		}

		$data = acd_data_get_content($acd_data_id);
		if (empty($data) || $data == '') {
			return $scanned_tag;
		}

		$values = array();
		$labels = array();

		foreach (explode("\n", $data) as $value) {
			$value = sanitize_text_field($value);

			$a = explode(':', $value);
			if (isset($a[1])) {
				$values[] = trim($a[0]);
				$labels[] = trim($a[1]);
			} else {
				$values[] = $value;
				$labels[] = $value;
			}
		}

		if (count($values) > 0) {
			$scanned_tag['raw_values'] = $values;
			$scanned_tag['values'] = $values;
			$scanned_tag['labels'] = $labels;
		}
	}

	return $scanned_tag;
}
add_filter('wpcf7_form_tag', 'acd_data_wpcf7_form_tag', 20, 2);

/*
 * Since 1.0.21
 * 
 */
function acd_data_wpcf7_editor_template()
{
	global $advanced_data;

	$page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
	$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

	if($page == 'wpcf7-new' || ($page == 'wpcf7' && $action == 'edit')) {
		$list = $advanced_data->get_data_list();
		
		foreach($list as $i => $p){
			$list[$i] = '<option value="acd-data:'.$p->ID.'">' . $p->post_title . '</option>';
		}

		?>
		<script type="text/template" id="acd-wpcf7-template">
			<tr>
				<th scope="row"><label for="tag-generator-panel-acd-data-id"><?php _e('Advanced Data', 'acd') ?></label></th>
				<td>
					<select name="acd-data-id" class="js-acd-data-id">
						<option value=""><?php _e('None', 'acd') ?></option>
						<?php echo implode("\n", $list) ?>
					</select>
				</td>
			</tr>
		</script>
		<?php
	}
}
add_action('admin_footer', 'acd_data_wpcf7_editor_template');

/*
 * Since 1.0.21
 * 
 */
function acd_data_wpcf7_editor_script()
{
	// $ver = current_time('YmdHis');

	$ver = '20240826083';

	wp_enqueue_script('acd-data-wpcf7', acd_url('/assets/js/cf7.js'), array('jquery'), $ver, true);
}
add_action('admin_enqueue_scripts', 'acd_data_wpcf7_editor_script');
