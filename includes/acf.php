<?php
/**
 * Advanced Custom Data
 * 
 * Support to Advanced Custom Fields
 */
defined('ABSPATH') or die();

/*
 * Since 1.0.4
 * 
 * return 
 * 
 * @array : $field
 */
function acd_data_acf_prepare_field($field = array())
{
	// Abort if it's native option
	if ($field['ID'] === 0) return $field;

	$types = array("select", "checkbox", "radio");

	if (in_array($field['type'], $types)) {
		$acd_data_id = 0;

		foreach ($field['choices'] as $opt) {
			if (preg_match('/(acd-data)/i', $opt)) {
				$a = explode(':', $opt);
				$acd_data_id = isset($a[1]) ? trim($a[1]) : 0;
				break;
			}
		}

		if ($acd_data_id === 0) {
			return $field;
		}

		$data = acd_data_get_content($acd_data_id);
		if (empty($data) || $data == '') {
			return $field;
		}

		$choices = [];

		foreach (explode("\n", $data) as $value) {
			$value = sanitize_text_field($value);

			$list = array_map('sanitize_text_field', explode(':', $value));
			if (isset($list[1])) {
				$choices[$list[0]] = $list[1];
			} else {
				$choices[$value] = $value;
			}
		}

		$field['choices'] = $choices;
	}

	return $field;
}
add_filter('acf/prepare_field', 'acd_data_acf_prepare_field', 1, 1);