<?php
$this->title = 'Service Unavailable';
$this->code = 500;
$this->message = $this->exception->getMessage() ?: 'Service Unavailable';

$this->need('error');