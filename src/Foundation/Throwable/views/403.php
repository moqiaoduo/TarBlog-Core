<?php
$this->title = 'Forbidden';
$this->code = 403;
$this->message = $this->exception->getMessage() ?: 'Forbidden';

$this->need('error');