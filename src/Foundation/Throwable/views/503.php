<?php
$this->title = _t('Service Unavailable');
$this->code = 500;
$this->message = _t($this->exception->getMessage() ?: 'Service Unavailable');

$this->need('error');