<?php 
namespace WHMCS\MarketConnect;


class SslController
{
    public function index(\WHMCS\Http\Message\ServerRequest $request)
    {
        $isAdminPreview = \App::getFromRequest("preview") && \WHMCS\Session::get("adminid");
        $sslAdminPreviewSession = \WHMCS\Session::get("sslAdminPreview");
        if( !$isAdminPreview && !$sslAdminPreviewSession ) 
        {
            $service = Service::where("name", "symantec")->first();
            if( is_null($service) || !$service->status ) 
            {
                return new \Zend\Diactoros\Response\RedirectResponse("index.php");
            }

        }

        $ca = $this->certInfoView();
        return $ca;
    }

    public function viewDv(\WHMCS\Http\Message\ServerRequest $request)
    {
        $isAdminPreview = \App::getFromRequest("preview") && \WHMCS\Session::get("adminid");
        $sslAdminPreviewSession = \WHMCS\Session::get("sslAdminPreview");
        if( !$isAdminPreview && !$sslAdminPreviewSession ) 
        {
            $service = Service::where("name", "symantec")->first();
            if( is_null($service) || !$service->status ) 
            {
                return new \Zend\Diactoros\Response\RedirectResponse("index.php");
            }

        }

        $ca = $this->certInfoView();
        $ca->setPageTitle(\Lang::trans("store.ssl.dv.title") . " - " . \Lang::trans("store.ssl.title"));
        $ca->addToBreadCrumb(routePath("store-ssl-certificates-dv"), \Lang::trans("store.ssl.dv.title"));
        $ca->setTemplate("store/ssl/dv");
        return $ca;
    }

    public function viewOv()
    {
        $isAdminPreview = \App::getFromRequest("preview") && \WHMCS\Session::get("adminid");
        $sslAdminPreviewSession = \WHMCS\Session::get("sslAdminPreview");
        if( !$isAdminPreview && !$sslAdminPreviewSession ) 
        {
            $service = Service::where("name", "symantec")->first();
            if( is_null($service) || !$service->status ) 
            {
                return new \Zend\Diactoros\Response\RedirectResponse("index.php");
            }

        }

        $ca = $this->certInfoView();
        $ca->setPageTitle(\Lang::trans("store.ssl.ov.title") . " - " . \Lang::trans("store.ssl.title"));
        $ca->addToBreadCrumb(routePath("store-ssl-certificates-ov"), \Lang::trans("store.ssl.ov.title"));
        $ca->setTemplate("store/ssl/ov");
        return $ca;
    }

    public function viewEv()
    {
        $isAdminPreview = \App::getFromRequest("preview") && \WHMCS\Session::get("adminid");
        $sslAdminPreviewSession = \WHMCS\Session::get("sslAdminPreview");
        if( !$isAdminPreview && !$sslAdminPreviewSession ) 
        {
            $service = Service::where("name", "symantec")->first();
            if( is_null($service) || !$service->status ) 
            {
                return new \Zend\Diactoros\Response\RedirectResponse("index.php");
            }

        }

        $ca = $this->certInfoView();
        $ca->setPageTitle(\Lang::trans("store.ssl.ev.title") . " - " . \Lang::trans("store.ssl.title"));
        $ca->addToBreadCrumb(routePath("store-ssl-certificates-ev"), \Lang::trans("store.ssl.ev.title"));
        $ca->setTemplate("store/ssl/ev");
        return $ca;
    }

    public function viewWildcard()
    {
        $isAdminPreview = \App::getFromRequest("preview") && \WHMCS\Session::get("adminid");
        $sslAdminPreviewSession = \WHMCS\Session::get("sslAdminPreview");
        if( !$isAdminPreview && !$sslAdminPreviewSession ) 
        {
            $service = Service::where("name", "symantec")->first();
            if( is_null($service) || !$service->status ) 
            {
                return new \Zend\Diactoros\Response\RedirectResponse("index.php");
            }

        }

        $ca = $this->certInfoView();
        $ca->setPageTitle(\Lang::trans("store.ssl.wildcard.title") . " - " . \Lang::trans("store.ssl.title"));
        $ca->addToBreadCrumb(routePath("store-ssl-certificates-wildcard"), \Lang::trans("store.ssl.wildcard.title"));
        $ca->setTemplate("store/ssl/wildcard");
        return $ca;
    }

    protected function certInfoView()
    {
        $ca = new \WHMCS\ClientArea();
        $ca->setPageTitle(\Lang::trans("store.ssl.title"));
        $ca->addToBreadCrumb("index.php", \Lang::trans("globalsystemname"));
        $ca->addToBreadCrumb(routePath("store"), \Lang::trans("navStore"));
        $ca->addToBreadCrumb(routePath("store-ssl-certificates-index"), \Lang::trans("store.ssl.title"));
        $ca->initPage();
        $all = Product::ssl()->visible()->get();
        $sessionCurrency = \WHMCS\Session::get("currency");
        $currency = getCurrency($ca->getUserId(), $sessionCurrency);
        $ca->assign("activeCurrency", $currency);
        $symantecPromoHelper = MarketConnect::factoryPromotionalHelper("symantec");
        $certificates = array(  );
        foreach( $symantecPromoHelper->getSslTypes() as $type => $names ) 
        {
            foreach( $names as $name ) 
            {
                $cert = $all->where("configoption1", $name)->first();
                if( !is_null($cert) ) 
                {
                    $cert->pricing($currency);
                    $certificates[$type][] = $cert;
                }

            }
        }
        $ca->assign("certificates", $certificates);
        $ca->assign("certificateFeatures", $symantecPromoHelper->getCertificateFeatures());
        $sslAdminPreviewSession = \WHMCS\Session::get("sslAdminPreview");
        if( $sslAdminPreviewSession ) 
        {
            $service = Service::where("name", "symantec")->first();
            if( !is_null($service) && $service->status ) 
            {
                $sslAdminPreviewSession = false;
                \WHMCS\Session::set("sslAdminPreview", false);
            }

        }

        $isAdminPreview = \App::getFromRequest("preview") && \WHMCS\Session::get("adminid");
        if( $isAdminPreview ) 
        {
            \WHMCS\Session::set("sslAdminPreview", true);
        }

        $ca->assign("inPreview", $isAdminPreview || $sslAdminPreviewSession);
        $ca->setTemplate("store/ssl/index");
        $ca->skipMainBodyContainer();
        return $ca;
    }

    public function handleSslCallback(\WHMCS\Http\Message\ServerRequest $request)
    {
        $orderNumber = $request->get("order_number");
        $customFieldValueCollection = \WHMCS\CustomField\CustomFieldValue::whereHas("customField", function($query)
{
    $query->where("fieldname", "=", "Order Number");
}

)->with("customField", "addon", "service")->where("value", "=", $orderNumber)->get();
        foreach( $customFieldValueCollection as $customFieldValue ) 
        {
            if( !$customFieldValue->customField ) 
            {
                continue;
            }

            switch( $customFieldValue->customField->type ) 
            {
                case "addon":
                    $model = $customFieldValue->addon;
                    $addonId = $model->id;
                    $serviceId = $model->serviceId;
                    break;
                case "product":
                    $model = $customFieldValue->service;
                    $addonId = 0;
                    $serviceId = $model->id;
                    break;
                default:
                    $model = null;
                    $sslRecord = null;
                    $serviceId = $addonId = 0;
            }
            if( !$model ) 
            {
                continue;
            }

            $sslRecord = \WHMCS\Service\Ssl::where("remoteid", "=", $orderNumber)->where("serviceid", "=", $serviceId)->where("addon_id", "=", $addonId)->where("module", "=", "marketconnect")->orderBy("id", "desc")->first();
            if( is_null($sslRecord) ) 
            {
                continue;
            }

            if( $sslRecord->status == \WHMCS\Service\Ssl::STATUS_COMPLETED ) 
            {
                return new \WHMCS\Http\Message\JsonResponse(array( "status" => "cert_already_installed" ));
            }

            if( $sslRecord->status != \WHMCS\Service\Ssl::STATUS_AWAITING_ISSUANCE ) 
            {
                return new \WHMCS\Http\Message\JsonResponse(array( "status" => "not_awaiting_notification" ));
            }

            $server = \WHMCS\Module\Server::factoryFromModel($model);
            if( !$server->functionExists("install_certificate") ) 
            {
                continue;
            }

            $installResponse = $server->call("install_certificate");
            if( $installResponse == "success" ) 
            {
                $sslRecord->status = \WHMCS\Service\Ssl::STATUS_COMPLETED;
                $sslRecord->save();
                return new \WHMCS\Http\Message\JsonResponse(array( "status" => "cert_installed" ));
            }

            break;
        }
        return new \WHMCS\Http\Message\JsonResponse(array( "status" => "order_not_found" ));
    }

    public function manage(\WHMCS\Http\Message\ServerRequest $request)
    {
        $ca = new \WHMCS\ClientArea();
        $ca->setPageTitle("Manage SSL Certificates");
        $ca->addToBreadCrumb("index.php", \Lang::trans("globalsystemname"));
        $ca->addToBreadCrumb("clientarea.php", "Client Area");
        $ca->addToBreadCrumb(routePath("store-ssl-certificates-manage"), "SSL Certificates");
        $ca->initPage();
        $ca->requireLogin();
        $sslProducts = \WHMCS\Service\Ssl::with("service", "service.product", "addon", "addon.productAddon", "addon.productAddon.moduleConfiguration", "addon.service", "addon.service.product")->where("userid", "=", $ca->getUserID())->get();
        $ca->assign("sslProducts", $sslProducts);
        $ca->assign("sslStatusAwaitingIssuance", \WHMCS\Service\Ssl::STATUS_AWAITING_ISSUANCE);
        $ca->assign("sslStatusAwaitingConfiguration", \WHMCS\Service\Ssl::STATUS_AWAITING_CONFIGURATION);
        $ca->setTemplate("managessl");
        return $ca;
    }

    public function resendApproverEmail(\WHMCS\Http\Message\ServerRequest $request)
    {
        $serviceId = $request->get("serviceId");
        $addonId = $request->get("addonId");
        $loggedInUserId = \WHMCS\Session::get("uid");
        $moduleInterface = new \WHMCS\Module\Server();
        if( $addonId ) 
        {
            $ownerId = \WHMCS\Service\Addon::find($addonId)->clientId;
            $moduleInterface->loadByAddonId($addonId);
        }
        else
        {
            $ownerId = \WHMCS\Service\Service::find($serviceId)->clientId;
            $moduleInterface->loadByServiceID($serviceId);
        }

        if( $loggedInUserId != $ownerId ) 
        {
            return new \WHMCS\Http\Message\JsonResponse(array( "success" => false, "message" => "Access Denied" ), 403);
        }

        try
        {
            $result = $moduleInterface->call("resendApproverEmail");
            if( $result == "success" ) 
            {
                return new \WHMCS\Http\Message\JsonResponse(array( "success" => true ));
            }

            return new \WHMCS\Http\Message\JsonResponse(array( "success" => false, "message" => "Unable to resend approver email" ), 500);
        }
        catch( \WHMCS\Exception $e ) 
        {
            return new \WHMCS\Http\Message\JsonResponse(array( "success" => false, "message" => "Unable to resend approver email" ), 500);
        }
    }

}


