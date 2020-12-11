<?php 
function virtualmin_MetaData()
{
    return array( "DisplayName" => "Virtualmin", "APIVersion" => "1.0" );
}

function virtualmin_ConfigOptions()
{
    $configarray = array( "Template Name" => array( "Type" => "text", "Size" => "30" ), "Plan Name" => array( "Type" => "text", "Size" => "30" ), "Dedicated IP" => array( "Type" => "yesno", "Description" => "Tick to auto assign next available dedicated IP" ) );
    return $configarray;
}

function virtualmin_ClientArea($params)
{
    global $_LANG;
    $domain = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);
    $form = sprintf("<form action=\"%s://%s/session_login.cgi\" method=\"post\" target=\"_blank\">" . "<input type=\"hidden\" name=\"user\" value=\"%s\" />" . "<input type=\"hidden\" name=\"pass\" value=\"%s\" />" . "<input type=\"hidden\" name=\"notestingcookie\" value=\"1\" />" . "<input type=\"submit\" value=\"%s\" class=\"button\" />" . "</form>", $params["serverhttpprefix"], WHMCS\Input\Sanitize::encode($domain), WHMCS\Input\Sanitize::encode($params["username"]), WHMCS\Input\Sanitize::encode($params["password"]), $_LANG["virtualminlogin"]);
    return $form;
}

function virtualmin_AdminLink($params)
{
    $domain = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);
    $form = sprintf("<form action=\"%s://%s/session_login.cgi\" method=\"post\" target=\"_blank\">" . "<input type=\"hidden\" name=\"user\" value=\"%s\" />" . "<input type=\"hidden\" name=\"pass\" value=\"%s\" />" . "<input type=\"hidden\" name=\"notestingcookie\" value=\"1\" />" . "<input type=\"submit\" value=\"%s\" class=\"button\" />" . "</form>", $params["serverhttpprefix"], WHMCS\Input\Sanitize::encode($domain), WHMCS\Input\Sanitize::encode($params["serverusername"]), WHMCS\Input\Sanitize::encode($params["serverpassword"]), "Login to Control Panel");
    return $form;
}

function virtualmin_CreateAccount($params)
{
    $updateData = array(  );
    if( $params["type"] == "reselleraccount" ) 
    {
        if( !$params["username"] ) 
        {
            $username = preg_replace("/[^a-z0-9]/", "", strtolower($params["clientsdetails"]["firstname"] . $params["clientsdetails"]["lastname"] . $params["serviceid"]));
            $updateData["username"] = $username;
            $params["username"] = $username;
        }

        $postfields = array(  );
        $postfields["program"] = "create-reseller";
        $postfields["name"] = $params["username"];
        $postfields["pass"] = $params["password"];
        $postfields["email"] = $params["clientsdetails"]["email"];
        if( $params["configoption2"] ) 
        {
            $postfields["plan"] = $params["configoption2"];
        }

        $result = virtualmin_req($params, $postfields);
    }
    else
    {
        $postfields = array(  );
        $postfields["program"] = "create-domain";
        $postfields["domain"] = $params["domain"];
        $postfields["user"] = $params["username"];
        $postfields["pass"] = $params["password"];
        $postfields["email"] = $params["clientsdetails"]["email"];
        if( $params["configoption1"] ) 
        {
            $postfields["template"] = $params["configoption1"];
        }

        if( $params["configoption2"] ) 
        {
            $postfields["plan"] = $params["configoption2"];
        }

        if( $params["configoption3"] ) 
        {
            $postfields["allocate-ip"] = "";
        }

        $postfields["features-from-plan"] = "";
        $result = virtualmin_req($params, $postfields);
    }

    if( $updateData ) 
    {
        $params["model"]->serviceProperties->save($updateData);
    }

    return $result;
}

function virtualmin_SuspendAccount($params)
{
    if( $params["type"] == "reselleraccount" ) 
    {
        $postfields = array(  );
        $postfields["program"] = "modify-reseller";
        $postfields["name"] = $params["username"];
        $postfields["pass"] = md5(rand(10000, 99999999) . $params["domain"]);
        $postfields["lock"] = "1";
    }
    else
    {
        $postfields = array(  );
        $postfields["program"] = "disable-domain";
        $postfields["domain"] = $params["domain"];
    }

    $result = virtualmin_req($params, $postfields);
    return $result;
}

function virtualmin_UnsuspendAccount($params)
{
    if( $params["type"] == "reselleraccount" ) 
    {
        $postfields = array(  );
        $postfields["program"] = "modify-reseller";
        $postfields["name"] = $params["username"];
        $postfields["pass"] = $params["password"];
        $postfields["lock"] = "0";
    }
    else
    {
        $postfields = array(  );
        $postfields["program"] = "enable-domain";
        $postfields["domain"] = $params["domain"];
    }

    $result = virtualmin_req($params, $postfields);
    return $result;
}

function virtualmin_TerminateAccount($params)
{
    if( $params["type"] == "reselleraccount" ) 
    {
        $postfields = array(  );
        $postfields["program"] = "delete-reseller";
        $postfields["name"] = $params["username"];
    }
    else
    {
        $postfields = array(  );
        $postfields["program"] = "delete-domain";
        $postfields["domain"] = $params["domain"];
    }

    $result = virtualmin_req($params, $postfields);
    return $result;
}

function virtualmin_ChangePassword($params)
{
    $postfields = array(  );
    $postfields["program"] = "modify-domain";
    $postfields["domain"] = $params["domain"];
    $postfields["pass"] = $params["password"];
    $result = virtualmin_req($params, $postfields);
    return $result;
}

function virtualmin_ChangePackage($params)
{
    $postfields = array(  );
    $postfields["program"] = "modify-domain";
    $postfields["domain"] = $params["domain"];
    $postfields["plan-features"] = "";
    if( $params["configoption1"] ) 
    {
        $postfields["template"] = $params["configoption1"];
    }

    if( $params["configoption2"] ) 
    {
        $postfields["apply-plan"] = $params["configoption2"];
    }

    $result = virtualmin_req($params, $postfields);
    return $result;
}

function virtualmin_UsageUpdate($params)
{
    $postfields = array(  );
    $postfields["program"] = "list-domains";
    $postfields["multiline"] = "";
    $result = virtualmin_req($params, $postfields, true);
    $dataarray = explode("\n", $result);
    $arraydata = array(  );
    $domainsarray = array(  );
    foreach( $dataarray as $line ) 
    {
        if( substr($line, 0, 4) == "    " ) 
        {
            $line = trim($line);
            $line = explode(":", $line, 2);
            $arraydata[trim($line[0])] = trim($line[1]);
        }
        else
        {
            $domain = trim($line);
            $domainsarray[$domain] = $arraydata;
            $arraydata = array(  );
        }

    }
    $services = WHMCS\Service\Service::where("server", "=", $params["serverid"])->get();
    $addons = WHMCS\Service\Addon::whereHas("customFieldValues.customField", function($query)
{
    $query->where("fieldname", "Domain");
}

)->with("customFieldValues", "customFieldValues.customField")->where("server", "=", $params["serverid"])->get();
    foreach( $domainsarray as $domain => $values ) 
    {
        if( !$domain ) 
        {
            continue;
        }

        $diskusage = $values["Server byte quota used"] / 1048576;
        $disklimit = $values["Server block quota"] / 1024;
        $bwlimit = $values["Bandwidth byte limit"] / 1048576;
        $bwused = $values["Bandwidth byte usage"] / 1048576;
        $model = $services->where("domain", $domain)->first();
        if( !$model ) 
        {
            foreach( $addons as $searchAddon ) 
            {
                foreach( $searchAddon->customFieldValues as $customFieldValue ) 
                {
                    if( !$customFieldValue->customField ) 
                    {
                        continue;
                    }

                    if( $customFieldValue->value == $domain ) 
                    {
                        $model = $searchAddon;
                        break 2;
                    }

                }
            }
        }

        if( !$model ) 
        {
            continue;
        }

        $model->serviceProperties->save(array( "diskusage" => $diskusage, "disklimit" => $disklimit, "bwusage" => $bwused, "bwlimit" => $bwlimit, "lastupdate" => Carbon\Carbon::now()->toDateTimeString() ));
    }
}

function virtualmin_req($params, $postfields, $rawdata = false)
{
    $http = $params["serverhttpprefix"];
    $domain = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);
    $url = (string) $http . "://" . $domain . "/virtual-server/remote.cgi?" . $fieldstring;
    $fieldstring = "";
    foreach( $postfields as $k => $v ) 
    {
        $fieldstring .= (string) $k . "=" . urlencode($v) . "&";
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldstring);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, $params["serverusername"] . ":" . $params["serverpassword"]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $data = curl_exec($ch);
    if( curl_errno($ch) ) 
    {
        $data = "Curl Error: " . curl_errno($ch) . " - " . curl_error($ch);
    }

    curl_close($ch);
    logModuleCall("virtualmin", $postfields["program"], $postfields, $data);
    if( strpos($data, "Unauthorized") == true ) 
    {
        return "Server Login Invalid";
    }

    if( $rawdata ) 
    {
        return $data;
    }

    $exitstatuspos = strpos($data, "Exit status:");
    $exitstatus = trim(substr($data, $exitstatuspos + 12));
    if( $exitstatus == "0" ) 
    {
        $result = "success";
    }
    else
    {
        $dataarray = explode("\n", $data);
        $result = $dataarray[0];
    }

    return $result;
}


