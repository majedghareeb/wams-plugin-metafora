<?php

namespace wams\core;

use GFAPI;

if (!defined('ABSPATH')) {
    exit;
}



if (!class_exists('wams\core\Vendors_Importer')) {

    class Vendors_Importer
    {
        /**
         *
         */
        public function vendors_importer_ajax_handler()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'wams-frontend-nonce')) {
                wp_die(esc_attr__('Security Check', 'wams'));
            }

            if (empty($_POST['param'])) {
                wp_send_json_error(__('Invalid Action.', 'wams'));
            }

            switch ($_POST['param']) {
                case 'start_import':
                    $message = $this->import_vendors_from_csv();
                    wp_send_json(['message' => json_encode($message['message']), 'current' => $message['current'], 'total' => $message['total']]);
                    wp_die();
                    break;
            }
        }
        public function upload_vendors_list()
        {
            $option = get_option('wams_csv_template_vendors');
            $message = [
                'type' => 'warning',
                'text' => 'Please upload your file based on the below template.'
            ];
            if (isset($_POST['upload_csv'])) {
                if (!isset($_FILES['csv_file']) || !wp_verify_nonce($_POST['csv_upload_nonce'], 'csv_upload_nonce')) {
                    return;
                }
                $file = $_FILES['csv_file'];
                if ($file['error'] !== 0) {
                    return;
                }
                $file_type = wp_check_filetype(basename($file['name']), ['csv']);
                $upload_dir = wp_upload_dir();
                $target_file = $upload_dir['path'] . '/' . basename($file['name']);
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    $message =  $target_file . ' CSV file uploaded successfully!';
                    if (file_exists($target_file)) {
                        if (($handle = fopen($target_file, 'r')) !== false) {
                            // Output table header
                            $rowCount = 0;
                            $header = fgetcsv($handle);
                            if ($this->verify_uploaded_csv($header)) {
                                while (($row = fgetcsv($handle)) !== false) {
                                    $rowCount++;
                                }
                                $file_details = [
                                    'file_path' => $target_file,
                                    'count' => $rowCount
                                ];
                                $message =  ['type' => 'success', 'text' => 'The file contain :#' . ($rowCount) . ' items to be imported please press start to continue!'];
                                // $serialized_data = serialize($data_arr);
                                set_transient('wams_imported_csv', $file_details, MINUTE_IN_SECONDS);
                            } else {
                                $message =  ['type' => 'warning', 'text' => 'Your File does not match the template, please ask the administrator to update the template!'];
                            }
                        }
                    }
                } else {
                    // Error handling for file upload failure
                    $message =  ['type' => 'danger', 'text' => 'Failed to upload CSV file.'];
                }
            }
            if (isset($_POST['export_csv']) && isset($_POST['file_path'])) {

                $csv_file_path = $_POST['file_path'];
                if (file_exists($csv_file_path)) {
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="' . basename($csv_file_path) . '"');
                    header('Pragma: no-cache');
                    header('Expires: 0');

                    $output = fopen('php://output', 'w');

                    if (($handle = fopen($csv_file_path, 'r')) !== false) {
                        // Output CSV header
                        $header = fgetcsv($handle);
                        fputcsv($output, $header);

                        // Output CSV data
                        while (($data = fgetcsv($handle)) !== false) {
                            fputcsv($output, $data);
                        }

                        fclose($handle);
                    }

                    fclose($output);
                    exit;
                } else {
                    echo 'File not found.';
                }
            }
            WAMS()->get_template('upload-vendors-list.php', '', ['option' => $option, 'message' => $message], true);
        }

        public function verify_uploaded_csv($header)
        {
            $option = get_option('wams_csv_template_vendors');
            $template_header = '';
            if (isset($option['path']) && file_exists($option['path'])) {
                if (($handle = fopen($option['path'], 'r')) !== false) {
                    $template_header = fgetcsv($handle);
                }
            }
            if ($template_header == $header) {
                return true;
            } else {
                return false;
            }
        }


        public function import_vendors_from_csv()
        {
            // $message = [];
            $template = get_option('wams_csv_template_vendors');
            if (!$template) {
                return ['message' => 'Please Create Template', 'total' => 0, 'current' => 0];
            }
            $target_file = get_transient('wams_imported_csv');
            $total_count = $target_file['count'];
            $counter = 0;
            // $message[] = $target_file['file_path'];
            if (isset($target_file['file_path']) && file_exists($target_file['file_path'])) {
                if (($handle = fopen($target_file['file_path'], 'r')) !== false) {
                    // $csvData = array();
                    $header = fgetcsv($handle);
                    $i = 0;
                    $vendor_template = $this->get_vendor_template();
                    while (($row = fgetcsv($handle)) !== false) {
                        // $csvData[] = $row;
                        // $message[] = $counter++ . ' : ' . $row[3];
                        // if ($i == 10) exit;
                        $i++;
                        $insert = $this->insert_new_vendor($row, $template['data'], $vendor_template);
                        $message[] =  $insert['message'];
                    }
                    fclose($handle);
                    // if (!empty($csvData)) {
                    // 	wp_send_json_success($csvData);
                } else {
                    $message[] =  ('Error reading the CSV file.');
                }
            } else {
                $message[] = ('Error in file');
            }



            // $message[] = ('Import CSV Started @ Row : ' . $start . ' !');
            return ['message' => $message, 'total' => $total_count, 'current' => $i];
        }

        public function get_vendor_template()
        {
            $current_user_id = get_current_user_id();
            $forms_settings = get_option('wams_forms_settings');
            // Get Forms Details from settings
            if ($forms_settings) {
                $vendor_form_id = $forms_settings['vendor_form'] ?? 0;
                $company_form_id = $forms_settings['company_form'] ?? 0;
                $project_form_id = $forms_settings['project_form'] ?? 0;
                $vendor_personal_details_form_id = $forms_settings['vendor_personal_details_form'] ?? 0;
                $vendor_banking_details_form_id = $forms_settings['vendor_banking_details_form'] ?? 0;
            }
            $vendor_form_fields = [];
            if ($form = \GFAPI::get_form($vendor_form_id)) {
                foreach ($form['fields'] as $field) {
                    if ($field->adminLabel == '') continue;
                    switch ($field->adminLabel) {
                        case 'vendor_name':
                            $vendor_name = $field->id;
                            break;
                        case 'vendor_sap_id':
                            $vendor_sap_id = $field->id;
                            break;
                        case 'vendor_company':
                            $vendor_company = $field->id;
                            break;
                        case 'vendor_personal_details':
                            $vendor_personal_details_field_id = $field->id;
                            break;
                        case 'vendor_banking_details':
                            $vendor_banking_details_field_id = $field->id;
                            break;
                        case 'vendor_type':
                            $vendor_type = $field->id;
                            break;
                    }
                }
            }
            if (!$vendor_name || !$vendor_sap_id || !$vendor_company || !$vendor_personal_details_field_id || !$vendor_banking_details_field_id) __return_false();

            return [
                'current_user_id' => $current_user_id,
                'vendor_form_id' => $vendor_form_id,
                'vendor_personal_details_field_id' => $vendor_personal_details_field_id,
                'vendor_banking_details_field_id' => $vendor_banking_details_field_id,
                'vendor_sap_id' => $vendor_sap_id,
                'vendor_type' => $vendor_type,
                'vendor_personal_details_form_id' => $vendor_personal_details_form_id,
                'vendor_banking_details_form_id' => $vendor_banking_details_form_id,
            ];
        }
        public function insert_new_vendor($row, $template, $form_template)
        {
            $current_user_id = $form_template['current_user_id'];
            $vendor_form_id = $form_template['vendor_form_id'];
            $vendor_personal_details_field_id = $form_template['vendor_personal_details_field_id'];
            $vendor_banking_details_field_id = $form_template['vendor_banking_details_field_id'];
            $vendor_sap_id = $form_template['vendor_sap_id'];
            $vendor_type = $form_template['vendor_type'];
            $vendor_personal_details_form_id = $form_template['vendor_personal_details_form_id'];
            $vendor_banking_details_form_id = $form_template['vendor_banking_details_form_id'];

            $entry_vendor = [
                'form_id' => $vendor_form_id,
                'created_by' => $current_user_id,
                $vendor_type => '["Freelancer"]'
            ];
            $personal_details_entry = [
                'form_id' => $vendor_personal_details_form_id,
                'created_by' => $current_user_id,
            ];
            $banking_details_entry = [
                'form_id' => $vendor_banking_details_form_id,
                'created_by' => $current_user_id,
            ];

            // Get template 
            foreach ($template as $field) {
                // each template field has [csv_index, csv_label, form_id, field_id ]
                // Check if row has empty value
                if ($row[$field['csv_index']] == '') continue;
                //Check if Vendor already imported
                // SAP ID is unique value
                if ($field['field_id'] == $vendor_sap_id) {
                    $search_criteria_0 = array(
                        'status'        => 'active',
                        'field_filters' => array(
                            'mode' => 'any',
                            array(
                                'key'   => $vendor_sap_id,
                                'value' => $row[$field['csv_index']]
                            )
                        )
                    );
                    $result = GFAPI::count_entries($vendor_form_id, $search_criteria_0);
                    if ($result > 0) {
                        return ["message" => "Entry with SAP ID: " .  $row[$field['csv_index']] . " is already exists!"];
                    }
                }
                switch ($field['form_id']) {
                    case $vendor_form_id:
                        $entry_vendor[$field['field_id']] = $row[$field['csv_index']];
                        break;
                    case $vendor_personal_details_form_id:
                        $personal_details_entry[$field['field_id']] =  $row[$field['csv_index']];
                        break;
                    case $vendor_banking_details_form_id:
                        $banking_details_entry[$field['field_id']] =  $row[$field['csv_index']];
                        break;
                }
            }

            // Insert Vendor Main Entry
            $new_vendor_entry_id = GFAPI::add_entry($entry_vendor);
            if (is_wp_error($new_vendor_entry_id)) {
                return ["message" => "Could not create new vendor!! "  . $new_vendor_entry_id->get_error_message()];
            }
            // Insert personal details nested form entry

            $new_personal_entry_id = GFAPI::add_entry($personal_details_entry);
            if (!is_wp_error($new_personal_entry_id)) {
                GFAPI::update_entry_field($new_vendor_entry_id, $vendor_personal_details_field_id, $new_personal_entry_id);
                GFAPI::update_entry_field($new_personal_entry_id, 'gpnf_entry_parent', $new_vendor_entry_id);
                GFAPI::update_entry_field($new_personal_entry_id, 'gpnf_entry_parent_form', $vendor_form_id);
                GFAPI::update_entry_field($new_personal_entry_id, 'gpnf_entry_nested_form_field', $vendor_personal_details_field_id);
            }
            // Insert banking details nested form entry

            $new_banking_entry_id = GFAPI::add_entry($banking_details_entry);
            if (!is_wp_error($new_banking_entry_id)) {
                GFAPI::update_entry_field($new_vendor_entry_id, $vendor_banking_details_field_id, $new_banking_entry_id);
                GFAPI::update_entry_field($new_banking_entry_id, 'gpnf_entry_parent', $new_vendor_entry_id);
                GFAPI::update_entry_field($new_banking_entry_id, 'gpnf_entry_parent_form', $vendor_form_id);
                GFAPI::update_entry_field($new_banking_entry_id, 'gpnf_entry_nested_form_field', $vendor_banking_details_field_id);
            }

            return ["message" => "Vendor Created with ID: # "  . $new_vendor_entry_id];
        }


        public function get_related_id_from_form($form_id, $field_id, $field_value, $related_field)
        {
            $search_criteria = array(
                'status'        => 'active',
                'field_filters' => array(
                    'mode' => 'any',
                    array(
                        'key'   => $field_id,  // check if the entry already copied
                        'value' => $field_value,
                    )
                )
            );
            $search_result = GFAPI::get_entries($form_id, $search_criteria);
            // echo '<pre>' . print_r($search_result, true) . '</pre>';
            if ($search_result) {
                return rgar($search_result[0], $related_field);
            }
        }
    }
}
