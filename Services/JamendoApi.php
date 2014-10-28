<?php
namespace Cogipix\CogimixJamendoBundle\Services;
use Cogipix\CogimixJamendoBundle\Entity\AccessTokenJamendo;

class JamendoApi
{

    private $clientId;
    private $secret;
    private $apiUrl = "https://api.jamendo.com/v3.0/";
    public $lastError;

    private $CURL_OPTS = array(CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
            //bad bad bad
            CURLOPT_SSL_VERIFYPEER => false);

    public function __construct($key, $secret)
    {
        $this->clientId = $key;
        $this->secret = $secret;
    }
    
    /**
     * Call Jamendo API (v3)
     * @param unknown_type $entity
     * @param unknown_type $subentity
     * @param unknown_type $parameters
     * @return array or false
     */
    private function callApi($entity, $subentity = null, $parameters = array())
    {
        if ($entity == null) {
            return false;
        }
        $paramtersString = '';
        foreach($parameters as $key => $value){
            $paramtersString.= '&'.$key.'='.urlencode($value);
        }
        
        $baseParameters = sprintf("?client_id=%s&format=json&%s", $this->clientId,$paramtersString);
                

        $entityPart = $entity;
        if ($subentity !== null) {
            $entityPart .= $subentity;
        }
        $finalUrl = sprintf("%s%s%s",$this->apiUrl,$entityPart,$baseParameters);
        $c = curl_init($finalUrl);
        curl_setopt_array($c, $this->CURL_OPTS);
        $output = curl_exec($c);

        if ($output === false) {
            $this->lastError = curl_error($c);
            return false;
        }
        //var_dump($output);die();
        return json_decode($output,true);
    }

    public function popularTracks($limit = 50)
    {
        $parameters = array(
            'limit'=>$limit,
            'order'=>'popularity_week'
        );
      //  $parameters = sprintf('&limit=%d&order=popularity_week',$limit);
        $result = $this->callApi('tracks/', null, $parameters);
        if ($result != false) {
            if (isset($result['headers']) && isset($result['headers']['code'])) {
                if ($result['headers']['code'] === 0) {
                    if (isset($result['results'])) {
                        return $result['results'];
                    } else {
                        return array();
                    }
                } else {
                    $this->lastError = $this
                    ->mapErrorCode($result['headers']['code']);
                }
            }
        }
        return false;
    }

    public function searchTracks($query, $limit = 30)
    {
        $parameters = array(
            'limit'=>$limit,
            'search'=>$query
        );
        
       // $parameters = sprintf('&namesearch=%s&limit=%d', urlencode($query), $limit);
        $result = $this->callApi('tracks/', null, $parameters);
        if ($result != false) {
            if (isset($result['headers']) && isset($result['headers']['code'])) {
                if ($result['headers']['code'] === 0) {
                    if (isset($result['results'])) {
                        return $result['results'];
                    } else {
                        return array();
                    }
                } else {
                    $this->lastError = $this
                            ->mapErrorCode($result['headers']['code']);
                }
            }
        }
        return false;
    }

    public function getUserPlaylists($accessToken)
    {
        $parameters = array(
            'limit'=>50,
            'access_token'=>$accessToken->getAccessToken()
        );
        
      /*  $parameters = sprintf('&access_token=%s&limit=%d',
                $accessToken->getAccessToken(), 30);*/
        $result = $this->callApi('playlists/', null, $parameters);
       
        if ($result != false) {

            if (isset($result['headers']) && isset($result['headers']['code'])) {
                if ($result['headers']['code'] === 0) {

                    if (isset($result['results'])) {
                        return $result['results'];
                    } else {
                        return array();
                    }
                } else {
                    $this->lastError = $this
                            ->mapErrorCode($result['headers']['code']);
                }
            }
        }
        return false;
    }

    public function getTracksFromIds(array $ids,$accessToken = null)
    {
        $parameters = array(
            'limit'=>100,
            'id'=>implode('+', $ids)
        );
        //$parameters = sprintf('&limit=%d&id=%s', 100, implode('+', $ids));
        $result = $this->callApi('tracks/', null, $parameters);

        if (isset($result['headers']) && isset($result['headers']['code'])) {
            if ($result['headers']['code'] === 0) {

                if (isset($result['results'])) {
                    return $result['results'];
                } else {
                    return array();
                }
            } else {
                $this->lastError = $this
                        ->mapErrorCode($result['headers']['code']);
            }
        }
        return false;
    }

    public function getPlaylistTracks($playlistId,$accessToken=null)
    {
        $parameters = array(   
            'limit'=>100,
            'id'=>$playlistId);
        if($accessToken!==null){
            $parameters['access_token'] = $accessToken->getAccessToken();
          /*  $parameters = sprintf('&access_token=%s&limit=%d&id=%s',
                    $accessToken->getAccessToken(), 100, $playlistId);*/
        }else{
            
            //$parameters = sprintf('&limit=%d&id=%s', 100, $playlistId);
        }

        $result = $this->callApi('playlists/', 'tracks/', $parameters);
        //var_dump($result);die();
        if ($result != false) {
            if (isset($result['headers']) && isset($result['headers']['code'])) {
                if ($result['headers']['code'] === 0) {
                    if (isset($result['results'])) {
                       // var_dump($result);die();
                        if (count($result['results']) > 0) {

                            $trackIds = array();
                            if(isset($result['results'][0]['tracks']) && count($result['results'][0]['tracks'])>0){
                                foreach ($result['results'][0]['tracks'] as $k=>$trackItem) {

                                    $trackIds[] = $trackItem['id'];
                                }
                            }


                            if (!empty($trackIds)) {
                                $tracksDetails = $this->getTracksFromIds($trackIds,$accessToken);
                                return $tracksDetails;
                            }

                        }
                    } else {
                        return array();
                    }
                } else {
                    $this->lastError = $this
                            ->mapErrorCode($result['headers']['code']);
                }
            }
        }
        return false;
    }

    public function getWebAuthUrl($redirectUrl)
    {
        $state = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $authorizeUrl = $this->apiUrl . 'oauth/authorize'
                . sprintf('?client_id=%s&redirect_uri=%s&state=%s',
                        $this->clientId, $redirectUrl, $state);
        return array($state, $authorizeUrl);
    }

    public function finishWebAuth($code, $state, $redirect_uri)
    {

        $postFields = array('client_id' => $this->clientId,
                'client_secret' => $this->secret, 'code' => $code,
                'state' => $state, 'redirect_uri' => $redirect_uri,
                'grant_type' => 'authorization_code');
        $this->CURL_OPTS[CURLOPT_POST] = true;
        $this->CURL_OPTS[CURLOPT_POSTFIELDS] = $postFields;
        $result = $this->callApi('oauth/', 'grant/');
        unset($this->CURL_OPTS[CURLOPT_POST]);
        unset($this->CURL_OPTS[CURLOPT_POSTFIELDS]);
        $accessTokenJamendo = null;
        if ($result != false && !empty($result)) {

            if (!isset($result['error'])) {
                $accessTokenJamendo = new AccessTokenJamendo();
                $accessTokenJamendo->setAccessToken($result['access_token']);
                $accessTokenJamendo->setTokenType($result['token_type']);
                $accessTokenJamendo->setRefreshToken($result['refresh_token']);
                $accessTokenJamendo->setExpiresIn($result['expires_in']);
                $accessTokenJamendo->setCreatedDate(new \DateTime());

            } else {
                $this->lastError = $result['error_description'];
            }
        }
        return $accessTokenJamendo;
    }

    public function refreshToken(AccessTokenJamendo $accessToken)
    {

        $postFields = array('client_id' => $this->clientId,
                'client_secret' => $this->secret,
                'refresh_token' => $accessToken->getRefreshToken(),
                'grant_type' => 'refresh_token');
        $this->CURL_OPTS[CURLOPT_POST] = true;
        $this->CURL_OPTS[CURLOPT_POSTFIELDS] = $postFields;
        $result = $this->callApi('oauth/', 'grant/');
        unset($this->CURL_OPTS[CURLOPT_POST]);
        unset($this->CURL_OPTS[CURLOPT_POSTFIELDS]);
        $accessTokenJamendo = null;
        if ($result !== false && !empty($result)) {

            if (!isset($result['error'])) {
                $accessTokenJamendo = new AccessTokenJamendo();
                $accessTokenJamendo->setAccessToken($result['access_token']);
                $accessTokenJamendo->setTokenType($result['token_type']);
                $accessTokenJamendo->setRefreshToken($result['refresh_token']);
                $accessTokenJamendo->setExpiresIn($result['expires_in']);
                $accessTokenJamendo->setCreatedDate(new \DateTime());
                $accessTokenJamendo->setUser($accessToken->getUser());
            } else {
                $this->lastError = $result['error_description'];
            }

        }
        return $accessTokenJamendo;
    }

    private function mapErrorCode($code)
    {
        switch ($code) {
        case 1:
            return "Exception	A generic not well identificated error occurred";
        case 2:
            return "ttp Method	The received http method is not supported for this method";
        case 3:
            return "Value	One of the received parameters has a value not respecting requirements such as range, format, etc";
        case 4:
            return "Required Parameter	A required parameter has not been received, or it was empty";
        case 5:
            return "Invalid Client Id	The client Id received does not exists or cannot be validated";
        case 6:
            return "Rate Limit Exceeded	This requester app or the requester IP have exceeded the permitted rate limit";
        case 7:
            return "Method Not Found	Jamendo Api rest-like reading methods are in the format api.jamendo.com/version/entity/subentity (subentity is optional). This exception is raised when entity and/or subentity methods don't exist";
        case 8:
            return "Needed Parameter	A needed parameter has not been received or/and this needed parameter has not the needed value";
        case 9:
            return "Format	This exception is raised when the api call requests an unkown output format";
        case 10:
            return "Entry Point	The used IP and/or port is not recognized as valid entry point";
        case 11:
            return "Suspended Application	The client application has been suspended (illegal usage, ...)";
        case 12:
            return "Access Token	Invalid Access Token.";
        case 13:
            return "Insufficient Scope	Insufficient scope. The request requires higher privileges than provided by the access token";
        default:
            return "The response code " . $code . " is not mapped";
        }
    }
}
