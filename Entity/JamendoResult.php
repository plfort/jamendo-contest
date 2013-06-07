<?php
namespace Cogipix\CogimixJamendoBundle\Entity;

use Cogipix\CogimixCommonBundle\Entity\TrackResult;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
/**
  * @JMSSerializer\AccessType("public_method")
 * @author plfort - Cogipix
 */
class JamendoResult extends TrackResult
{



    public function __construct(){
        parent::__construct();

    }

    public function setUrl($url)
    {
        $this->pluginProperties['url'] =$url;
    }


    public function getEntryId(){
        return $this->getId();
    }

}
