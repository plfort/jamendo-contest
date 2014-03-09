<?php
namespace Cogipix\CogimixJamendoBundle\Services;

use Cogipix\CogimixJamendoBundle\Entity\JamendoResult;

use Cogipix\CogimixCommonBundle\ResultBuilder\ResultBuilderInterface;
class ResultBuilder implements ResultBuilderInterface
{

    /**
     * Map a Jamendo track representation a Cogimix track
     * @param unknown_type $jamendoTrack
     */
    public function createFromJamendoTrack($jamendoTrack)
    {
        //need more tests
        $item =null;
        if(!empty($jamendoTrack)){
            $item = new JamendoResult();
            $item->setId($jamendoTrack['id']);
            $item->setArtist($jamendoTrack['artist_name']);
            $item->setTitle($jamendoTrack['name']);
            $item->setThumbnails($jamendoTrack['album_image']);
            $item->setUrl($jamendoTrack['audio']);
            $item->setTag($this->getResultTag());
            $item->setIcon($this->getDefaultIcon());

        }
        return $item;
    }
    /**
     * Map an array of Jamendo tracks to an array of Cogimix tracks
     * @param unknown_type $jamendoTracks
     */
    public function createArrayFromJamendoTracks($jamendoTracks)
    {
        $tracks =array();
        if(is_array($jamendoTracks) && !empty($jamendoTracks)){
            foreach($jamendoTracks as $jamendoTrack){
                $item = $this->createFromJamendoTrack($jamendoTrack);
                if($item !==null){
                    $tracks[]=$item;
                }
            }
        }
        return $tracks;
    }


    public function getResultTag(){
        return 'jam';
    }

    public function getDefaultIcon(){
        return '/bundles/cogimixjamendo/images/jamendo-icon.png';
    }

}
