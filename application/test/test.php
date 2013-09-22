<?php
/**
 * just a simple test
 *
 */
 
$context = stream_context_create(array('http'=>array('ignore_errors'=>true)));
echo file_get_contents('http://nodeprint.com', false, $context);
echo file_get_contents('http://127.0.0.1', false, $context);
exit(0);
