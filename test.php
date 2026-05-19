<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$mocked_input = '{"query": "Tell me about packages"}';
// we can inject this into php://input by mocking the file_get_contents
