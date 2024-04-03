<?php

namespace wams\core\google;

require_once WAMS_PATH . 'includes/lib/vendor/autoload.php';

use Google;
use GuzzleHttp;
use Exception;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\Analytics;
use Google\Service\AnalyticsReporting;
use Google\Service\GoogleAnalyticsAdmin;
// use Google\Service\AnalyticsData;


use DateTimeZone;
use DateTime;
use DatePeriod;
use DateInterval;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\core\google\GA_Api_Controller')) {

    final class GA_Api_Controller
    {

        public $client;

        public $service;

        public $service_ga3_reporting;

        public $service_ga4_admin;

        public $service_ga4_data;

        public $timeshift;

        public $quotauser;

        public $ga_config;

        public function __construct()
        {

            $this->ga_config = new GA_Config;
            $this->client = new Google\Client();
            $httpClient = new GuzzleHttp\Client();
            $this->client->setHttpClient($httpClient);
            $this->client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));
            $this->client->setAccessType('offline');
            $this->client->setApprovalPrompt('force');
            $this->client->setApplicationName('WAMS ' . WAMS_VERSION);
            $state_uri = admin_url('admin.php?page=wams_ga');

            $this->client->setState($state_uri);
            $this->quotauser = 'u' . get_current_user_id() . 's' . get_current_blog_id();
            if ($this->ga_config->options['user_api'] == 1) {
                $this->client->setClientId($this->ga_config->options['client_id']);
                $this->client->setClientSecret($this->ga_config->options['client_secret']);
            } else {
                $this->client->setClientId('566561265895-9dvkhl2f55vnklnrcthu5835qrr0usu0.apps.googleusercontent.com');
                $this->client->setClientSecret('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');
            }
            // $this->client->setRedirectUri(WAMS_PLUGIN_URL . 'tools/oauth2callback.php');
            $this->client->setRedirectUri('https://www.rakami.net/wams-plugin/oauth2callback.php');



            /**
             *  Endpoint support
             */
            if ($this->ga_config->options['token']) {
                $token = $this->ga_config->options['token'];
                // print_r($token->refresh_token);
                if ($token) {
                    try {
                        $array_token = (array)$token;
                        $this->client->setAccessToken($array_token);
                        if ($this->client->isAccessTokenExpired() && isset($token->refresh_token)) {
                            echo $this->client->getRefreshToken();
                            $creds = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                            if ($creds && isset($creds['access_token'])) {
                                $this->ga_config->options['token'] = $this->client->getAccessToken();
                            } else {
                                echo 'error';
                                $timeout = $this->get_timeouts('midnight');
                                GA_Tools::set_error($creds, $timeout);
                                if (isset($creds['error']) && 'invalid_grant' == $creds['error']) {
                                    $this->reset_token(true);
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $timeout = $this->get_timeouts('midnight');
                        GA_Tools::set_error($e, $timeout);
                        $this->reset_token(true);
                    }
                    $this->ga_config->set_plugin_options();
                }
            }

            // $this->service = new Google\Service\Analytics($this->client);

            $this->service_ga4_admin = new \Google\Service\GoogleAnalyticsAdmin($this->client);

            $this->service_ga4_data = new \Google\Service\AnalyticsData($this->client);
        }


        public function authenticate($access_code)
        {

            try {
                $this->client->fetchAccessTokenWithAuthCode($access_code);
                return $this->client->getAccessToken();
            } catch (GoogleServiceException $e) {
                $timeout = $this->get_timeouts('midnight');
                GA_Tools::set_error($e, $timeout);
            } catch (Exception $e) {
                $timeout = $this->get_timeouts('midnight');
                GA_Tools::set_error($e, $timeout);
            }
        }

        /**
         * Handles the token reset process
         *
         * @param
         *            $all
         */
        public function reset_token($all = false)
        {

            $token = $this->client->getAccessToken();

            if (!empty($token)) {
                try {
                    $this->client->revokeToken($token);
                } catch (Exception $e) {
                    $timeout = $this->get_timeouts('midnight');
                    GA_Tools::set_error($e, $timeout);
                }
            }

            if ($all) {
                $this->ga_config->options['site_jail'] = "";
                $this->ga_config->options['sites_list'] = array();
                $this->ga_config->options['ga4_profiles_list'] = array();
                $this->ga_config->options['default_profile'] = '';
                $this->ga_config->options['reporting_type'] = 0;
            }

            $this->ga_config->options['token'] = "";
            $this->ga_config->options['sites_list_locked'] = 0;
            $this->ga_config->set_plugin_options();
            // echo 'reset token done';
        }

        /**
         * Handles errors returned by GAPI Library
         *
         * @return boolean
         */
        public function gapi_errors_handler()
        {

            $errors = GA_Tools::get_cache('gapi_errors');

            //Proceed as normal if we don't know the error
            if (false === $errors || !isset($errors[0])) {
                return false;
            }

            //Reset the token since these are unrecoverable errors and need user intervention
            if (isset($errors[1][0]['reason']) && ('invalidParameter' == $errors[1][0]['reason'] || 'badRequest' == $errors[1][0]['reason'] || 'invalidCredentials' == $errors[1][0]['reason'] || 'insufficientPermissions' == $errors[1][0]['reason'] || 'required' == $errors[1][0]['reason'])) {
                //  $this->reset_token();
                return true;
            }

            if (400 == $errors[0] || 401 == $errors[0]) {
                //$this->reset_token();
                return true;
            }

            //Backoff processing until the error timeouts, usually at midnight
            if (isset($errors[1][0]['reason']) && ('dailyLimitExceeded' == $errors[1][0]['reason'] || 'userRateLimitExceeded' == $errors[1][0]['reason'] || 'rateLimitExceeded' == $errors[1][0]['reason'] || 'quotaExceeded' == $errors[1][0]['reason'])) {
                return true;
            }

            /** Back-off system for subsequent requests - an Auth error generated after a Service request
             *  The native back-off system for Service requests is covered by the GAPI PHP Client
             */
            if (isset($errors[1][0]['reason']) && ('authError' == $errors[1][0]['reason'])) {
                if ($this->ga_config->options['api_backoff'] <= 5) {
                    usleep($this->ga_config->options['api_backoff'] * 1000000 + rand(100000, 1000000));
                    $this->ga_config->options['api_backoff'] = $this->ga_config->options['api_backoff'] + 1;
                    $this->ga_config->set_plugin_options();
                    return false;
                } else {
                    return true;
                }
            }

            if (500 == $errors[0] || 503 == $errors[0] || $errors[0] < -50) {
                return true;
            }

            return false;
        }

        /**
         * Calculates proper timeouts for each GAPI query
         *
         * @param
         *            $interval
         * @return number
         */
        public function get_timeouts($interval = '')
        {
            $local_time = time() + $this->timeshift;
            if ('daily' == $interval) {
                $nextday = explode('-', date('n-j-Y', strtotime(' +1 day', $local_time)));
                $midnight = mktime(0, 0, 0, $nextday[0], $nextday[1], $nextday[2]);
                return $midnight - $local_time;
            } else if ('midnight' == $interval) {
                $midnight = strtotime("tomorrow 00:00:00"); // UTC midnight
                $midnight = $midnight + 8 * 3600; // UTC 8 AM
                return $midnight - time();
            } else if ('hourly' == $interval) {
                $nexthour = explode('-', date('H-n-j-Y', strtotime(' +1 hour', $local_time)));
                $newhour = mktime($nexthour[0], 0, 0, $nexthour[1], $nexthour[2], $nexthour[3]);
                return $newhour - $local_time;
            } else {
                $newtime = strtotime(' +5 minutes', $local_time);
                return $newtime - $local_time;
            }
        }

        /**
         * Retrieves all Google Analytics 4 Properties with details
         *
         * @return array
         */
        public function refresh_profiles_ga4()
        {
            try {
                $ga4_webstreams_list = array();

                $accounts = $this->service_ga4_admin->accountSummaries->listAccountSummaries(array("pageSize" => 200))->getAccountSummaries();

                if (!empty($accounts)) {
                    foreach ($accounts as $account) {
                        $properties = $account->getPropertySummaries();
                        if (!empty($properties)) {
                            foreach ($properties as $property) {
                                $datastreams = $this->service_ga4_admin->properties_dataStreams->listPropertiesDataStreams($property->getProperty())->getDataStreams();

                                if (!empty($datastreams)) {
                                    foreach ($datastreams as $datastream) {
                                        $webstream = $datastream->getWebStreamData();
                                        if ('WEB_DATA_STREAM' == $datastream->type) {
                                            $ga4_webstreams_list[] = array($datastream->getDisplayName(), $datastream->getName(), $webstream->getDefaultUri(), $webstream->getMeasurementId());
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                return $ga4_webstreams_list;
            } catch (GoogleServiceException $e) {
                $timeout = $this->get_timeouts('midnight');
                GA_Tools::set_error($e, $timeout);
            } catch (Exception $e) {
                $timeout = $this->get_timeouts('midnight');
                GA_Tools::set_error($e, $timeout);
            }
        }

        /**
         * Generates serials for transients
         *
         * @param
         *            $serial
         * @return string
         */
        public function get_serial($serial)
        {
            return sprintf("%u", crc32($serial));
        }

        /**
         * Google Analtyics 4 Reports Get and cache
         *
         * @param
         *            $projecId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $metrics
         * @param
         *            $options
         * @param
         *            $serial
         * @return int|Google\Service\AnalyticsReporting\DateRangeValues
         */
        private function handle_corereports_ga4($projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial)
        {
            try {
                if ('today' == $from) {
                    $interval = 'hourly';
                } else {
                    $interval = 'daily';
                }
                $transient = GA_Tools::get_cache($serial);
                if (false === $transient) {
                    if ($this->gapi_errors_handler()) {
                        // return -23;
                    }

                    $projectIdArr = explode('/dataStreams/', $projectId);
                    $projectId = $projectIdArr[0];

                    $quotauser = $this->get_serial($this->quotauser . $projectId);

                    // Create the DateRange object.
                    $dateRange = new Google\Service\AnalyticsData\DateRange();
                    $dateRange->setStartDate($from);
                    $dateRange->setEndDate($to);

                    // Create the Metrics object.
                    if (is_array($metrics)) {
                        foreach ($metrics as $value) {
                            $value = GA_Tools::ga3_ga4_mapping($value);
                            $metricobj = new Google\Service\AnalyticsData\Metric();
                            $metricobj->setName($value);
                            $metric[] = $metricobj;
                        }
                    } else {
                        $metrics = GA_Tools::ga3_ga4_mapping($metrics);
                        $metricobj = new Google\Service\AnalyticsData\Metric();
                        $metricobj->setName($metrics);
                        $metric[] = $metricobj;
                    }

                    // Create the ReportRequest object.
                    $request = new Google\Service\AnalyticsData\RunReportRequest();
                    $request->setProperty($projectId);
                    $request->setDateRanges($dateRange);
                    $request->setMetrics($metric);
                    $request->setMetricAggregations('TOTAL');
                    $request->setKeepEmptyRows(true);

                    // Create the Dimensions object.
                    if ($dimensions) {

                        if (is_array($dimensions)) {
                            foreach ($dimensions as $value) {
                                $value = GA_Tools::ga3_ga4_mapping($value);
                                $dimensionobj = new Google\Service\AnalyticsData\Dimension();
                                $dimensionobj->setName($value);
                                $dimension[] = $dimensionobj;
                            }
                        } else {
                            $dimensions = GA_Tools::ga3_ga4_mapping($dimensions);
                            $dimensionobj = new Google\Service\AnalyticsData\Dimension();
                            $dimensionobj->setName($dimensions);
                            $dimension[] = $dimensionobj;
                        }

                        $request->setDimensions($dimension);
                    }

                    // Create the Filters
                    if ($filters) {

                        $dimensionFilterExpression = array();

                        foreach ($filters as $value) {
                            $dimensionFilter = new Google\Service\AnalyticsData\Filter();
                            $stringFilter = new Google\Service\AnalyticsData\StringFilter();
                            $value[0] = GA_Tools::ga3_ga4_mapping($value[0]);
                            $dimensionFilter->setFieldName($value[0]);
                            $stringFilter->setValue($value[2]);
                            $stringFilter->setMatchType($value[1]);
                            $dimensionFilter->setStringFilter($stringFilter);

                            $dimensionFilterExpressionobj = new Google\Service\AnalyticsData\FilterExpression();
                            $notexpr = new Google\Service\AnalyticsData\FilterExpression();

                            if ($value[3]) {
                                $dimensionFilterExpressionobj->setFilter($dimensionFilter);
                                $notexpr->setNotExpression($dimensionFilterExpressionobj);
                                $dimensionFilterExpression[] = $notexpr;
                            } else {
                                $dimensionFilterExpressionobj->setFilter($dimensionFilter);
                                $dimensionFilterExpression[] = $dimensionFilterExpressionobj;
                            }
                        }

                        $dimensionFilterExpressionList = new Google\Service\AnalyticsData\FilterExpressionList();
                        $dimensionFilterExpressionList->setExpressions($dimensionFilterExpression);

                        $dimensionFilterExpressionobj = new Google\Service\AnalyticsData\FilterExpression();
                        if (count($dimensionFilterExpression) > 1) {
                            $dimensionFilterExpressionobj->setAndGroup($dimensionFilterExpressionList);
                        } else {
                            $dimensionFilterExpressionobj = $dimensionFilterExpression[0];
                        }

                        $request->setDimensionFilter($dimensionFilterExpressionobj);
                    }

                    // Create the Ordering.
                    if ($sortby) {
                        $ordering = new Google\Service\AnalyticsData\OrderBy();
                        $metrics = GA_Tools::ga3_ga4_mapping($metrics);
                        $metricOrderBy = new Google\Service\AnalyticsData\MetricOrderBy();
                        $metricOrderBy->setMetricName($metrics);
                        $ordering->setMetric($metricOrderBy);
                        $ordering->setDesc(true);
                        $request->setOrderBys($ordering);
                    } else {
                        if (isset($dimension[0])) {
                            $dimensionOrderBy = new Google\Service\AnalyticsData\DimensionOrderBy();
                            $dimensionOrderBy->setDimensionName($dimension[0]->getName());
                            $dimensionOrderBy->setOrderType('NUMERIC');
                            $ordering = new Google\Service\AnalyticsData\OrderBy();
                            $ordering->setDimension($dimensionOrderBy);
                            $ordering->setDesc(false);
                            $request->setOrderBys($ordering);
                        }
                    }

                    $response = $this->service_ga4_data->properties->runReport($projectId, $request, array('quotaUser' => $quotauser));
                    $dataraw = $response;

                    $data['values'] = array();

                    foreach ($dataraw->getRows() as $row) {

                        $values = array();

                        if (isset($row->getDimensionValues()[0])) {
                            foreach ($row->getDimensionValues() as $item) {
                                $values[] = $item->getValue();
                            }
                        }

                        if (isset($row->getMetricValues()[0])) {
                            foreach ($row->getMetricValues() as $item) {
                                $values[] = $item->getValue();
                            }
                        }

                        $data['values'][] = $values;
                    }

                    $data['totals'] = 0;

                    if (method_exists($dataraw, 'getTotals') && isset($dataraw->getTotals()[0]->getMetricValues()[0])) {
                        $data['totals'] = $dataraw->getTotals()[0]->getMetricValues()[0]->getValue();
                    }

                    GA_Tools::set_cache($serial, $data, $this->get_timeouts($interval));
                } else {
                    $data = $transient;
                }
            } catch (GoogleServiceException $e) {
                $timeout = $this->get_timeouts('midnight');
                GA_Tools::set_error($e, $timeout);
                return $e->getCode();
            } catch (Exception $e) {
                $timeout = $this->get_timeouts('midnight');
                GA_Tools::set_error($e, $timeout);
                return $e->getCode();
            }
            $this->ga_config->options['api_backoff'] = 0;
            $this->ga_config->set_plugin_options();

            return $data;
        }

        /**
         * Google Analytics 4 data for Area Charts (Admin Dashboard Widget report)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $query
         * @param
         *            $filter
         * @return array|int
         */
        private function get_areachart_data_ga4($projectId, $from, $to, $query, $filter = '')
        {

            $factor = 1;

            switch ($query) {
                case 'users':
                    $title = __("Users", 'wams');
                    break;
                case 'pageviews':
                    $title = __("Page Views", 'wams');
                    break;
                case 'visitBounceRate':
                    $title = __("Bounce Rate", 'wams');
                    $factor = 100;
                    break;
                case 'organicSearches':
                    $title = __("Engaged Sessions", 'wams');
                    break;
                case 'uniquePageviews':
                    $title = __("Unique Page Views", 'wams');
                    break;
                default:
                    $title = __("Sessions", 'wams');
            }

            $metrics = 'ga:' . $query;

            if ('today' == $from || 'yesterday' == $from) {
                $dimensions = 'ga:hour';
                $dayorhour = __("Hour", 'wams');
            } else if ('365daysAgo' == $from || '1095daysAgo' == $from) {
                $dimensions = array(
                    'ga:year',
                    'ga:month'
                );
                $dayorhour = __("Date", 'wams');
            } else {
                $dimensions = array(
                    'ga:date',
                    'ga:dayOfWeekName'
                );
                $dayorhour = __("Date", 'wams');
            }

            $filters = false;

            if ($filter) {
                $filters[] = array('ga:pagePath', 'EXACT', $filter, false);
            }

            $serial = 'qr2_' . $this->get_serial($projectId . $from . $to . $metrics . $filter);

            $data = $this->handle_corereports_ga4($projectId, $from, $to, $metrics, $dimensions, false, $filters, $serial);

            if (is_numeric($data)) {
                return $data;
            }

            if (empty($data['values'])) {
                // unable to render it as an Area Chart, returns a numeric value to be handled by reportsx.js
                return -21;
            }

            $aiwp_data = array(array($dayorhour, $title));
            if ('today' == $from || 'yesterday' == $from) {

                for ($i = 0; $i < 24; $i++) {
                    $fill_data[$i] = 0;
                }
                foreach ($data['values'] as $row) {
                    $fill_data[(int) $row[0]] = round($row[1], 2) * $factor;
                }
                foreach ($fill_data as $key => $value) {
                    $aiwp_data[] = array($key . ':00', $value);
                }
            } else if ('365daysAgo' == $from || '1095daysAgo' == $from) {

                $yesterday = date("Y-m-d", strtotime("-1 day"));
                $offset = str_replace('daysAgo', '', $from);
                $xdaysago =  date("Y-m-d", strtotime("-" . $offset . " day"));

                $period = new DatePeriod(
                    new DateTime($xdaysago),
                    new DateInterval('P1M'),
                    new DateTime($yesterday)
                );

                foreach ($period as $key => $value) {
                    $fill_data[$value->format('Ym')] = 0;
                }

                foreach ($data['values'] as $row) {
                    $key = $row[0] . $row[1];
                    $fill_data[$key] = round($row[2], 2) * $factor;
                }

                foreach ($fill_data as $key => $value) {
                    /*
					 * translators:
					 * Example: 'F, Y' will become 'November, 2015'
					 * For details see: http://php.net/manual/en/function.date.php#refsect1-function.date-parameters
					 */
                    $aiwp_data[] = array(date_i18n(__('F, Y', 'wams'), strtotime($key . '01')), $value);
                }
            } else {

                $yesterday = date("Y-m-d", strtotime("-1 day"));
                $offset = str_replace('daysAgo', '', $from);
                $xdaysago =  date("Y-m-d", strtotime("-" . $offset . " day"));

                $period = new DatePeriod(
                    new DateTime($xdaysago),
                    new DateInterval('P1D'),
                    new DateTime($yesterday)
                );

                foreach ($period as $key => $value) {
                    $fill_data[$value->format('Ymd')] = 0;
                }

                foreach ($data['values'] as $row) {
                    $fill_data[$row[0]] = round($row[2], 2) * $factor;
                }

                foreach ($fill_data as $key => $value) {
                    /*
					 * translators:
					 * Example: 'l, F j, Y' will become 'Thusday, November 17, 2015'
					 * For details see: http://php.net/manual/en/function.date.php#refsect1-function.date-parameters
					 */
                    $aiwp_data[] = array(date_i18n(__('l, F j, Y', 'wams'), strtotime($key)), $value);
                }
            }

            return $aiwp_data;
        }
        /**
         * Google Analytics 4 data for Page Title
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $filter
         * @return array|int
         */
        private function get_title_ga4($projectId, $from, $to, $filter = '')
        {
            $metrics = array(
                'ga:sessions',
                'ga:users',
                'ga:pageviews',
                'engagementRate',
                'userEngagementDuration',
            );

            $dimensions = 'ga:pageTitle';

            $filters = false;
            if ($filter) {
                $filters[] = array('ga:pageTitle', 'CONTAINS', $filter, false);
            }

            $serial = 'qr11_' . $this->get_serial($projectId . $from . $to . $filter);

            $data =  $this->handle_corereports_ga4($projectId, $from, $to, $metrics, $dimensions, false, $filters, $serial);


            if (is_numeric($data)) {
                return $data;
            }

            $ga4_data = array();

            foreach ($data['values'] as $row) {
                $ga4_data[] = [
                    'Title' => isset($row[0]) ? $row[0] : 'No Title',
                    'Sessions' => isset($row[1]) ? number_format_i18n($row[1]) : 0,
                    'Users' => isset($row[2]) ? number_format_i18n($row[2]) : 0,
                    'Pageviews' => isset($row[3]) ? number_format_i18n($row[3]) : 0,
                    'Engagement Rate' => isset($row[4]) ? number_format_i18n($row[4] * 100, 2) . '%' : '0%',
                    'User Engagement Duration' => isset($row[5]) ? GA_Tools::secondstohms($row[5]) : '00:00:00',
                ];
            }


            return $ga4_data;
        }
        /**
         * Google Analytics 4 data for Bottom Stats (bottom stats on main report)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $filter
         * @return array|int
         */
        private function get_bottomstats_ga4($projectId, $from, $to, $filter = '')
        {

            $filters = false;

            if ($filter) {
                // $filters[] = array('ga:pageTitle', 'PARTIAL', $filter, false);
                $filters[] = array('ga:pagePath', 'EXACT', $filter, true);
                $metrics = array(
                    'ga:sessions',
                    'ga:users',
                    'ga:pageviews',
                    'ga:BounceRate',
                    'ga:pageviewsPerSession',
                    'engagedSessions',
                    'engagementRate',
                    'userEngagementDuration',
                );
            } else {
                $metrics = array(
                    'ga:sessions',
                    'ga:users',
                    'ga:pageviews',
                    'ga:BounceRate',
                    'ga:pageviewsPerSession',
                    'engagedSessions',
                    'engagementRate',
                    'userEngagementDuration',
                );
            }
            $sortby = false;
            $serial = 'qr3_' . $this->get_serial($projectId . $from . $to . $filter);
            $data = $this->handle_corereports_ga4($projectId, $from, $to, $metrics, false, $sortby, $filters, $serial);

            if (is_numeric($data)) {
                return $data;
            }

            $aiwp_data = array();

            $aiwp_data = $data['values'][0];

            // i18n support
            $aiwp_data[0] = isset($aiwp_data[0]) ? number_format_i18n($aiwp_data[0]) : 0;
            $aiwp_data[1] = isset($aiwp_data[1]) ? number_format_i18n($aiwp_data[1]) : 0;
            $aiwp_data[2] = isset($aiwp_data[2]) ? number_format_i18n($aiwp_data[2]) : 0;
            $aiwp_data[3] = isset($aiwp_data[3]) ? number_format_i18n($aiwp_data[3] * 100, 2) . '%' : '0%';
            $aiwp_data[4] = isset($aiwp_data[4]) ? GA_Tools::secondstohms($aiwp_data[4]) : '00:00:00';
            $aiwp_data[5] = isset($aiwp_data[5]) ? number_format_i18n($aiwp_data[5], 2) : 0;
            $aiwp_data[6] = isset($aiwp_data[6]) ? number_format_i18n($aiwp_data[6]) : 0;
            $aiwp_data[7] = isset($aiwp_data[7]) ? number_format_i18n($aiwp_data[7] * 100, 2) . '%' : '0%';
            $aiwp_data[8] = isset($aiwp_data[8]) ? GA_Tools::secondstohms($aiwp_data[8]) : '00:00:00';

            return $aiwp_data;
        }

        /**
         * Google Analytics 4 data for Table Charts (location reports)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $filter
         * @return array|int
         */
        private function get_locations_ga4($projectId, $from, $to, $metric, $filter = '')
        {

            $metrics = 'ga:' . $metric;

            $title = __("Countries", 'wams');

            $serial = 'qr7_' . $this->get_serial($projectId . $from . $to . $filter . $metric);

            $dimensions = 'ga:country';

            $local_filter = '';

            if ($this->ga_config->options['ga_target_geomap']) {
                $dimensions = array(
                    'ga:city',
                    'ga:region'
                );

                $country_codes = GA_Tools::get_countrycodes();
                if (isset($country_codes[$this->ga_config->options['ga_target_geomap']])) {
                    $local_filter = array('ga:country', 'EXACT', ($country_codes[$this->ga_config->options['ga_target_geomap']]), false);
                    $title = __("Cities from", 'wams') . ' ' . __($country_codes[$this->ga_config->options['ga_target_geomap']]);
                    $serial = 'qr7_' . $this->get_serial($projectId . $from . $to . $this->ga_config->options['ga_target_geomap'] . $filter . $metric);
                }
            }

            $sortby = '-' . $metrics;

            $filters = false;
            if ($filter) {
                $filters[] = array('ga:pagePath', 'EXACT', $filter, false);
                if ($local_filter) {
                    $filters[] = array('ga:pagePath', 'EXACT', $filter, false);
                    $filters[1] = $local_filter;
                }
            } else {
                if ($local_filter) {
                    $filters[] = $local_filter;
                }
            }

            $data =  $this->handle_corereports_ga4($projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial);

            if (is_numeric($data)) {
                return $data;
            }

            $aiwp_data = array(array($title, __(ucfirst($metric), 'wams')));

            foreach ($data['values'] as $row) {
                if (isset($row[2])) {
                    $aiwp_data[] = array(esc_html($row[0]) . ', ' . esc_html($row[1]), (int) $row[2]);
                } else {
                    $aiwp_data[] = array(esc_html($row[0]), (int) $row[1]);
                }
            }

            return $aiwp_data;
        }

        /**
         * Google Analytics 4 data for Table Charts (content pages)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $filter
         * @return array|int
         */
        private function get_contentpages_ga4($projectId, $from, $to, $metric, $filter = '')
        {

            $metrics = 'ga:' . $metric;

            $dimensions = 'ga:pageTitle';

            $sortby = '-' . $metrics;

            $filters = false;
            if ($filter) {
                $filters[] = array('ga:pageTitle', '=~', $filter, false);
            }

            $serial = 'qr4_' . $this->get_serial($projectId . $from . $to . $filter . $metric);

            $data =  $this->handle_corereports_ga4($projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial);

            if (is_numeric($data)) {
                return $data;
            }

            $aiwp_data = array(array(__("Pages", 'wams'), __(ucfirst($metric), 'wams')));

            foreach ($data['values'] as $row) {
                $aiwp_data[] = array(esc_html($row[0]), (int) $row[1]);
            }

            return $aiwp_data;
        }

        /**
         * Google Analytics 4 data for Org Charts (traffic channels, device categories)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $query
         * @param
         *            $filter
         * @return array|int
         */
        private function get_orgchart_data_ga4($projectId, $from, $to, $query, $metric, $filter = '')
        {

            $metrics = 'ga:' . $metric;

            $dimensions = 'ga:' . $query;

            $sortby = '-' . $metrics;


            $filters = false;
            if ($filter) {
                $filters[] = array('ga:pagePath', 'EXACT', $filter, false);
            }

            $serial = 'qr8_' . $this->get_serial($projectId . $from . $to . $query . $filter . $metric);

            $data =  $this->handle_corereports_ga4($projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial);

            if (is_numeric($data)) {
                return $data;
            }

            if (empty($data['values'])) {
                // unable to render as an Org Chart, returns a numeric value to be handled by reportsx.js
                return -21;
            }

            $block = ('channelGrouping' == $query) ? __("Channels", 'wams') : __("Devices", 'wams');
            $aiwp_data = array(array('<div style="color:black; font-size:1.1em">' . $block . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $data['totals'] . '</div>', ""));
            foreach ($data['values'] as $row) {
                $shrink = explode(" ", $row[0]);
                if (isset($shrink[1])) {
                    $shrink[0] = esc_html($shrink[0]) . '<br>' . esc_html($shrink[1]);
                }
                if ('Unassigned' !== $shrink[0]) {
                    $aiwp_data[] = array('<div style="color:black; font-size:1.1em">' . $shrink[0] . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $row[1] . '</div>', '<div style="color:black; font-size:1.1em">' . $block . '</div><div style="color:darkblue; font-size:1.2em">' . (int) $data['totals'] . '</div>');
                }
            }

            return $aiwp_data;
        }

        /**
         * Google Analytics 4 data for Table Charts (referrers)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $filter
         * @return array|int
         */
        private function get_referrers_ga4($projectId, $from, $to, $metric, $filter = '')
        {

            $metrics = 'ga:' . $metric;

            $dimensions = 'ga:source';

            $sortby = '-' . $metrics;

            $filters = false;
            if ($filter) {
                $filters[] = array('ga:pagePath', 'EXACT', $filter, false);
                $filters[] = array('ga:medium', 'EXACT', 'referral', false);
            } else {
                $filters[] = array('ga:medium', 'EXACT', 'referral', false);
            }

            $serial = 'qr5_' . $this->get_serial($projectId . $from . $to . $filter . $metric);

            $data =  $this->handle_corereports_ga4($projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial);

            if (is_numeric($data)) {
                return $data;
            }

            $aiwp_data = array(array(__("Referrers", 'wams'), __(ucfirst($metric), 'wams')));

            foreach ($data['values'] as $row) {
                $aiwp_data[] = array(esc_html($row[0]), (int) $row[1]);
            }

            return $aiwp_data;
        }

        /**
         * Google Analytics 4 data for Table Charts (searches)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $filter
         * @return array|int
         */
        private function get_searches_ga4($projectId, $from, $to, $metric, $filter = '')
        {

            $metrics = 'ga:' . $metric;

            $dimensions = 'ga:source';

            $sortby = '-' . $metrics;

            $filters = false;
            if ($filter) {
                $filters[] = array('ga:pagePath', 'EXACT', $filter, false);
                $filters[] = array('ga:medium', 'EXACT', 'organic', false);
            } else {
                $filters[] = array('ga:medium', 'EXACT', 'organic', false);
            }

            $serial = 'qr6_' . $this->get_serial($projectId . $from . $to . $filter . $metric);

            $data =  $this->handle_corereports_ga4($projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial);

            if (is_numeric($data)) {
                return $data;
            }

            $aiwp_data = array(array(__("Search Engines", 'wams'), __(ucfirst($metric), 'wams')));
            foreach ($data['values'] as $row) {
                $aiwp_data[] = array(esc_html($row[0]), (int) $row[1]);
            }

            return $aiwp_data;
        }

        /**
         * Google Analytics 4 data for Pie Charts (traffic mediums, serach engines, languages, browsers, screen rsolutions, etc.)
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @param
         *            $query
         * @param
         *            $filter
         * @return array|int
         */
        private function get_piechart_data_ga4($projectId, $from, $to, $query, $metric, $filter = '')
        {

            $metrics = 'ga:' . $metric;
            $dimensions = 'ga:' . $query;
            $sortby =  false;
            $filters = false;

            if ('source' == $query) {
                $sortby = '-' . $metrics;
                if ($filter) {
                    $filters[] = array('ga:pagePath', 'EXACT', $filter, false);
                    $filters[] = array('ga:medium', 'EXACT', 'organic', false);
                } else {
                    $filters[] = array('ga:medium', 'EXACT', 'organic', false);
                }
            } else {
                $sortby = '-' . $metrics;
                if ($filter) {
                    $filters[] = array('ga:pagePath', 'EXACT', $filter, false);
                    $filters[] = array('ga:' . $query, 'EXACT', '(not set)', true);
                } else {
                    $filters[] = array('ga:' . $query, 'EXACT', '(not set)', true);
                }
            }

            $serial = 'qr10_' . $this->get_serial($projectId . $from . $to . $query . $filter . $metric);

            $data =  $this->handle_corereports_ga4($projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial);

            if (is_numeric($data)) {
                return $data;
            }

            $aiwp_data = array(array(__("Type", 'wams'), __(ucfirst($metric), 'wams')));

            $included = 0;
            foreach ($data['values'] as $row) {
                $aiwp_data[] = array(str_replace("(none)", "direct", esc_html($row[0])), (int) $row[1]);
                $included += $row[1];
            }

            $totals = $data['totals'];
            $others = $totals - $included;
            if ($others > 0) {
                $aiwp_data[] = array(__('Other', 'wams'), $others);
            }

            return $aiwp_data;
        }

        /**
         * Google Analytics 4 data for 404 Errors
         *
         * @param
         *            $projectId
         * @param
         *            $from
         * @param
         *            $to
         * @return array|int
         */
        private function get_404errors_ga4($projectId, $from, $to, $metric, $filter = "Page Not Found")
        {

            $metrics = 'ga:' . $metric;

            $dimensions = array(
                'ga:pagePath',
                'ga:fullReferrer'
            );

            $sortby = '-' . $metrics;

            $filters[] = array('ga:pageTitle', 'PARTIAL', $filter, false);

            $serial = 'qr4_' . $this->get_serial($projectId . $from . $to . $filter . $metric);

            $data =  $this->handle_corereports_ga4($projectId, $from, $to, $metrics, $dimensions, $sortby, $filters, $serial);

            if (is_numeric($data)) {
                return $data;
            }

            $aiwp_data = array(array(__("404 Errors", 'wams'), __(ucfirst($metric), 'wams')));
            foreach ($data['values'] as $row) {
                $path = esc_html($row[0]);
                $source = esc_html($row[1]);
                $aiwp_data[] = array("<strong>" . __("URI:", 'wams') . "</strong> " . $path . "<br><strong>" . __("Source:", 'wams') . "</strong> " . $source, (int) $row[2]);
            }

            return $aiwp_data;
        }

        /**
         * Google Analytics 4 data for Frontend Widget (chart data and totals)
         *
         * @param
         *            $projectId
         * @param
         *            $period
         * @param
         *            $anonim
         * @return array|int
         */
        public function frontend_widget_stats_ga4($projectId, $from, $anonim)
        {

            $to = 'yesterday';
            $metrics = 'ga:sessions';
            $dimensions = array(
                'ga:date',
                'ga:dayOfWeekName'
            );

            $serial = 'qr2_' . $this->get_serial($projectId . $from . $to . $metrics);


            $data =  $this->handle_corereports_ga4($projectId, $from, $to, $metrics, $dimensions, false, false, $serial);

            if (is_numeric($data)) {
                return $data;
            }

            $aiwp_data = array(array(__("Date", 'wams'), __("Sessions", 'wams')));

            if ($anonim) {
                $max_array = array();
                foreach ($data['values'] as $row) {
                    $max_array[] = $row[2];
                }
                $max = max($max_array) ? max($max_array) : 1;
            }

            foreach ($data['values'] as $row) {
                $aiwp_data[] = array(date_i18n(__('l, F j, Y', 'wams'), strtotime($row[0] . ',' . $row[1])), ($anonim ? round($row[2] * 100 / $max, 2) : (int) $row[2]));
            }
            $totals = $data['totals'];

            return array($aiwp_data, $anonim ? 0 : number_format_i18n($totals));
        }

        /**
         * Google Analytics 4 data for Realtime component (the real-time report)
         *
         * @param
         *            $projectId
         * @return array|int
         */
        private function get_realtime_ga4($projectId)
        {
            $metrics = 'activeUsers';
            $dimensions = array('deviceCategory', 'unifiedScreenName');

            $projectIdArr = explode('/dataStreams/', $projectId);
            $projectId = $projectIdArr[0];

            $quotauser = $this->get_serial($this->quotauser . $projectId);

            try {
                $serial = 'qr_realtimecache_' . $this->get_serial($projectId);
                $transient = GA_Tools::get_cache($serial);
                if (false === $transient) {

                    if ($this->gapi_errors_handler()) {
                        // return -23;
                    }

                    $request = new Google\Service\AnalyticsData\RunRealtimeReportRequest();

                    // Create the Metrics object.
                    $metrics = GA_Tools::ga3_ga4_mapping($metrics);
                    $metricobj = new Google\Service\AnalyticsData\Metric();
                    $metricobj->setName($metrics);
                    $metric[] = $metricobj;

                    // Create the ReportRequest object.
                    $request->setMetrics($metric);
                    $request->setMetricAggregations('TOTAL');

                    // Create the Dimensions object.
                    if ($dimensions) {

                        if (is_array($dimensions)) {
                            foreach ($dimensions as $value) {
                                $value = GA_Tools::ga3_ga4_mapping($value);
                                $dimensionobj = new Google\Service\AnalyticsData\Dimension();
                                $dimensionobj->setName($value);
                                $dimension[] = $dimensionobj;
                            }
                        } else {
                            $dimensions = GA_Tools::ga3_ga4_mapping($dimensions);
                            $dimensionobj = new Google\Service\AnalyticsData\Dimension();
                            $dimensionobj->setName($dimensions);
                            $dimension[] = $dimensionobj;
                        }

                        $request->setDimensions($dimension);
                    }

                    $data = $this->service_ga4_data->properties->runRealtimeReport($projectId, $request, array('quotaUser' => $quotauser));

                    GA_Tools::set_cache($serial, $data, 55);
                } else {

                    $data = $transient;
                }
            } catch (GoogleServiceException $e) {
                $timeout = $this->get_timeouts('midnight');
                GA_Tools::set_error($e, $timeout);
                return $e->getCode();
            } catch (Exception $e) {
                $timeout = $this->get_timeouts('midnight');
                GA_Tools::set_error($e, $timeout);
                return $e->getCode();
            }

            if ($data->getRows() < 1) {
                return -21;
            }

            $aiwp_data['rows'] = array();

            foreach ($data->getRows() as $row) {

                $values = array();

                if (isset($row->getDimensionValues()[0])) {
                    foreach ($row->getDimensionValues() as $item) {
                        $values[] = esc_html($item->getValue());
                    }
                }

                if (isset($row->getMetricValues()[0])) {
                    foreach ($row->getMetricValues() as $item) {
                        $values[] = esc_html($item->getValue());
                    }
                }

                $aiwp_data['rows'][] = $values;
            }

            $aiwp_data['totals'] = 0;

            if (method_exists($data, 'getTotals') && isset($data->getTotals()[0]->getMetricValues()[0])) {
                $aiwp_data['totals'] = (int)$data->getTotals()[0]->getMetricValues()[0]->getValue();
            }

            return $aiwp_data;
        }

        /**
         * Handles ajax requests and calls the needed methods
         * @param
         * 		$projectId
         * @param
         * 		$query
         * @param
         * 		$from
         * @param
         * 		$to
         * @param
         * 		$filter
         * @return number|Google\Service\Analytics\GaData
         */
        public function get($projectId, $query, $from = false, $to = false, $filter = '', $metric = 'sessions')
        {

            if (empty($projectId) || '' == $projectId || 'Disabled' == $projectId) {
                wp_die(-26);
            }

            if (in_array($query, array('sessions', 'users', 'organicSearches', 'visitBounceRate', 'pageviews', 'uniquePageviews'))) {
                return $this->get_areachart_data_ga4($projectId, $from, $to, $query, $filter);
            }
            if ('bottomstats' == $query) {
                return $this->get_bottomstats_ga4($projectId, $from, $to, $filter);
            }
            if ('pageTitle' == $query) {
                return $this->get_title_ga4($projectId, $from, $to, $filter);
            }
            if ('locations' == $query) {
                return $this->get_locations_ga4($projectId, $from, $to, $metric, $filter);
            }
            if ('contentpages' == $query) {
                return $this->get_contentpages_ga4($projectId, $from, $to, $metric, $filter);
            }
            if ('referrers' == $query) {
                return $this->get_referrers_ga4($projectId, $from, $to, $metric, $filter);
            }
            if ('searches' == $query) {
                return $this->get_searches_ga4($projectId, $from, $to, $metric, $filter);
            }
            if ('404errors' == $query) {
                $filter = $this->ga_config->options['pagetitle_404'];
                return $this->get_404errors_ga4($projectId, $from, $to, $metric, $filter);
            }
            if ('realtime' == $query) {
                return $this->get_realtime_ga4($projectId);
            }
            if ('channelGrouping' == $query || 'deviceCategory' == $query) {
                return $this->get_orgchart_data_ga4($projectId, $from, $to, $query, $metric, $filter);
            }
            if (in_array($query, array('medium', 'visitorType', 'socialNetwork', 'source', 'browser', 'operatingSystem', 'screenResolution', 'mobileDeviceBranding'))) {
                return $this->get_piechart_data_ga4($projectId, $from, $to, $query, $metric, $filter);
            }
            wp_die(-27);
        }
    }
}
