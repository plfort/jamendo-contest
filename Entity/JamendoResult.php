<?php
namespace Cogipix\CogimixJamendoBundle\Entity;

use Cogipix\CogimixCommonBundle\Entity\Song;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMSSerializer;
/**
  * @JMSSerializer\AccessType("public_method")
 * @ORM\MappedSuperclass()
 * @author plfort - Cogipix
 */
class JamendoResult extends Song
{

    public function setUrl($url)
    {
        $this->pluginProperties['url'] =$url;
    }


    public function getEntryId(){
        return $this->getId();
    }

}
