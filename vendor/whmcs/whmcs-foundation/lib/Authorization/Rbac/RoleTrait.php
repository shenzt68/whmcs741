<?php 
namespace WHMCS\Authorization\Rbac;


trait RoleTrait
{
    use PermissionTrait;

    public function allow(array $itemsToAllow = array(  ))
    {
        $itemsToAllow = array_filter($itemsToAllow);
        if( empty($itemsToAllow) ) 
        {
            return $this;
        }

        $data = $this->getData();
        foreach( $itemsToAllow as $item ) 
        {
            if( is_string($item) ) 
            {
                $data[$item] = 1;
            }

        }
        $this->setData($data);
        return $this;
    }

    public function deny(array $itemsToDeny = array(  ))
    {
        $itemsToDeny = array_filter($itemsToDeny);
        if( empty($itemsToDeny) ) 
        {
            return $this;
        }

        $data = $this->getData();
        foreach( $itemsToDeny as $item ) 
        {
            if( is_string($item) ) 
            {
                $data[$item] = 0;
            }

        }
        $this->setData($data);
        return $this;
    }

}


