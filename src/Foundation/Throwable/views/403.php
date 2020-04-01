<?php
$this->title = _t('Forbidden');
$this->code = 403;
$this->message = _t($this->exception->getMessage() ?: 'Forbidden');

$this->need('error');