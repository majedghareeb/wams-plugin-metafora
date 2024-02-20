<?php

class Gravity_Flow_Step_Wams_Process extends Gravity_Flow_Step
{
    // Make this unique
    public $_step_type = 'update_post_status';

    /**
     * Returns the label for the step type.
     *
     * @return string
     */
    public function get_label()
    {
        return 'Custom Input Step';
    }

    public function get_icon_url()
    {
        //Replace with your own path / image
        return  plugin_dir_url(__FILE__) . '/process-icon.jpg';
    }

    public function get_settings()
    {

        return array(
            'title'  => 'Update Post Status Step',
            'fields' => array(
                // $settings_api->get_setting_assignee_type(),
                // $settings_api->get_setting_assignees(),
                // $settings_api->get_setting_assignee_routing(),
                array(
                    'name'          => 'process_type',
                    'label'         => esc_html__('Process Type', 'gravityflowcs'),
                    'type'          => 'radio',
                    'default_value' => 'url_parse',
                    'horizontal'    => true,
                    'onchange'      => 'jQuery(this).closest("form").submit();',
                    'choices'       => array(
                        array('label' => esc_html__('URL Parse', 'gravityflowcs'), 'value' => 'url_parse'),
                        array('label' => esc_html__('Get Client Details', 'gravityflowcs'), 'value' => 'get_client_details'),
                    ),
                ),
                array(
                    'name'     => 'url_field_id',
                    'required' => true,
                    'label'    => 'URL Field to Check',
                    'type'     => 'field_select',
                    'args'       => array(
                        'input_types' => array('text', 'website', 'list'),
                    ),
                    'tooltip'  => __('Select the field which will contain the URL.', 'gravityflowcs'),
                    'dependency' => array(
                        'field'  => 'process_type',
                        'values' => array('url_parse'),
                    ),

                    // 'choices'  => $outputStatus,
                ),
                array(
                    'name'     => 'client_id_field_id',
                    'required' => true,
                    'label'    => 'Select Client ID Field',
                    'type'     => 'field_select',
                    'args'       => array(
                        'input_types' => array('number', 'wams_search', 'text'),
                    ),
                    'tooltip'  => __('Select the field which will contain the Client ID.', 'gravityflowcs'),
                    'dependency' => array(
                        'field'  => 'process_type',
                        'values' => array('get_client_details'),
                    ),

                    // 'choices'  => $outputStatus,
                ),
                array(
                    'name'     => 'output_field_id',
                    'required' => true,
                    'label'    => 'Select the output field',
                    'type'     => 'field_select',
                    'args'       => array(
                        'input_types' => array('textarea'),
                    ),
                    'tooltip'  => __('Select the textarea Field to write the output in it', 'gravityflowcs'),

                    // 'choices'  => $outputStatus,
                ),
            ),
        );
    }

    /**
     * Retrieve the associated entry / post object and update the status to selected value
     *
     * @return bool Is the step complete?
     */
    public function process()
    {
        $process_type = $this->process_type;
        switch ($process_type) {
            case 'url_parse':
                $result = $this->process_url_parse();
                break;
            case 'get_client_details':
                $result = $this->process_get_client_details();
                break;
        }

        $note = $this->get_name() . ': ' . esc_html__('Processed.', 'gravityflow');
        $this->add_note($note);
        return $result;

        // $url = rgar($this->get_entry(), $this->url);
        // if (filter_var($string, FILTER_VALIDATE_URL) !== false) return false;

    }



    public function process_url_parse()
    {
        $meta_data = [];
        $url_field_id = $this->get_setting('url_field_id');
        $output_field_id = $this->get_setting('output_field_id');
        $entry = $this->get_entry();
        $form = $this->get_form();
        $field = GFAPI::get_field($form, $url_field_id);
        $field_type = $field->get_input_type();
        switch ($field_type) {
            case 'list':
                $list = rgar($this->get_entry(), $url_field_id);
                $list = unserialize($list);
                foreach ($list as $url) {
                    if (!$this->isURL($url)) continue;
                    $meta_data = $this->get_url_meta_data($url);
                    $entry[$output_field_id]  .= print_r($meta_data, true);
                }

                break;
            case 'text':
                $url = rgar($this->get_entry(), $url_field_id);
                if (!$this->isURL($url)) return false;
                $meta_data = $this->get_url_meta_data($url);
                $entry[$output_field_id]  .= print_r($meta_data, true);
                break;
            case 'website':
                $url = rgar($this->get_entry(), $url_field_id);
                $meta_data = $this->get_url_meta_data($url);
                $entry[$output_field_id]  .= print_r($meta_data, true);
                break;
            default:
                return false;
                break;
        }
        GFAPI::update_entry($entry);

        return true;
    }
    public function process_get_client_details()
    {
        $client_data = [];
        $client_id_field_id = $this->get_setting('client_id_field_id');
        $output_field_id = $this->get_setting('output_field_id');
        $entry = $this->get_entry();
        $form = $this->get_form();
        $field = GFAPI::get_field($form, $client_id_field_id);
        $field_type = $field->get_input_type();
        switch ($field_type) {
            case 'wams_search':
            case 'number':
                $client_id = rgar($this->get_entry(), $client_id_field_id);
                $client_data = $this->get_client_data($client_id);
                $entry[$output_field_id]  = $client_data;
                break;
            default:
                return false;
                break;
        }
        GFAPI::update_entry($entry);
        return true;
    }

    public function isURL($string)
    {
        return filter_var($string, FILTER_VALIDATE_URL) !== false;
    }


    public function get_form_field_type()
    {
        $form = GFAPI::get_form(23);
        if (is_array($form['fields'])) {
            foreach ($form['fields'] as $field) {
                echo $input_type = $field->get_input_type() . '<br>';
                echo  $inputs     = print_r($field->get_entry_inputs(), true) . '<br>';
            }
        }
    }



    public function getDateTime($string)
    {
        if (strlen($string) > 24) $string = preg_replace('/:00/', '', $string);
        $dateFormats = [
            'Y-m-d\TH:i:sO',      // 2023-08-28T16:07:43+0300
            'Y-m-d H:i:s',        // 2023-08-28 16:07:43
            'Y-m-d',              // 2023-08-28
            // Add more formats here...
        ];
        foreach ($dateFormats as $format) {
            $datetime = DateTime::createFromFormat($format, $string);
            if ($datetime !== false) {
                return $datetime->format('Y-m-d H:i:s');
            }
        }

        return false; // No matching format found
    }
    /**
     * get Client Related Data
     *
     * @param [type] $client id
     * @return void
     */
    public function get_client_data($client_id)
    {
        $forms = get_option('wams_input_forms_settings');
        $client_add_new_form = $forms['client_add_new_form'];
        $website = $forms['client_writing_request_form'];
        $assignment = $forms['client_media_request_form'];
        $interview = $forms['client_interview_request_form'];
        $related_orders = '';
        $forms = [
            [
                'id' => $website,
                'title' => 'Writing Request',
                'client_id_field' => 29,
                'total_records_field_id' => 16
            ],
            [
                'id' => $assignment,
                'title' => 'Media Request',
                'client_id_field' => 71,
                'total_records_field_id' => 17
            ],
            [
                'id' => $interview,
                'title' => 'Interview Request',
                'client_id_field' => 76,
                'total_records_field_id' => 18
            ]
        ];
        $client_entry = GFAPI::get_entry($client_id);
        $related_orders = '';
        if (!is_wp_error($client_entry)) {
            foreach ($forms as $form) {
                $field_filters[] = array(
                    'key'   => $form['client_id_field'],
                    'value' => $client_id
                );
                $search_criteria['field_filters'] = $field_filters;
                $search_criteria['status'] = 'active';
                // $entry_ids = GFAPI::get_entry_ids($form['id'], $search_criteria, null, null);
                // $related_orders .= print_r($entry_ids, true) . '<br>';
                $total_records = 0;

                $entries = GFAPI::get_entries($form['id'], $search_criteria, null, null, $total_records);

                $client_entry[$form['total_records_field_id']] = $total_records;

                if ($total_records > 0) {
                    $related_orders .= $this->print_related_orders_to_table($entries, $form);
                }
            }
            // GFAPI::update_entry($client_entry);
        }


        // $related_orders .= print_r($client_entry, true);
        return $related_orders;
    }

    private function print_related_orders_to_table($entries, $form)
    {
        $related_orders = '<h2> ' . __('Total Records', 'wams') . ' : ' . count($entries) . ' - ' . __('In', 'wams') . ' : ' . $form['title'] . '</h2><br>';
        $related_orders .= '<table class="table table-strip table-bordered">';
        $related_orders .= '<thead>';
        $related_orders .= '<tr>';
        $related_orders .= '<th>' . __('Entry ID', 'wams') . '</th>';
        $related_orders .= '<th>Client</th>';
        $related_orders .= '<th>' . __('Created By', 'wams') . '</th>';
        $related_orders .= '<th>' . __('Date Created', 'wams') . '</th>';
        $related_orders .= '<th>' . __('Workflow Status', 'wams') . '</th>';
        $related_orders .= '<th>' . __('Approval Status', 'wams') . '</th>';
        $related_orders .= '</tr>';
        $related_orders .= '</thead>';
        foreach ($entries as $entry) {
            $is_approved = GravityView_Entry_Approval::get_entry_status($entry);
            // print_r($entry);
            $related_orders .= '<tr>';
            $related_orders .= '<td>' . rgar($entry, 'id', '') . '</td>';
            $related_orders .= '<td>' . rgar($entry, $form['client_id_field'], '') . '</td>';
            $related_orders .= '<td>' . get_userdata(rgar($entry, 'created_by', ''))->display_name ?? 'N/A'  . '</td>';
            $related_orders .= '<td>' . rgar($entry, 'date_created', '') . '</td>';
            $related_orders .= '<td>' . rgar($entry, 'workflow_final_status', '') . '</td>';
            $related_orders .= '<td>' . $is_approved . '</td>';
            $related_orders .= '</tr>';
        }
        $related_orders .= '</table>';
        return $related_orders;
    }
    // Function to extract meta data from a URL
    public function get_url_meta_data($url)
    {
        // Use WordPress HTTP API to fetch the URL and extract meta data
        $html = wp_remote_retrieve_body(wp_remote_get($url));

        // Parse the HTML and extract the meta data
        $doc = new DOMDocument();
        @$doc->loadHTML($html);

        $title = '';
        $description = '';

        // Get the title tag
        $title_elements = $doc->getElementsByTagName('title');
        if ($title_elements->length > 0) {
            $title = $title_elements->item(0)->nodeValue;
        }

        // Get the meta description tag
        $meta_tags = [];
        $schema = [];
        $meta_elements = $doc->getElementsByTagName('meta');
        foreach ($meta_elements as $meta) {
            $key =  ($meta->getAttribute('name')) ? $meta->getAttribute('name') : $meta->getAttribute('property');
            $content = $meta->getAttribute('content');
            if ($key !== '') {
                $meta_tags[$key] = $content;
            }
        }
        $scriptElements = $doc->getElementsByTagName('script');
        $schemaJsonLd = null;
        $dataLayer = [];
        foreach ($scriptElements as $scriptElement) {
            if ($scriptElement->getAttribute('type') === 'application/ld+json') {
                $schemaJsonLd = json_decode($scriptElement->textContent);
            }
            $text = $scriptElement->textContent;
            $pattern = '/dataLayer\.push\((.*?)\);/s';
            preg_match($pattern, $text, $matches);

            if (isset($matches[1])) {
                $jsonString = $matches[1];
                $lastCommaPosition = strrpos($jsonString, ',');

                if ($lastCommaPosition !== false) {
                    // Remove the last comma
                    $stringWithoutComma = substr_replace($jsonString, '', $lastCommaPosition, 1);
                    $stringWithoutComma = str_replace("'", '"', $stringWithoutComma);
                    // echo $stringWithoutComma;
                }
                $arrayData = json_decode($stringWithoutComma, true);

                if ($arrayData !== null) {
                    foreach ($arrayData as $key => $value) {
                        $dataLayer[$key] =  $value;
                    }
                } else {
                    $dataLayer[] =  ["Invalid JSON string."];
                }
                // echo  $jsonString;
            }
        }


        if (isset($schemaJsonLd->{"@graph"}[0])) {
            $schema['headline'] = $schemaJsonLd->{"@graph"}[0]->headline;
            $schema['description'] = $schemaJsonLd->{"@graph"}[0]->description;
            $schema['author'] = $schemaJsonLd->{"@graph"}[0]->author->name;
            // $datePublished = new DateTime($schemaJsonLd->{"@graph"}[0]->datePublished);
            $datePublished = $schemaJsonLd->{"@graph"}[0]->datePublished;
            $dateModified = $schemaJsonLd->{"@graph"}[0]->dateModified;
            $datePublishedFormat = $this->getDateTime($datePublished) ?? $this->getDateTime($datePublished);
            $dateModifiedFormat = $this->getDateTime($dateModified) ?? $this->getDateTime($dateModified);
            // $dateModified = new DateTime($schemaJsonLd->{"@graph"}[0]->dateModified);
            $schema['datePublished'] = $datePublishedFormat;
            $schema['dateModified'] = $dateModifiedFormat;
        } else {

            $schema =  ["graph not found."];
        }

        // Return the extracted meta data
        return array(
            'meta_tags' => $meta_tags,
            'schema' => $schema,
            'dataLayer' => $dataLayer,
        );
    }
}
