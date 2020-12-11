<?php 
namespace WHMCS\Admin\ApplicationSupport\Route\Middleware;


class Authentication implements \WHMCS\Route\Contracts\Middleware\StrategyInterface
{
    use \WHMCS\Route\Middleware\Strategy\DelegatingMiddlewareTrait;

    protected $adminAuth = NULL;

    public function _process(\WHMCS\Http\Message\ServerRequest $request, \Interop\Http\ServerMiddleware\DelegateInterface $delegate)
    {
        $auth = $this->getAdminAuth();
        if( !$auth ) 
        {
            $auth = new \WHMCS\Auth();
            $this->setAdminAuth($auth);
        }

        if( !$auth->isLoggedIn() ) 
        {
            $auth->routableRedirectToLogin($request);
            return $request;
        }

        $auth->getInfobyID(\WHMCS\Session::get("adminid"));
        if( $auth->isSessionPWHashValid() ) 
        {
            $auth->updateAdminLog();
            $user = \WHMCS\User\Admin::find(\WHMCS\Session::get("adminid"));
            $this->prepareAdminLanguage($user);
            return $request->withAttribute("authenticatedUser", $user);
        }

        $auth->destroySession();
        throw new \WHMCS\Exception\Authentication\LoginRequired("Admin Login Required");
    }

    protected function prepareAdminLanguage(\WHMCS\User\Admin $user)
    {
        if( \WHMCS\Session::get("adminlang") ) 
        {
            $language = \WHMCS\Session::get("adminlang");
        }
        else
        {
            $language = $user->language;
        }

        try
        {
            if( \AdminLang::getName() != $language ) 
            {
                \DI::forgetInstance("adminlang");
                $adminLang = \DI::make("adminlang", array( $language ));
                \AdminLang::swap($adminLang);
            }
            else
            {
                \DI::make("adminlang");
            }

        }
        catch( \Exception $e ) 
        {
            throw new \WHMCS\Exception\Fatal(\WHMCS\View\Helper::applicationError("Error Preparing Admin Language", $e->getMessage(), $e));
        }
    }

    protected function getAdminAuth()
    {
        return $this->adminAuth;
    }

    protected function setAdminAuth($adminAuth)
    {
        $this->adminAuth = $adminAuth;
        return $this;
    }

}


