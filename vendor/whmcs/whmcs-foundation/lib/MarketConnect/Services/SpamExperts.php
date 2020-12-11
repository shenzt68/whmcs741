<?php 
namespace WHMCS\MarketConnect\Services;


class SpamExperts extends AbstractService
{
    public function provision($model, array $params = NULL)
    {
        $this->configure($model, $params);
    }

    public function configure($model, array $params = NULL)
    {
        $serviceProperties = $model->serviceProperties;
        $orderNumber = $serviceProperties->get("Order Number");
        if( !$orderNumber ) 
        {
            throw new \WHMCS\Exception\Module\NotServicable("You must provision this service before attempting to configure it");
        }

        $relatedHostingService = null;
        if( $model instanceof \WHMCS\Service\Service ) 
        {
            $relatedHostingService = \WHMCS\MarketConnect\Provision::findRelatedHostingService($model);
        }

        $domainName = ($model instanceof \WHMCS\Service\Addon ? $model->service->domain : $model->domain);
        $configure = array( "order_number" => $orderNumber, "domain" => $domainName );
        $configure["server_module"] = (($model instanceof \WHMCS\Service\Addon ? $model->service->product->module : $relatedHostingService instanceof \WHMCS\Service\Service) ? $relatedHostingService->product->module : "");
        $api = new \WHMCS\MarketConnect\Api();
        $response = $api->configure($configure);
        if( array_key_exists("error", $response) ) 
        {
            throw new \WHMCS\Exception($response["error"]);
        }

        $mxRecords = $response["data"]["mxRecords"];
        $dataToAdd = array(  );
        foreach( $mxRecords as $mxRecord ) 
        {
            $dataToAdd[$mxRecord["host"]] = $mxRecord["priority"];
        }
        $configurationRequired = true;
        $emailRelatedId = $model->id;
        if( $model instanceof \WHMCS\Service\Addon || $relatedHostingService instanceof \WHMCS\Service\Service ) 
        {
            $parentModel = ($model instanceof \WHMCS\Service\Addon ? $model->service : $relatedHostingService);
            switch( $parentModel->product->module ) 
            {
                case "cpanel":
                    $serverInterface = \WHMCS\Module\Server::factoryFromModel($parentModel);
                    $mxData = $serverInterface->call("GetMxRecords", array( "mxDomain" => $domainName ));
                    $dataToSaveRemove = array(  );
                    foreach( $mxData["mxRecords"] as $mxDatum ) 
                    {
                        $dataToSaveRemove[$mxDatum["mx"]] = $mxDatum["priority"];
                    }
                    if( $dataToAdd ) 
                    {
                        $serverInterface->call("AddMxRecords", array( "mxDomain" => $domainName, "mxRecords" => $dataToAdd, "alwaysAccept" => "local" ));
                    }

                    if( $dataToSaveRemove ) 
                    {
                        $serverInterface->call("DeleteMxRecords", array( "mxDomain" => $domainName, "mxRecords" => $dataToSaveRemove ));
                        $dataString = "";
                        foreach( $dataToSaveRemove as $host => $priority ) 
                        {
                            $dataString .= (string) $priority . ":" . $host . "\r\n";
                        }
                        $serviceProperties->save(array( "Original MX Records" => array( "type" => "textarea", "value" => $dataString ) ));
                    }

                    $emailRelatedId = ($model instanceof \WHMCS\Service\Addon ? $parentModel->id : $model->id);
                    $configurationRequired = false;
                    break;
                case "directadmin":
                case "plesk":
                    $serverInterface = \WHMCS\Module\Server::factoryFromModel($parentModel);
                    $mxData = $serverInterface->call("GetMxRecords", array( "mxDomain" => $domainName ));
                    $dataToSaveRemove = $mxData["mxRecords"];
                    if( $dataToAdd ) 
                    {
                        $serverInterface->call("AddMxRecords", array( "mxDomain" => $domainName, "mxRecords" => $dataToAdd, "internal" => "no" ));
                    }

                    if( $dataToSaveRemove ) 
                    {
                        $serverInterface->call("DeleteMxRecords", array( "mxDomain" => $domainName, "mxRecords" => $dataToSaveRemove ));
                        $dataString = "";
                        foreach( $dataToSaveRemove as $mxRecord ) 
                        {
                            $dataString .= (string) $mxRecord["priority"] . ":" . $mxRecord["mx"] . "\r\n";
                        }
                        $serviceProperties->save(array( "Original MX Records" => array( "type" => "textarea", "value" => $dataString ) ));
                    }

                    $emailRelatedId = ($model instanceof \WHMCS\Service\Addon ? $parentModel->id : $model->id);
                    $configurationRequired = false;
                    break;
            }
        }

        sendMessage("SpamExperts Welcome Email", $emailRelatedId, array( "required_mx_records" => $dataToAdd, "configuration_required" => $configurationRequired ));
    }

    public function cancel($model)
    {
        try
        {
            $serviceProperties = $model->serviceProperties;
            $orderNumber = $serviceProperties->get("Order Number");
            if( !$orderNumber ) 
            {
                throw new \WHMCS\Exception\Module\NotServicable("You must provision this service before attempting to manage it");
            }

            $relatedHostingService = null;
            if( $model instanceof \WHMCS\Service\Service ) 
            {
                $relatedHostingService = \WHMCS\MarketConnect\Provision::findRelatedHostingService($model);
            }

            $domainName = ($model instanceof \WHMCS\Service\Addon ? $model->service->domain : $model->domain);
            $api = new \WHMCS\MarketConnect\Api();
            $response = $api->cancel($orderNumber);
            if( $response["success"] == true ) 
            {
                if( $model instanceof \WHMCS\Service\Addon || $relatedHostingService instanceof \WHMCS\Service\Service ) 
                {
                    $existingMxRecords = $serviceProperties->get("Original MX Records");
                    $parentModel = ($model instanceof \WHMCS\Service\Addon ? $model->service : $relatedHostingService);
                    switch( $parentModel->product->module ) 
                    {
                        case "cpanel":
                            if( $existingMxRecords ) 
                            {
                                $existingMxRecords = explode("\r\n", $existingMxRecords);
                                $dataToAdd = array(  );
                                foreach( $existingMxRecords as $existingMxRecord ) 
                                {
                                    $existingMxRecord = explode(":", $existingMxRecord);
                                    if( isset($existingMxRecord[1]) ) 
                                    {
                                        $dataToAdd[$existingMxRecord[1]] = $existingMxRecord[0];
                                    }

                                }
                                $serverInterface = \WHMCS\Module\Server::factoryFromModel($parentModel);
                                $mxData = $serverInterface->call("GetMxRecords", array( "mxDomain" => $domainName ));
                                $dataToRemove = array(  );
                                foreach( $mxData["mxRecords"] as $mxDatum ) 
                                {
                                    $dataToRemove[$mxDatum["mx"]] = $mxDatum["priority"];
                                }
                                if( $dataToRemove ) 
                                {
                                    $serverInterface->call("DeleteMxRecords", array( "mxDomain" => $domainName, "mxRecords" => $dataToRemove ));
                                }

                                if( $dataToAdd ) 
                                {
                                    $serverInterface->call("AddMxRecords", array( "mxDomain" => $domainName, "mxRecords" => $dataToAdd, "alwaysAccept" => "auto" ));
                                }

                            }

                            return NULL;
                        case "directadmin":
                        case "plesk":
                            if( $existingMxRecords ) 
                            {
                                $existingMxRecords = explode("\r\n", $existingMxRecords);
                                $dataToAdd = array(  );
                                foreach( $existingMxRecords as $existingMxRecord ) 
                                {
                                    $existingMxRecord = explode(":", $existingMxRecord);
                                    if( isset($existingMxRecord[1]) ) 
                                    {
                                        $dataToAdd[$existingMxRecord[1]] = $existingMxRecord[0];
                                    }

                                }
                                $serverInterface = \WHMCS\Module\Server::factoryFromModel($parentModel);
                                $mxData = $serverInterface->call("GetMxRecords", array( "mxDomain" => $domainName ));
                                $dataToRemove = $mxData["mxRecords"];
                                if( $dataToRemove ) 
                                {
                                    $serverInterface->call("DeleteMxRecords", array( "mxDomain" => $domainName, "mxRecords" => $dataToRemove ));
                                }

                                if( $dataToAdd ) 
                                {
                                    $serverInterface->call("AddMxRecords", array( "mxDomain" => $domainName, "mxRecords" => $dataToAdd, "internal" => "yes" ));
                                }

                            }

                            return NULL;
                    }
                }

                return NULL;
            }

            throw new \WHMCS\Exception("Cancellation Failed");
        }
        catch( \Exception $e ) 
        {
            throw $e;
        }
    }

    public function clientAreaAllowedFunctions($params)
    {
        if( $params["status"] != "Active" ) 
        {
            return array(  );
        }

        return array( "manage_order" );
    }

    public function clientAreaOutput(array $params)
    {
        $orderNumber = marketconnect_GetOrderNumber($params);
        if( !$orderNumber ) 
        {
            return "";
        }

        $serviceId = $params["serviceid"];
        $addonId = (array_key_exists("addonId", $params) ? $params["addonId"] : 0);
        $manageText = \Lang::trans("manage");
        return "<form>\n    <input type=\"hidden\" name=\"modop\" value=\"custom\" />\n    <input type=\"hidden\" name=\"a\" value=\"manage_order\" />\n    <input type=\"hidden\" name=\"serviceid\" value=\"" . $serviceId . "\" />\n    <input type=\"hidden\" name=\"addonId\" value=\"" . $addonId . "\" />\n    <button class=\"btn btn-service-sso\">\n        <span class=\"loading hidden\">\n            <i class=\"fa fa-spinner fa-spin\"></i>\n        </span>\n        <span class=\"text\">" . $manageText . "</span>\n    </button>\n    <span class=\"login-feedback\"></span>\n</form>";
    }

    public function adminServicesTabOutput(array $params, \WHMCS\MarketConnect\OrderInformation $orderInformation = NULL, array $actionButtons = NULL)
    {
        $orderInfo = \WHMCS\MarketConnect\OrderInformation::factory($params);
        $actionBtns = array( array( "icon" => "fa-cog", "label" => "Attempt Configuration", "class" => "btn-default", "moduleCommand" => "resend_configuration_data", "applicableStatuses" => array( "Awaiting Configuration" ) ), array( "icon" => "fa-sign-in", "label" => "Login to SpamExperts Control Panel", "class" => "btn-default", "moduleCommand" => "admin_sso", "applicableStatuses" => array( "Active" ) ) );
        return parent::adminServicesTabOutput($params, $orderInfo, $actionBtns);
    }

}


