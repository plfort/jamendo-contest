<?php
namespace Cogipix\CogimixJamendoBundle\ViewHooks\Playlist;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

use Cogipix\CogimixCommonBundle\Utils\SecurityContextAwareInterface;

use Cogipix\CogimixCommonBundle\ViewHooks\Playlist\PlaylistRendererInterface;
/**
 *
 * @author plfort - Cogipix
 *
 */
class PlaylistRenderer implements PlaylistRendererInterface,SecurityContextAwareInterface{

    private $accessTokenManager;
    private $jamendoApi;
    private $securityContext;


    public function __construct($accessTokenManager,$jamendoApi){
       $this->accessTokenManager=$accessTokenManager;
       $this->jamendoApi= $jamendoApi;
    }

    /**
     * Template file for rendering playlists list
     */
    public function getListTemplate(){
        return 'CogimixJamendoBundle:Playlist:list.html.twig';
    }

    /**
     * Get the user's playlists from Jamendo
     * @return array
     */
    public function getPlaylists(){
        $user=$this->getCurrentUser();
        if($user!==null){

            $jamendoToken = $this->accessTokenManager->getUserAccessToken($user);
            if($jamendoToken!==null){

                $playlists= $this->jamendoApi->getUserPlaylists($jamendoToken);

                if($playlists !==false){
                    return $playlists;
                }else{
                    echo $this->jamendoApi->lastError;
                }
            }
        }

        return array();
    }

    public function setSecurityContext(
            SecurityContextInterface $securityContext)
    {
        $this->securityContext=$securityContext;

    }

    protected function getCurrentUser() {
        $user = $this->securityContext->getToken()->getUser();
        if ($user instanceof AdvancedUserInterface){
            return $user;
        }

        return null;
    }

    public function getTag(){
        return 'jamendo';
    }
}