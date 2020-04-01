<?php
$this->title = __('Service Unavailable');
$this->code = 500;
$this->message = __($this->exception->getMessage() ?: 'Service Unavailable');

$this->need('error');