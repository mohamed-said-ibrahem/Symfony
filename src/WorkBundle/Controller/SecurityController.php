<?php 

namespace WorkBundle\Controller;

use Captcha\Bundle\CaptchaBundle\Security\Core\Exception\InvalidCaptchaException;
use FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use ReCaptcha\ReCaptcha; // Include the recaptcha lib
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WorkBundle\Entity\User;


class SecurityController extends BaseController
{
  use \WorkBundle\Helper\ControllerHelper;
  
  public function loginAction(Request $request)
  {
    /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
    $session = $request->getSession();

    if (class_exists('\Symfony\Component\Security\Core\Security')) {
      $authErrorKey = Security::AUTHENTICATION_ERROR;
      $lastUsernameKey = Security::LAST_USERNAME;
    } else {
      // BC for SF < 2.6
      $authErrorKey = SecurityContextInterface::AUTHENTICATION_ERROR;
      $lastUsernameKey = SecurityContextInterface::LAST_USERNAME;
    }

    // get captcha object instance
    $captcha = $this->get('captcha')->setConfig('LoginCaptcha');
    if ($request->isMethod('POST')) {
      $recaptcha = new ReCaptcha('6LftWT0UAAAAAIvF7gU8qscf-Vc5ZhkTTLBv490U');
      $resp = $recaptcha->verify($request->request
      ->get('g-recaptcha-response'), $request->getClientIp());
      if (!$resp->isSuccess()) {
        // Do something if the submit wasn't valid ! Use the message to show something
        $message = "The reCAPTCHA wasn't entered correctly. Go back and try it again.";
        echo $message;
      }else{
      // validate the user-entered Captcha code when the form is submitted
      $code = $request->request->get('captchaCode');
      $isHuman = $captcha->Validate($code);
      if ($isHuman) {
        // Captcha validation passed, check username and password

        // LOGIC FOR JWT AUTH "MY LOGIC :)"
        $username = $request->request->get('_username');
        $password = $request->request->get('_password');
        $user = $this->getDoctrine()
        ->getRepository('WorkBundle:User')
        ->findOneBy(['username' => $username]);
        if ($user) 
        {
          $isValid = $this->get('security.password_encoder')
          ->isPasswordValid($user, $password);
          if($isValid)
          {
            $token = $this->getToken($user);
            $response = new Response($this->serialize(['token' => $token]), Response::HTTP_OK);
            $response2 = $this->setBaseHeaders($response);   
            // dump($response);die;
            // dump($response2);die;

          }
        }



        return $this->redirectToRoute('fos_user_security_check', [
          'request' => $request], 307);
      } else {
        // Captcha validation failed, set an invalid captcha exception in $authErrorKey attribute
        $invalidCaptchaEx = new InvalidCaptchaException('CAPTCHA validation failed, try again.');
        $request->attributes->set($authErrorKey, $invalidCaptchaEx);

        // set last username entered by the user
        $username = $request->request->get('_username', null, true);
        $request->getSession()->set($lastUsernameKey, $username);
      }
    }
  }

    // get the error if any (works with forward and redirect -- see below)
    if ($request->attributes->has($authErrorKey)) {
      $error = $request->attributes->get($authErrorKey);
    } elseif (null !== $session && $session->has($authErrorKey)) {
      $error = $session->get($authErrorKey);
      $session->remove($authErrorKey);
    } else {
      $error = null;
    }

    if (!$error instanceof AuthenticationException) {
      $error = null; // The value does not come from the security component.
    }

    // last username entered by the user
    $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

    if ($this->has('security.csrf.token_manager')) {
      $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();
    } else {
      // BC for SF < 2.4
      $csrfToken = $this->has('form.csrf_provider')
        ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
        : null;
    }
    
    return $this->renderLogin(array(
      'last_username' => $lastUsername,
      'error' => $error,
      'csrf_token' => $csrfToken,
      'captcha_html' => $captcha->Html()
    ));
  }
  


/**
 * Returns token for user.
 *
 * @param User $user
 *
 * @return array
 */
  public function getToken(User $user)
  {
      return $this->container->get('lexik_jwt_authentication.encoder')
              ->encode([
                  'username' => $user->getUsername(),
                  'exp' => $this->getTokenExpiryDateTime(),
              ]);
  }

  /**
 * Returns token expiration datetime.
 *
 * @return string Unixtmestamp
 */
  private function getTokenExpiryDateTime()
  {
      $tokenTtl = $this->container->getParameter('lexik_jwt_authentication.token_ttl');
      $now = new \DateTime();
      $now->add(new \DateInterval('PT'.$tokenTtl.'S'));
   
      return $now->format('U');
  }

}