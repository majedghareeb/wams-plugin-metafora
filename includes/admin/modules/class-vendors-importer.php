<?php

namespace wams\admin\modules;

use \wams\admin\core\Admin_Settings_API;
use GFAPI;
use GFFormsModel;

class Vendors_Importer
{
    /**
     * @var object
     */
    private $settings_api;
    /**
     * @var array
     */
    private $page;


    /**
     * Admin_Menu constructor.
     */
    function __construct()

    {
        $this->settings_api = new Admin_Settings_API();
        $this->init_variables();
        $this->settings_api->addSubpages($this->page['subpage']);
        $this->settings_api->register();
    }

    public function init_variables()
    {
        $this->page = [
            'subpage' => [
                [
                    'parent_slug' => 'wams',
                    'page_title' => 'Vendors Importer',
                    'menu_title' => 'Vendors Importer',
                    'capability' => 'edit_wams_settings',
                    'menu_slug' => 'vendors_importer',
                    'callback' => array($this, 'vendors_importer_page')
                ]
            ]
        ];
    }
    public function vendors_importer_page()
    {
        echo '<h1>Vendors Importer</h1>';
        echo '<div class="wrap">';
        $forms_settings = get_option('wams_forms_settings');

        if ($forms_settings) {
            $company_form_id = $forms_settings['company_form'];
            $project_form_id = $forms_settings['project_form'];
            $vendor_form_id = $forms_settings['vendor_form'];
            $vendor_personal_details_form_id = $forms_settings['vendor_personal_details_form'];
            $vendor_banking_details_form_id = $forms_settings['vendor_banking_details_form'];

            $form_ids = [$vendor_form_id, $vendor_personal_details_form_id, $vendor_banking_details_form_id,];
            $forms_fields = [];
            foreach ($form_ids as $form_id) {
                $form = GFAPI::get_form($form_id);
                if ($form) {
                    foreach ($form['fields'] as $field) {
                        if (in_array($field['type'], ['form', 'section'])) continue;
                        $forms_fields[] = [
                            'form_id' => $form['id'],
                            'form_title' => $form['title'],
                            'field_id' => $field['id'],
                            'field_type' => $field['type'],
                            'field_label' =>  $field['label']
                        ];
                        // echo '<option value="' . $field['id'] . '">' . $field['label'] . '</option>';
                    }
                }
            }
        }
        if (isset($_POST['delete_template'])) {
            if (isset($_POST['file_path']) && file_exists($_POST['file_path'])) {
                $target_file = $_POST['file_path'];
                if (unlink($target_file)) {
                    $message = 'File deleted successfully.';
                    delete_option('wams_csv_template_' . $_POST['template']);
                } else {
                    $message = 'Error deleting the file.';
                }
            } else {
                $message = 'File does not exist.';
            }
        }
        if (isset($_POST['upload_csv'])) {
            if (!isset($_FILES['csv_file']) || !wp_verify_nonce($_POST['csv_upload_nonce'], 'csv_upload_nonce')) {
                return;
            }
            $file = $_FILES['csv_file'];

            if ($file['error'] !== 0) {
                // Handle error
                return;
            }

            $file_type = wp_check_filetype(basename($file['name']), ['csv']);
            $upload_dir = wp_upload_dir();
            $target_file = $upload_dir['path'] . '/' . basename('vendors_template.csv');

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                // File uploaded successfully, you can process the CSV file here
                // For example, you can use the $target_file path to read and import the CSV data
                delete_option('wams_csv_template_vendors');
                $message =  $target_file . ' CSV file uploaded successfully!';

                if (file_exists($target_file)) {
                    // echo '<h2>CSV Data</h2>';
                    if (($handle = fopen($target_file, 'r')) !== false) {
                        $header = fgetcsv($handle);
                        $first_row = fgetcsv($handle);
                        $first_row = fgetcsv($handle);
                        $first_row = fgetcsv($handle);
                        fclose($handle);
                        // echo '<pre>' . print_r($forms_fields, true) . '</pre>';
                        // print_template_table($header, $forms_fields);
                    }
                }
            } else {
                // Error handling for file upload failure
                $message =  'Failed to upload CSV file.';
            }
        }
        if (isset($_POST['save_map'])) {
            $formData = array();
            foreach ($_POST['select'] as $index => $value) {
                if ($value == '') continue;
                // Store the pair of text and select values for each row in the array
                $form_data = explode('.', $index);
                $csv_header = explode('.', $_POST['select'][$index]);

                $formData[] = array(
                    'csv_index' => $csv_header[0],
                    'csv_label' => $csv_header[1],
                    'form_id' => $form_data[0],
                    'field_id' => $form_data[1],
                );
            }
            $template = [
                'template' => 'vendors',
                'path' => $_POST['file_path'],
                'data' => $formData
            ];
            update_option('wams_csv_template_vendors', $template);
            $message =  'Vendor Template Saved';
            // echo '<pre>' . print_r($formData, true) . '</pre>';
        }
        include_once WAMS()->admin()->templates_path . 'vendors-importer.php';
        echo '</div>';
    }
}
