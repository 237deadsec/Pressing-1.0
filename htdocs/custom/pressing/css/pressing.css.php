<?php
if (!defined('NOTOKENRENEWAL')) {
    define('NOTOKENRENEWAL', '1');
}
if (!defined('NOREQUIREMENU')) {
    define('NOREQUIREMENU', '1');
}
if (!defined('NOREQUIREHTML')) {
    define('NOREQUIREHTML', '1');
}
if (!defined('NOREQUIREAJAX')) {
    define('NOREQUIREAJAX', '1');
}
require '../../../main.inc.php';
header('Content-Type: text/css');
?>
.pressing-dashboard {
    display: grid;
    grid-template-columns: repeat(4, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}
.dashboard-item {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 12px;
    background: #fff;
}
.dashboard-item .label {
    display: block;
    color: #666;
}
.dashboard-item .value {
    display: block;
    font-size: 1.6em;
    font-weight: 700;
}
