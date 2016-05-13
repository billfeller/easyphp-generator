<?php
// vim: set expandtab cindent tabstop=4 shiftwidth=4 fdm=marker:

// /json.php?mod=mod&act=act&params=xxx
function mod_act() {
    $array = array('t' => 1);
    return json_encode($array);
}