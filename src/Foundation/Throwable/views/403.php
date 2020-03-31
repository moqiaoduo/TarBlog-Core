<?php
$this->title = 'Forbidden';
$this->code = 403;
$this->message = $this->exception->getMessage() ?: '访问被拒绝';

$this->need('error');