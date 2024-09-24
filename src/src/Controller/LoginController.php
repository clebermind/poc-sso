<?php

namespace App\Controller;

use Jumbojett\OpenIDConnectClientException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Authentication;
use App\Factory\OpenIDConnectFactory;
use App\Service\OpenIDConnect;

class LoginController extends MainController
{
    private OpenIDConnect $openIDConnect;

    public function __construct(
        private readonly Authentication $authentication,
        OpenIDConnectFactory $openIDConnectFactory,
        TokenStorageInterface $tokenStorage,
    ) {
        $this->openIDConnect = $openIDConnectFactory->create();

        parent::__construct($tokenStorage);
    }

    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        return $this->render('Login/index.html.twig');
    }

    #[Route('/login/validate', name: 'login_validation')]
    public function validate(Request $request): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        try {
            $this->authentication->authenticateWithCredentials($username, $password);
        } catch (AuthenticationException $e) {
            return $this->redirectToRoute('login', ['message' => $e->getMessage()]);
        }

        return $this->redirectToRoute('user_list');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): Response
    {
        $this->authentication->logout();

        return $this->redirectToRoute('login', ['message' => 'Logged out successfully']);
    }

    #[Route('/login/sso', name: 'login_sso')]
    public function loginSso(Request $request): ?Response
    {
        try {
            $this->openIDConnect->authenticate();
        } catch (AuthenticationException|\Exception $e) {
            return $this->redirectToRoute('login', ['message' => $e->getMessage()]);
        }
    }

    #[Route('/login/sso/callback', name: 'login_sso_callback')]
    public function loginSsoCallback(Request $request): Response
    {
        try {
            $this->openIDConnect->authenticate();
        } catch (AuthenticationException|\Exception $e) {
            return $this->redirectToRoute('login', ['message' => $e->getMessage()]);
        }

        $accessToken = $this->openIDConnect->getAccessToken();
        $idToken = $this->openIDConnect->getIdToken();
        $refreshToken = $this->openIDConnect->getRefreshToken();

        try {
            $this->openIDConnect->verifyJWTSignature($accessToken);
        } catch (OpenIDConnectClientException|\Exception $e) {
            return $this->redirectToRoute('login', ['message' => $e->getMessage()]);
        }

        try {
            $this->authentication->authenticateSsoTokens($accessToken, $idToken, $refreshToken);
        } catch (AuthenticationException $e) {
            return $this->redirectToRoute('login', ['message' => $e->getMessage()]);
        }

        return $this->redirectToRoute('user_list');
    }
}
