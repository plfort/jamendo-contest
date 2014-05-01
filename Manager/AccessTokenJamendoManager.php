<?php
namespace Cogipix\CogimixJamendoBundle\Manager;
use Cogipix\CogimixCommonBundle\Manager\AbstractManager;

use Cogipix\CogimixCommonBundle\Utils\LoggerAwareInterface;

/**
 *
 * @author plfort - Cogipix
 *
 */
class AccessTokenJamendoManager extends AbstractManager
{

    
    private $jamendoApi;

    public function __construct($jamendoApi)
    {
        
        $this->jamendoApi = $jamendoApi;
    }

    public function removeAccessToken($user)
    {
        $jamendoToken = $this->em
                ->getRepository('CogimixJamendoBundle:AccessTokenJamendo')
                ->findOneByUser($user);
        if ($jamendoToken !== null) {
            $this->em->remove($jamendoToken);
        }
        $user->removeRole('ROLE_JAMENDO');
        $this->em->flush();
        return true;

    }
    /**
     *
     * @param unknown_type $user
     */
    public function getUserAccessToken($user)
    {
        $jamendoToken = $this->em
                ->getRepository('CogimixJamendoBundle:AccessTokenJamendo')
                ->findOneByUser($user);
        if ($jamendoToken !== null) {
            if ($jamendoToken->isExpired()) {
                try {
                    $newToken = $this->jamendoApi->refreshToken($jamendoToken);
                    if ($newToken !== null) {
                        $jamendoToken
                                ->setAccessToken($newToken->getAccessToken());
                        $jamendoToken
                                ->setRefreshToken($newToken->getRefreshToken());
                        $jamendoToken->setExpiresIn($newToken->getExpiresIn());
                        $jamendoToken->setCreatedDate(new \DateTime());
                        $this->em->flush();
                    } else {
                        $this->logger->err("Error refreshtoken");
                        $this->logger->err($this->jamendoApi->lastError);
                        $this->removeAccessToken($user);
                    }
                } catch (\Exception $ex) {
                    $this->logger->err($ex->getMessage());
                }
            }
        }
        return $jamendoToken;
    }

    public function setAccessToken($accessToken, $user)
    {
        $accessTokenDb = $this->getUserAccessToken($user);
        if ($accessTokenDb !== null) {
            $this->em->remove($accessTokenDb);
        }
        $accessToken->setUser($user);
        $user->addRole('ROLE_JAMENDO');
        $this->em->persist($accessToken);

    }

}
