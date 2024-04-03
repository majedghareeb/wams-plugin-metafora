<?php

// require_once WAMS_PATH . '/includes/core/twitter/api.php';
// $twitter = new wams/core/twitter;
// print_r($twitter->capture_twitter_feeds());

// URL of the remote WordPress site's JSON endpoint
get_header();

// Retrieve Gravity Forms data (example)
$entry = GFAPI::get_entry(1890);

// Start generating Word document
$form_id = $entry['form_id'];
$html = '<html><body dir="rtl"><div class = "content">';
$form_fields = GFAPI::get_form($form_id)['fields'];
echo '<pre>';
print_r($form_fields);
// foreach ($form_fields as $field) {
//     if ($entry[$field->id] == '') continue;
//     $html .= '<h2>' . $field->label . '</h2>';
//     if ($field->type == 'form') {

//         $html .= '<div>FORM Data HERE</div>';
//         $link_entries = explode(',', $entry[$field->id]);
//         if (empty($link_entries)) continue;
//         $html .= '<ul>';
//         foreach ($link_entries as $entry_id) {
//             $link_entry = GFAPI::get_entry($entry_id);
//             if (!is_wp_error($link_entry)) {
//                 $html .= '<li><a href=' . $link_entry['1'] . '">' . $link_entry['1'] . '</a></li>';
//             }
//         }
//         $html .= '</ul>';
//     } else {
//         $html .= '<div>' . $entry[$field->id] . '</div>';
//     }

//     $html .= '<br>';
// }
// $html .= '</div></body></html>';
// // print_r($form_fields);
// echo $html;
// require_once WAMS_PATH . 'includes/lib/html2doc/class-export-to-word.php';
// $upload_dir = wp_upload_dir();

// // Access the path to the upload directory
// $upload_dir_path = $upload_dir['basedir'];

// $css = '<style type = "text/css">.test {font-weight: 600;}</style>';
// $filePath =  WAMS_PATH . 'temp/' . $entry['id'] . '.doc';
// $file_url = WAMS_URL . 'temp/' . $entry['id'] . '.doc';
// \ExportToWord::htmlToDoc($html, $css, $filePath);
// header("Content-Type: application/octet-stream");
// header("Content-Disposition: attachment; filename=\"" . basename($filePath) . "\"");
// readfile($filePath);
// $fileurl = '/wp-content/plugins/wams/temp/1282.doc';
// // Check if the file exists
// if (file_exists($filePath)) {
//     // Set headers for download
//     header('Content-Type: application/octet-stream'); // Adjust content type if necessary
//     header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
//     header('Content-Length: ' . filesize($filePath)); // Optional: Set content length

//     // Open the file for reading in binary mode
//     $fileHandle = fopen($filePath, 'rb');

//     // Check if file opened successfully
//     if ($fileHandle) {
//         // Read the file content in chunks and output it
//         while (!feof($fileHandle)) {
//             $buffer = fread($fileHandle, 1024); // Read 1024 bytes at a time
//             echo $buffer;
//         }

//         // Close the file handle
//         fclose($fileHandle);
//     } else {
//         echo 'Error: Could not open file.';
//     }

//     // Exit script (optional) to prevent further output

// } else {
//     // Handle the case where the file doesn't exist
//     echo 'Error: File not found.';
// }
// Exit
echo '<a href="' . $file_url . '">Download</a>';
exit;


?>


<?php
