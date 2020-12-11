<?php 
namespace WHMCS\Config;


abstract class AbstractConfig extends \ArrayObject
{
    private $defaultValue = "";

    public function __construct(array $data = array(  ))
    {
        parent::setFlags(parent::ARRAY_AS_PROPS);
        parent::__construct($data);
    }

    public function setData(array $data)
    {
        $this->exchangeArray($data);
        return $this;
    }

    public function getData()
    {
        return $this->getArrayCopy();
    }

    public function setDefaultReturnValue($value)
    {
        $this->defaultValue = $value;
    }

    public function OffsetGet($property)
    {
        if( $this->OffsetExists($property) ) 
        {
            return parent::OffsetGet($property);
        }

        return $this->defaultValue;
    }

}


