<?php
$this->title = __('Forbidden');
$this->code = 403;
$this->message = __($this->exception->getMessage() ?: 'Forbidden');

$this->need('error');