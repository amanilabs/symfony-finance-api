<?php
// src/Security/JsonLoginAuthenticator.php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

// NOTE: We no longer need 'use Symfony\Component\Security\Core\Security;' 
// because we are avoiding the Security::LAST_USERNAME constant.

class JsonLoginAuthenticator extends AbstractAuthenticator
{
    // ... supports() method is correct
    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST') && str_ends_with($request->getPathInfo(), '/api/login_check');
    }

    public function authenticate(Request $request): Passport
    {
        $data = json_decode($request->getContent(), true);
        
        // --- START OF FIX: Removed the line causing ClassNotFoundError ---
        // $request->getSession()->set(Security::LAST_USERNAME, $data['email'] ?? '');
        // --- END OF FIX ---

        return new Passport(
            new UserBadge($data['email'] ?? ''),
            new PasswordCredentials($data['password'] ?? '')
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
    
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new Response('Authentication Required', Response::HTTP_UNAUTHORIZED);
    }
}
