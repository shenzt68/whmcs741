<?php 
namespace WHMCS\Admin\Search\Controller;


class ClientController extends AbstractSearchController
{
    public function getSearchTerm(\WHMCS\Http\Message\ServerRequest $request)
    {
        return array( "searchTerm" => $request->get("dropdownsearchq", null), "clientId" => $request->get("clientId", null) );
    }

    public function getSearchable()
    {
        return new \WHMCS\Search\Client();
    }

    public function search($searchTerm = NULL)
    {
        if( is_array($searchTerm) ) 
        {
            $clientId = (isset($searchTerm["clientId"]) ? $searchTerm["clientId"] : null);
            $searchTerm = (isset($searchTerm["searchTerm"]) ? $searchTerm["searchTerm"] : null);
        }
        else
        {
            $clientId = null;
        }

        $searchFor = array( "clientId" => $clientId, "searchTerm" => $searchTerm );
        return $this->getSearchable()->search($searchFor);
    }

}


