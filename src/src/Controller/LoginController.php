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
use InvalidArgumentException;
use LogicException;
use Exception;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LoginController extends MainController
{
    private OpenIDConnect $openIDConnect;
    private bool $isSooEnabled = true;
    private ?string $disabledSooReason = null;

    public function __construct(
        private readonly Authentication $authentication,
        OpenIDConnectFactory $openIDConnectFactory,
        TokenStorageInterface $tokenStorage,
    ) {
        try {
            $this->openIDConnect = $openIDConnectFactory->create();
        } catch (InvalidArgumentException $exception) {
            $this->isSooEnabled = false;
            $this->disabledSooReason = 'Set up Identity Provider is not available';
            error_log($exception->getMessage());
        }catch (LogicException $exception) {
            $this->isSooEnabled = false;
            $this->disabledSooReason = 'Identity Provider is not available';
            error_log($exception->getMessage());
        } catch (Exception $exception) {
            $this->isSooEnabled = false;
            $this->disabledSooReason = 'Unknown error';
            error_log($exception->getMessage());
        }

        parent::__construct($tokenStorage);
    }

    #[Route('/login', name: 'login')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function login(): Response
    {
        return $this->render(
            'Login/index.html.twig',
            [
                'isSooEnabled' => $this->isSooEnabled,
                'disabledSooReason' => $this->disabledSooReason,
            ]
        );
    }

    #[Route('/login/validate', name: 'login_validation')]
    #[IsGranted('PUBLIC_ACCESS')]
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
    #[IsGranted('PUBLIC_ACCESS')]
    public function logout(): Response
    {
        $this->authentication->logout();

        return $this->redirectToRoute('login', ['message' => 'Logged out successfully']);
    }

    #[Route('/login/sso', name: 'login_sso')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function loginSso(Request $request): ?Response
    {
        try {
            $this->openIDConnect->authenticate();
        } catch (AuthenticationException|\Exception $e) {
            return $this->redirectToRoute('login', ['message' => $e->getMessage()]);
        }

        return null;
    }

    #[Route('/login/sso/callback', name: 'login_sso_callback')]
    #[IsGranted('PUBLIC_ACCESS')]
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
