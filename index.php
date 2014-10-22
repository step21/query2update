<?php
/* query2uptime reads a flatfile of uptimes and allows also to put an uptime 
   into the queue.

 */

include_once '../lib/shared.php';
include_once '../lib/file.php';
include_once '../lib/header.php';
?>

<div class="wrapper">

<?php


$now = '';
if ( empty($configs['_time']) )
    $now = time();
else
    $now = $configs['_time'];

$ourid = 'default';
if ( !empty($configs['_id']) )
    $ourid = $configs['_id'];


$configs['_ip'] = $_SERVER["REMOTE_ADDR"];;


$db = 'db/' . $ourid . '.csv';
$updates = '';
$updates = get_file_csv( $db );
// if ( FALSE == $updates )
//    echo '<h4 class="alert alert-danger">Could not read updates.</h4>' . "\n";

$inputs = array("_time" => $now,"_ip" => $configs['_ip']) + $inputs;

$allkeys = array_keys($inputs);
$allvals = array_values($inputs);
$allins = array($allkeys,$allvals);


$date_style = 'Y-m-d h:i:s A';


$last_update = end($updates);
// print_r($last_update);
// print_r($allvals);

$ct = 0;
$is_same_update = true;
foreach ( $allkeys as $key )
{
    if ( $key == '_time' )
    {
        $ct++;
        continue;
    }
    if ( $last_update[$ct] != $allvals[$ct] )
        $is_same_update = false;
    $ct++;
}


$body_display = '<table class="table table-striped table-bordered table-hover">' . "\n";
$body_display .= '<tr style="font-weight: bold">' . "\n";
foreach ( $inputs as $key => $value )
{
    if ( $key == '_ip' )
        continue;
    // hacky fix to strip current input type selector
    if ( '_t' == substr( $key, -2) )
        $key = substr( $key, 0, -2);
    $key = ucwords(strtr($key, array('-' => ' ', '_' => '') ));
    $body_display .= '<td>' . $key . '</td>' . "\n";
}

if ( ! $is_same_update )
{
    $body_display .= '<tr class="alert alert-info">';
    foreach ( $inputs as $key => $val )
    {
        if ( $key == '_time' )
            $val = date($date_style, $val); 
        if ( $key == '_ip' )
            continue;
        if ( is_email($val) )
            $val = '<a href="mailto:' . $val . '" title="Email ' .$val. '">' . 
                   $val . '</a>';
        if ( is_url($val) )
            $val = '<a href="' . $val . '" title="File ' . $val . '">' . 
                   $val . '</a>';

        $body_display .= '<td>' . $val . '</td>' . "\n";
    }
    $body_display .= '</tr>';
}

$updates = array_reverse($updates);
foreach ( $updates as $up )
{
    $ct = 0;
    $body_display .= '<tr>';
    foreach ( $up as $u )
    {
        if ( $allkeys[$ct] == '_time' )
            $u = date($date_style, $u);
        // if (strpos($allkeys[$ct],'email') !== false)
        if ( is_email($u) )
            $u = '<a href="mailto:' . $u . '" title="Email ' .$u. '">' . 
                   $u . '</a>';
        // if ( $allkeys[$ct] == 'file' )
        if ( is_url($u) )
            $u = '<a href="' . $u . '" title="File ' . $u . '">' . 
                   $u . '</a>';
        if ( $allkeys[$ct] == '_ip' )
        {
            $ct++;
            continue;
        }
        $body_display .= '<td>' . $u . '</td>' . "\n";
        $ct++;
    }
    $body_display .= '</tr>';
}


if ( $is_same_update )
    echo '<h4 class="alert alert-danger">' . "You can't save the same data twice. Go back and fix." . "</h4>\n";
else
    echo '<h4 class="alert alert-success">' . $configs['_success'] . "</h4>\n";

echo $body_display;


echo "</table>\n";


if ( !$is_same_update && save_file_csv( array($allvals), $db, 'a' ) )
    echo '<h4 class="alert alert-success">Successfully Updated.</h4>';
else
    echo '<h4 class="alert alert-danger">Failed to Update.</h4>';



?>
</div>
<?php

include_once '../lib/footer.php';
