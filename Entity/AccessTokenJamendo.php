<?php
namespace Cogipix\CogimixJamendoBundle\Entity;
use Cogipix\CogimixCommonBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
/**
 *
 * @author plfort - Cogipix
 * @ORM\Entity
 */
class AccessTokenJamendo
{

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    /**
     * @ORM\Column(type="string")
     * @var unknown_type
     */
    protected $accessToken;

    /**
     * @ORM\Column(type="string")
     * @var unknown_type
     */
    protected $refreshToken;

    /**
     * @ORM\Column(type="integer")
     * @var unknown_type
     */
    protected $expiresIn;

    /**
     * @ORM\Column(type="datetime")
     * @var unknown_type
     */

    protected $createdDate;

    /**
     * @ORM\Column(type="string")
     * @var unknown_type
     */
    protected $tokenType;

    /**
     * @ORM\OneToOne(targetEntity="Cogipix\CogimixCommonBundle\Entity\User")
     * @var User $user
     */

    protected $user;


    public function getId()
    {
        return $this->id;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;
    }

    public function getTokenType()
    {
        return $this->tokenType;
    }

    public function setTokenType($tokenType)
    {
        $this->tokenType = $tokenType;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }


    public function isExpired(){
        $now = new \DateTime();
        $createdDate = $this->createdDate;
        $expireDate = $createdDate->add(new \DateInterval("PT".$this->expiresIn."S"));
        return $now >= $expireDate;
    }
}
