<?php
// vim: set expandtab cindent tabstop=4 shiftwidth=4 fdm=marker:

$lib_root = preg_replace('/htdocs.*/i', 'weblib/', dirname(__FILE__));
require_once($lib_root . 'etc/config.inc.php');

$logger = new Logger('cc');

$jsonConfig = array('t' => 1);

$tpl = new Template();
$tpl->assign('jsonConfig', json_encode($jsonConfig));

$tpltest = !empty($_REQUEST['tpltest']) ? $_REQUEST['tpltest'] : 0;
if (!empty($tpltest)) {
    $tpl->display(SITE_TPL_SRC_ROOT.'/index/index.html');
} else {
    $tpl->display(SITE_TPL_ROOT.'/index/index.html');
}