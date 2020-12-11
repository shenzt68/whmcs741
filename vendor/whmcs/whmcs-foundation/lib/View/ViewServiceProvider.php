<?php 
namespace WHMCS\View;


class ViewServiceProvider extends \WHMCS\Application\Support\ServiceProvider\AbstractServiceProvider
{
    public function register()
    {
        $this->app->singleton("asset", function()
{
    return new Asset(\WHMCS\Utility\Environment\WebHelper::getBaseUrl(ROOTDIR, $_SERVER["SCRIPT_NAME"]));
}

);
        $this->app->bind("View\\Engine\\Php\\Admin", function()
{
    return new Engine\Php\Admin();
}

);
        $this->app->bind("View\\Engine\\Smarty\\Admin", function()
{
    return new Engine\Smarty\Admin();
}

);
    }

}


