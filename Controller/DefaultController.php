<?php
namespace Cogipix\CogimixJamendoBundle\Controller;

use Cogipix\CogimixJamendoBundle\Entity\AccessTokenJamendo;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Cogipix\CogimixCommonBundle\Utils\AjaxResult;
/**
 * @Route("/jamendo")
 * @author plfort - Cogipix
 *
 */
class DefaultController extends Controller
{
    /**
     * @Route("/login",name="_jamendo_login", options={"expose"=true})
     *
     */
    public function loginAction()
    {
        $response = new AjaxResult();
        $jamendoApi = $this->get('cogimix_jamendo.jamendo_api');
        $response->setSuccess(true);
        list($state, $authorizeUrl) = $jamendoApi->getWebAuthUrl($this->generateUrl('_jamendo_login_finish',array(),true));
        $this->get('session')->set('jamendoAuthState',$state);
        $response->addData('authUrl',$authorizeUrl);
        return $response->createResponse();
    }

    /**
     * @Secure("ROLE_USER")
     * @Route("/loginfinish",name="_jamendo_login_finish", options={"expose"=true})
     *
     */
    public function loginFinishAction(Request $request){
        $code = $request->query->get('code');

        $jamendoApi = $this->get('cogimix_jamendo.jamendo_api');
        $stateAuth=$this->get('session')->get('jamendoAuthState');
        $this->get('session')->remove('jamendoAuthState');
        $redirectUri = $this->generateUrl('_jamendo_login_finish',array(),true);
        $accessTokenJamendo =$jamendoApi->finishWebAuth($code,$stateAuth,$redirectUri);
        $success = false;
        if($accessTokenJamendo !== null){
            $success=true;
            $em = $this->getDoctrine()->getEntityManager();
            $user = $this->getUser();
            $AccessTokenJamendoDb = $em->getRepository('CogimixJamendoBundle:AccessTokenJamendo')->findOneByUser($user);
            if($AccessTokenJamendoDb !=null){
                $em->remove($AccessTokenJamendoDb);

            }
                $accessTokenJamendo->setUser($user);
                $user->addRole('ROLE_JAMENDO');
                $em->persist($accessTokenJamendo);


            $em->flush();
            $this->get('security.context')->getToken()->setAuthenticated(false);
        }else{
            $this->get('logger')->err($jamendoApi->lastError);
        }

        return $this->render('CogimixJamendoBundle:Login:finish.html.twig',array('success'=>$success));
    }

    /**
     * @Secure("ROLE_JAMENDO")
     * @Route("/logout",name="_jamendo_logout", options={"expose"=true})
     *
     */

    public function logout(){
        $response = new AjaxResult();
        $accessTokenManager = $this->get('cogimix_jamendo.access_token_manager');
        if($accessTokenManager->removeAccessToken($this->getUser()) == true ){

            $this->get('security.context')->getToken()->setAuthenticated(false);
            $response->setSuccess(true);
            $response->addData('loginLink', $this->renderView('CogimixJamendoBundle:Login:loginLink.html.twig'));
        }
        return $response->createResponse();
    }

    /**
     * @Secure("ROLE_JAMENDO")
     * @Route("/playlist/tracks/{id}",name="_jamendo_playlist_songs", options={"expose"=true})
     *
     */
    public function getPlaylistTracksAction($id){
        $response = new AjaxResult();
        $jamendoApi = $this->get('cogimix_jamendo.jamendo_api');
        $accessTokenManager = $this->get('cogimix_jamendo.access_token_manager');
        $resultBuilder = $this->get('cogimix_jamendo.result_builder');
        $accessToken= $accessTokenManager->getUserAccessToken($this->getUser());
        if($accessToken!==null){
            $tracks=$jamendoApi->getPlaylistTracks($id,$accessToken);
            if($tracks){
                $response->setSuccess(true);
                $response->addData('tracks', $resultBuilder->createArrayFromJamendoTracks($tracks));
            }
        }else{
            $this->get('logger')->err('No Jamendo accesstoken');
         }

         return $response->createResponse();
    }

}
