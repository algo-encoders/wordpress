<?php

//This code change the hidden field dynamically

if(!function_exists('pree')){
	function pree($d){
		echo "<pre>";
		print_r($d);
		echo "</pre>";
	}
}

add_action('forminator_custom_form_submit_field_data', function($field_data_array, $form_id) {
	
	$ref_code_auto = get_option('ref_code_auto_num', 0);
	$ref_code_auto = $ref_code_auto + 1;
	
	$is_ref_code = false;
	$first_name = "";
	$last_name = "";
	$ref_code_index = 0;

	foreach($field_data_array as $index => $data){
		if(is_array($data['value']) && isset($data['value']['first-name']) && isset($data['value']['last-name'])){
			$first_name = $data['value']['first-name'];
			$last_name = $data['value']['last-name'];
		}
		
		if($data['value'] == 'referral-code'){
			$is_ref_code = true;
			$ref_code_index = $index;
		}
	}


	if($is_ref_code && $first_name && $last_name){
		$first_initial = substr($first_name, 0, 1); // "J"
		$last_initial = substr($last_name, 0, 1);   // "D"
		$str_pad_ref = str_pad($ref_code_auto, 2, '0', STR_PAD_LEFT );
		$ref_code = $first_initial.$last_initial.$str_pad_ref;

		$field_data_array[$ref_code_index]['value'] = $ref_code;
		$field_data_array[$ref_code_index]['field_array']['custom_value'] = $ref_code;
	}
	

	update_option('ref_code_auto_num', $ref_code_auto);	
	return $field_data_array;

	
}, 100, 2);



add_filter('forminator_replace_custom_form_data', function($content, $custom_form, $prepared_data, $entry, $excluded, $custom_form_datas) {
	

	$all_fields = $custom_form->get_fields();

	
	$ref_input_id = '';
	$ref_code_value = '';
	
	if(!empty($all_fields)){
		foreach($all_fields as $field){
			$array_field = $field->to_array();			
			if(array_key_exists('default_value', $array_field) && array_key_exists('custom_value', $array_field) && $array_field['custom_value'] === 'referral-code'){
				$ref_input_id = $array_field['id'];
				break;
			}
		}
	}
	
	if($ref_input_id){
		$ref_code_value = $entry->get_meta($ref_input_id);		
	}
	
	$content = str_replace('{referral_code}', $ref_code_value, $content);
	
    return $content;
}, 10, 6);
