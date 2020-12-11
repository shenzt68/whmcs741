<?php 
namespace WHMCS\View\Menu\Factory;


class WhmcsExtension implements \Knp\Menu\Factory\ExtensionInterface
{
    public function buildOptions(array $options)
    {
        return array_merge(array( "uri" => null, "badge" => null, "order" => null, "icon" => null, "headingHtml" => null, "bodyHtml" => null, "footerHtml" => null, "disabled" => false ), $options);
    }

    public function buildItem(\Knp\Menu\ItemInterface $item, array $options)
    {
        $item->setUri($options["uri"])->setBadge($options["badge"])->setOrder($options["order"])->setIcon($options["icon"])->setHeadingHtml($options["headingHtml"])->setBodyHtml($options["bodyHtml"])->setFooterHtml($options["footerHtml"]);
        if( $options["disabled"] ) 
        {
            $item->disable();
        }

    }

}


