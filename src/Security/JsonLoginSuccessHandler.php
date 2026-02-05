<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class JsonLoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    /**
     * This is called when an interactive authentication attempt succeeds.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $user = $token->getUser();

        // This line attempts to create the real JWT token using the HMAC secret key.
        // If this returns an empty string, the environment is fundamentally broken for token generation.
        $token = $this->jwtManager->create($user); 

        return new JsonResponse([
            'message' => 'Authentication successful!',
            'token' => $token, // Will be the real JWT string if successful
            'user_id' => $user->getId(),
            'email' => $user->getUserIdentifier(),
        ]);
    }
}
