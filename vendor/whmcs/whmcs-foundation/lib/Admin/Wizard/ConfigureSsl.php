<?php 
namespace WHMCS\Admin\Wizard;


class ConfigureSsl extends Wizard
{
    protected $wizardName = "ConfigureSsl";

    public function __construct()
    {
        $this->steps = array( array( "name" => "Csr", "stepName" => "Provide CSR", "stepDescription" => "Enter server information" ), array( "name" => "Contacts", "stepName" => "Contact Information", "stepDescription" => "Provide admin contact info" ), array( "name" => "Approval", "stepName" => "Approval Method", "stepDescription" => "Choose approval method" ), array( "name" => "Complete", "hidden" => true ) );
    }

    public function hasRequiredAdminPermissions()
    {
        return \WHMCS\User\Admin\Permission::currentAdminHasPermissionName("Perform Server Operations");
    }

}


