<?php
namespace Cogipix\CogimixJamendoBundle\Services;

use Cogipix\CogimixJamendoBundle\Entity\JamendoResult;

use Cogipix\CogimixCommonBundle\MusicSearch\AbstractMusicSearch;

class JamendoMusicSearch extends AbstractMusicSearch
{

    /**
     *
     * @var CustomProviderInfo $customProviderInfo
     */
    private $jamendoApi;
    private $resultBuilder;


    public function __construct(JamendoApi $jamendoApi,ResultBuilder $resultBuilder)
    {
        $this->jamendoApi=$jamendoApi;
        $this->resultBuilder=$resultBuilder;
    }

    protected function parseResponse($output)
    {
        $tracks = array();
        try {
            $tracks = $this->resultBuilder->createArrayFromJamendoTracks($output);

        } catch (\Exception $ex) {
            $this->logger->info($ex->getMessage());
            return array();
        }
        return $tracks;
        $tracks = array();

    }


    protected function executeQuery()
    {

        $output= $this->jamendoApi->searchTracks($this->searchQuery->getSongQuery());
        if($output !== false){

            return $this->parseResponse($output);
        }else{
            $this->logger->err($this->jamendoApi->lastError);
        }


    }

    protected function buildQuery()
    {


    }

    public function getName()
    {
       return "Jamendo";
    }

    public function getAlias()
    {
        return 'jamendoservice';
    }

    public function getResultTag()
    {
        return 'jam';
    }

    public function getDefaultIcon()
    {
        return 'bundles/cogimixjamendo/images/jamendo-icon.png';
    }



}

?>