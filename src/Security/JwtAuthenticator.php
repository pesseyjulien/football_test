<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class JwtAuthenticator extends
    AbstractGuardAuthenticator
{
    /** @var EntityManagerInterface */
    private $em;

    /**
     * @var ContainerBagInterface
     */
    private $params;

    /**
     * @var array
     */
    private $jwt;

    /**
     * @var string[]
     */
    private $allowedRoutes = [
        'user_register',
        'user_login'
    ];

    /**
     * @param EntityManagerInterface $em
     * @param ContainerBagInterface $params
     */
    public function __construct(
        EntityManagerInterface $em,
        ContainerBagInterface $params
    ) {
        $this->em = $em;
        $this->params = $params;
    }

    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return JsonResponse
     */
    public function start(
        Request $request,
        AuthenticationException $authException = null
    ) {
        $data = [
            'success' => false,
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data,
            Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(
        Request $request
    ) {
        return !in_array($request->attributes->get('_route'),
            $this->allowedRoutes);
    }

    /**
     * @param Request $request
     * @return array|mixed|string|null
     */
    public function getCredentials(
        Request $request
    ) {
        if (!$request->headers->has('authorization')) {
            throw new AuthenticationException('Missing Auth. header');
        }

        return $request->headers->get('authorization');
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|object|UserInterface|null
     */
    public function getUser(
        $credentials,
        UserProviderInterface $userProvider
    ) {
        try {
            $credentials = str_replace('Bearer ',
                '',
                $credentials);

            $this->jwt = (array)JWT::decode(
                $credentials,
                $this->params->get('JWT_SECRET'),
                ['HS256']
            );

            return $this->em->getRepository(User::class)->findByEmail($this->jwt['email']);
        } catch (\Exception $exception) {
            throw new AuthenticationException($exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return JsonResponse
     */
    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ) {
        return new JsonResponse([
            'success' => false,
            'message' => $exception->getMessage()
        ],
            Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null
     */
    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        $providerKey
    ) {
        return null;
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool|void
     */
    public function checkCredentials(
        $credentials,
        UserInterface $user
    ) {
        return $user->getId() === $this->jwt['id'];
    }

    /**
     * Stateless API
     */
    public function supportsRememberMe(
    )
    {
        return false;
    }

}
