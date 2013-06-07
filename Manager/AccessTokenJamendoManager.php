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

    private $om;
    private $jamendoApi;

    public function __construct($om, $jamendoApi)
    {
        $this->om = $om;
        $this->jamendoApi = $jamendoApi;
    }

    public function removeAccessToken($user)
    {
        $jamendoToken = $this->om
                ->getRepository('CogimixJamendoBundle:AccessTokenJamendo')
                ->findOneByUser($user);
        if ($jamendoToken !== null) {
            $this->om->remove($jamendoToken);
        }
        $user->removeRole('ROLE_JAMENDO');
        $this->om->flush();
        return true;

    }
    /**
     *
     * @param unknown_type $user
     */
    public function getUserAccessToken($user)
    {
        $jamendoToken = $this->om
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
                        $this->om->flush();
                    } else {
                        $this->logger->err($this->jamendoApi->lastError);
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
        $this->om->persist($accessToken);

    }

}
