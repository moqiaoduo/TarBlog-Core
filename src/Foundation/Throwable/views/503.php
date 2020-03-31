<?php
$this->title = 'Service Unavailable';
$this->code = 500;
$this->message = $this->exception->getMessage() ?: '服务暂不可用';

$this->need('error');