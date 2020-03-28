<?php
/**
 * @var $this \TarBlog\View\Engine
 */
$title = 'Not Found';
$code = 404;
$message = 'Not Found';

$this->need('error',compact('title','code','message'));