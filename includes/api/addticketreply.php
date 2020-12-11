<?php 
if( !defined("WHMCS") ) 
{
    exit( "This file cannot be accessed directly" );
}

if( !function_exists("AddReply") ) 
{
    require(ROOTDIR . "/includes/ticketfunctions.php");
}

$useMarkdown = (bool) (int) App::get_req_var("markdown");
$from = "";
$result = select_query("tbltickets", "", array( "id" => $ticketid ));
$data = mysql_fetch_array($result);
$ticketid = $data["id"];
if( !$ticketid ) 
{
    $apiresults = array( "result" => "error", "message" => "Ticket ID Not Found" );
}
else
{
    if( $clientid ) 
    {
        $result = select_query("tblclients", "id", array( "id" => $clientid ));
        $data = mysql_fetch_array($result);
        if( !$data["id"] ) 
        {
            $apiresults = array( "result" => "error", "message" => "Client ID Not Found" );
            return NULL;
        }

        if( $contactid ) 
        {
            $result = select_query("tblcontacts", "id", array( "id" => $contactid, "userid" => $clientid ));
            $data = mysql_fetch_array($result);
            if( !$data["id"] ) 
            {
                $apiresults = array( "result" => "error", "message" => "Contact ID Not Found" );
                return NULL;
            }

        }

    }
    else
    {
        if( (!$name || !$email) && !$adminusername ) 
        {
            $apiresults = array( "result" => "error", "message" => "Name and email address are required if not a client" );
            return NULL;
        }

        $from = array( "name" => $name, "email" => $email );
    }

    if( !$message ) 
    {
        $apiresults = array( "result" => "error", "message" => "Message is required" );
    }
    else
    {
        AddReply($ticketid, $clientid, $contactid, $message, $adminusername, "", $from, $status, $noemail, true, $useMarkdown);
        if( $customfields ) 
        {
            $customfields = base64_decode($customfields);
            $customfields = safe_unserialize($customfields);
            saveCustomFields($ticketid, $customfields, "support", true);
        }

        $apiresults = array( "result" => "success" );
    }

}


