<?php 
if( !defined("WHMCS") ) 
{
    exit( "This file cannot be accessed directly" );
}

$admin = WHMCS\User\Admin::find((int) WHMCS\Session::get("adminid"));
if( is_null($admin) ) 
{
    $apiresults = array( "result" => "error", "message" => "You must be authenticated as an admin user to perform this action" );
}
else
{
    $admin->notes = $notes;
    $admin->save();
    $apiresults = array( "result" => "success" );
}


