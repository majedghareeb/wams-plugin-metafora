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
                    'label'         => esc_html__('Process Type', 'wams'),
                    'type'          => 'radio',
                    'default_value' => 'url_parse',
                    'horizontal'    => true,
                    'onchange'      => 'jQuery(this).closest("form").submit();',
                    'choices'       => array(
                        array('label' => esc_html__('URL Parse', 'wams'), 'value' => 'url_parse', 'tooltip'  => __('Extract Data From URL.', 'wams'),),
                        array('label' => esc_html__('Get vendor Details', 'wams'), 'value' => 'get_vendor', 'tooltip'  => __('Get Vendor ID for An Author and create new one of not found.', 'wams')),
                        array('label' => esc_html__('Get Project Details', 'wams'), 'value' => 'get_project_details', 'tooltip'  => __('Get Project Details for a domain.', 'wams')),
                        array('label' => esc_html__('Update User Roles', 'wams'), 'value' => 'update_user_roles', 'tooltip'  => __('Update User Roles from Form field', 'wams')),

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
                    'tooltip'  => __('Select the field which will contain the URL.', 'wams'),
                    'dependency' => array(
                        'field'  => 'process_type',
                        'values' => array('url_parse'),
                    ),

                    // 'choices'  => $outputStatus,
                ),
                array(
                    'name'     => 'author_field_id',
                    'required' => true,
                    'label'    => 'Select Author Field',
                    'type'     => 'field_select',
                    'args'       => array(
                        'input_types' => array('text', 'number', 'wams_search'),
                    ),
                    'tooltip'  => __('Select the field which will contain the Author.', 'wams'),
                    'dependency' => array(
                        'field'  => 'process_type',
                        'values' => array('get_vendor'),
                    ),

                    // 'choices'  => $outputStatus,
                ),
                array(
                    'name'     => 'domain_field_id',
                    'required' => true,
                    'label'    => 'Select Domain Field',
                    'type'     => 'field_select',
                    'args'       => array(
                        'input_types' => array('text', 'website', 'select'),
                    ),
                    'tooltip'  => __('Select the field which will contain the Author.', 'wams'),
                    'dependency' => array(
                        'field'  => 'process_type',
                        'values' => array('get_project_details'),
                    ),

                    // 'choices'  => $outputStatus,
                ),
                array(
                    'name'     => 'project_field_id',
                    'required' => true,
                    'label'    => 'Select Project Field',
                    'type'     => 'field_select',
                    'args'       => array(
                        'input_types' => array('text', 'number', 'wams_search'),
                    ),
                    'tooltip'  => __('Select the field which will contain the Author.', 'wams'),
                    'dependency' => array(
                        'field'  => 'process_type',
                        'values' => array('get_project_details'),
                    ),

                    // 'choices'  => $outputStatus,
                ),
                array(
                    'name'     => 'project_manager_field_id',
                    'required' => true,
                    'label'    => 'Select Project Manager Field',
                    'type'     => 'field_select',
                    'args'       => array(
                        'input_types' => array('workflow_user'),
                    ),
                    'tooltip'  => __('Select the field which will contain the Author.', 'wams'),
                    'dependency' => array(
                        'field'  => 'process_type',
                        'values' => array('get_project_details'),
                    ),

                    // 'choices'  => $outputStatus,
                ),
                array(
                    'name'     => 'user_email_field',
                    'required' => true,
                    'label'    => 'Select User ID Field',
                    'type'     => 'field_select',
                    'args'       => array(
                        'input_types' => array('email'),
                    ),
                    'tooltip'  => __('Select the field which will contain the User ID.', 'wams'),
                    'dependency' => array(
                        'field'  => 'process_type',
                        'values' => array('update_user_roles'),
                    ),
                ),
                array(
                    'name'     => 'roles_field',
                    'required' => true,
                    'label'    => 'Select Roles Field',
                    'type'     => 'field_select',
                    'args'       => array(
                        'input_types' => array('select'),
                    ),
                    'tooltip'  => __('Select the field which will contain the Roles.', 'wams'),
                    'dependency' => array(
                        'field'  => 'process_type',
                        'values' => array('update_user_roles'),
                    ),
                ),
                array(
                    'name'     => 'output_field_id',
                    'required' => true,
                    'label'    => 'Select the output field',
                    'type'     => 'field_select',
                    'args'       => array(
                        'input_types' => array('text', 'wams_search'),
                    ),
                    'dependency' => array(
                        'field'  => 'process_type',
                        'values' => array('get_vendor'),
                    ),
                    'tooltip'  => __('Select the Vendor Field to write the matching vendor to', 'wams'),

                    // 'choices'  => $outputStatus,
                ),
                array(
                    'name'     => 'title_field_id',
                    'required' => true,
                    'label'    => 'Select the Title field',
                    'type'     => 'field_select',
                    'args'       => array(
                        'input_types' => array('text', 'wams_search'),
                    ),
                    'dependency' => array(
                        'field'  => 'process_type',
                        'values' => array('url_parse'),
                    ),
                    'tooltip'  => __('Select the field ID to set Title Tag of URL', 'wams'),

                    // 'choices'  => $outputStatus,
                ),
                array(
                    'name'     => 'hostname_field_id',
                    'required' => true,
                    'label'    => 'Select the Hostname field',
                    'type'     => 'field_select',
                    'args'       => array(
                        'input_types' => array('text', 'wams_search'),
                    ),
                    'dependency' => array(
                        'field'  => 'process_type',
                        'values' => array('url_parse'),
                    ),
                    'tooltip'  => __('Select the field ID to set Hostname of URL', 'wams'),

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
            case 'get_vendor':
                $result = $this->process_get_vendor();
                break;
            case 'get_project_details':
                $result = $this->process_project_details();
                break;
            case 'update_user_roles':
                $result = $this->update_user_roles();
                break;
        }

        $note = $this->get_name() . ': ' . esc_html__('Processed.', 'gravityflow');
        $this->add_note($note);
        return $result;

        // $url = rgar($this->get_entry(), $this->url);
        // if (filter_var($string, FILTER_VALIDATE_URL) !== false) return false;

    }

    public function update_user_roles()
    {
        $user_id_field_id = $this->get_setting('user_id_field');
        $roles_field_id = $this->get_setting('roles_field');
        $entry = $this->get_entry();
        $form = $this->get_form();
        $user_id = rgar($this->get_entry(), $user_id_field_id);
        $roles = rgar($this->get_entry(), $roles_field_id);
        WAMS()->common()->Logger()::info('Custom Step : ' . $user_id . ' ' . $roles);
    }

    public function process_url_parse()
    {
        $meta_data = [];
        $url_field_id = $this->get_setting('url_field_id');
        $title_field_id = $this->get_setting('title_field_id');
        $hostname_field_id = $this->get_setting('hostname_field_id');
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
                    $title = $this->get_url_title($url);
                    $hostname = $this->get_host_name($url);
                    $entry[$title_field_id]  = $title;
                    $entry[$hostname_field_id]  = $hostname;
                }
                break;
            case 'text':
            case 'website':
                $url = rgar($this->get_entry(), $url_field_id);
                if (!$this->isURL($url)) {
                    $entry[$title_field_id] = 'Not Valid URL';
                } else {
                    $title = $this->get_url_title($url);
                    $hostname = $this->get_host_name($url);
                    $entry[$title_field_id]  = $title;
                    $entry[$hostname_field_id]  = $hostname;
                }
                break;
        }
        GFAPI::update_entry($entry);

        return true;
    }

    public function process_get_vendor()
    {
        $author_field_id = $this->get_setting('author_field_id');
        $output_field_id = $this->get_setting('output_field_id');
        $entry = $this->get_entry();
        $form = $this->get_form();
        $field = GFAPI::get_field($form, $author_field_id);
        $field_type = $field->get_input_type();
        switch ($field_type) {
            case 'text':
                // author name is stored
                $author = rgar($this->get_entry(), $author_field_id);
                $vendor_id = $this->get_vendor_by_rss_author_name($author);
                $entry[$output_field_id]  = $vendor_id;
                break;
            default:
                return false;
                break;
        }
        GFAPI::update_entry($entry);
        return true;
    }
    public function process_project_details()
    {
        $domain_field_id = $this->get_setting('domain_field_id');
        $project_field_id = $this->get_setting('project_field_id');
        $project_manager_field_id = $this->get_setting('project_manager_field_id');

        $entry = $this->get_entry();
        $form = $this->get_form();
        $field = GFAPI::get_field($form, $domain_field_id);
        $field_type = $field->get_input_type();
        switch ($field_type) {
            case 'text':
            case 'website':
                $domain = $this->get_host_name(rgar($this->get_entry(), $domain_field_id));
                $project = $this->get_project_data($domain);
                if (!empty($project)) {
                    $entry[$project_field_id]  = $project['name'];
                    $entry[$project_manager_field_id]  = $project['project_manager'];
                }
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

    /**
     * Get  Host Name from URL and remove subdomain
     * @param   string  URL
     * @return  string  Hostname
     */
    public function get_host_name($url)
    {
        $parsedUrl = parse_url($url);
        $hostName = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';

        $domain = preg_replace('/^www\./', '', $hostName);
        return $domain;
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
     * get vendor ID for Author
     *
     * @param [author] Author Name
     * @return mixed Vendor ID or False if not found
     */
    public function get_vendor_by_rss_author_name($author)
    {
        // switch_to_blog(WAMS_MAIN_BLOG_ID);
        $wams_rss_fetcher_settings = get_option('wams_rss_fetcher_settings') ?? 0;
        $wams_urls_form_settings = get_option('wams_urls_form_settings') ?? 0;
        $wams_vendor_on_rss_form_settings = get_option('wams_vendor_on_rss_form_settings') ?? 0;
        $vendor_name_on_rss_form_id = $wams_rss_fetcher_settings['vendor_rss_form'] ?? 0;
        $author_name_field = $wams_vendor_on_rss_form_settings['author_name'] ?? 0;
        $vendor_id = $wams_vendor_on_rss_form_settings['vendor_id'] ?? 0;
        // $wams_vendor_form_settings = get_option('wams_vendor_form_settings');
        // $vendor_name_field_id = $wams_vendor_form_settings['vendor_name'] ?? 0;
        if ($vendor_name_on_rss_form_id && $author_name_field && $vendor_id) {
            $existing_entries = $this->get_entry_if_exists($vendor_name_on_rss_form_id, $author, $author_name_field);
            if (!is_wp_error($existing_entries)) {
                $vendor_id = rgar($existing_entries[0], $vendor_id);
                return (!is_null($vendor_id)) ? $vendor_id : false;
            }
        } else {
            return false;
        }
    }


    /**
     * get vendor ID for Author
     *
     * @param string  domain host Name
     * @return array project details [code,project_manager]
     */
    public function get_project_data($domain)
    {
        $project = [];
        $forms = get_option('wams_forms_settings');
        $project_form = $forms['project_form'];
        $domain_form = $forms['domain_form'];
        $wams_project_form_settings = get_option('wams_project_form_settings');
        $wams_domain_form_settings = get_option('wams_domain_form_settings');
        $project_name_field_id = $wams_project_form_settings['project_name'] ?? 1;
        $project_code_field_id = $wams_project_form_settings['project_code'] ?? 6;
        $project_manager_field_id = $wams_project_form_settings['project_manager'] ?? 4;
        $host_name_field_id = $wams_domain_form_settings['host_name'] ?? 6;
        $domain_project_field_id = $wams_domain_form_settings['domain_project'] ?? 8;
        $domain = $this->get_entry_if_exists($domain_form, $domain, $host_name_field_id);
        if ($domain) {

            $_project = rgar($domain, $domain_project_field_id);
            $project_data = $this->get_entry_if_exists($project_form, $_project, $project_code_field_id);
            if ($project_data) {
                $project['name'] = rgar($project_data, $project_name_field_id);
                $project['code'] = rgar($project_data, $project_code_field_id);
                $project['project_manager'] = rgar($project_data, $project_manager_field_id);
            }
        }
        return $project;
    }

    /**
     * Check if enry exists
     * @return  array|bool  first as array or false if not fount
     */
    private function get_entry_if_exists($form_id, $value_to_search, $match_field)
    {
        $search_criteria  = array(
            'status' => 'active',
            'field_filters' => array(
                array(
                    'key'   => $match_field,  // Original Client ID Field ID in Add New Clients Form
                    'value' => $value_to_search,
                )
            )
        );
        return GFAPI::get_entries($form_id, $search_criteria);
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
    public function get_url_title($url)
    {
        $title = WAMS()->web_page_parser()->getTitle($url);
        if (isset($title['title'])) return $title;
        return 'N/A';
    }
    public function get_url_status($url)
    {
        // Return the extracted meta data
        $status = '';
        $status_code = WAMS()->web_page_parser()->getStatusCode($url);
        switch ($status_code) {
            case '200':
                $status = __('URL is Valid ', 'wams');
                break;
            case '404':
                $status = __('URL Not Found!', 'wams');
                break;

            default:
                $status = __('Could Not Get URL Data! ', 'wams');
                break;
        }
        return $status;
    }
}
