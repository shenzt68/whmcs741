<?php 
namespace WHMCS\Module\Addon\Mailchimp;


class SettingsHelper
{
    public $vars = array(  );

    public function __construct($vars)
    {
        $this->vars = $vars;
    }

    public function request($key)
    {
        return (isset($_REQUEST[$key]) ? $_REQUEST[$key] : null);
    }

    public function get($key)
    {
        return (isset($this->vars[$key]) ? $this->vars[$key] : null);
    }

    public function set($key, $value)
    {
        $setting = \WHMCS\Module\Addon\Setting::firstOrNew(array( "module" => $this->vars["module"], "setting" => $key ));
        $setting->value = $value;
        $setting->save();
        $this->vars[$key] = $value;
    }

}


