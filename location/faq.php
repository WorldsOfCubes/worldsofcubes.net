<?php
if (!defined('MCR')) exit;

$page = 'FAQ по системе'; 

$content_main = View::ShowStaticPage('faq.html');

$menu->SetItemActive('faq');
?>
