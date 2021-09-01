<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UsersController extends
    AbstractController
{
    /**
     * @Route("/users/register", name="user_register", methods={"POST"})
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    public function register(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder
    ) {
        $data = \json_decode(
            $request->getContent(),
            true
        );
        if (empty($data)) {
            return $this->json(
                [
                    'success' => false,
                    'message' => 'empty params'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        /** @var User $user */
        $user = $serializer->deserialize(
            \json_encode(
                \array_filter(
                    $data
                )
            ),
            User::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => new User()
            ]
        );

        //validate user
        $errors = $validator->validate(
            $user
        );
        if (count(
                $errors
            ) > 0) {
            return $this->json(
                [
                    'success' => false,
                    'message' => $errors
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        //get doctrine
        $em = $this->getDoctrine()->getManager();

        //check duplicate
        $check = $em->getRepository(
            User::class
        )->findByEmail(
            $user->getEmail()
        );
        if ($check) {
            return $this->json(
                [
                    'success' => false,
                    'message' => 'User with same email already exists'
                ],
                Response::HTTP_CONFLICT
            );
        }

        //encode password
        $user->setPassword(
            $encoder->encodePassword(
                $user,
                $user->getPassword()
            )
        );

        //save
        $em->persist(
            $user
        );
        $em->flush();

        //return response
        $serializedEntity = $serializer->serialize(
            $user,
            'json',
            array('groups' => ['read_user'])
        );
        return new Response(
            $serializedEntity,
            Response::HTTP_CREATED,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @Route("/users/login", name="user_login", methods={"POST"})
     */
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $encoder
    ) {
        $data = \json_decode(
            $request->getContent(),
            true
        );
        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(
                [
                    'success' => false,
                    'message' => 'missing email or password',
                ],
                404
            );
        }

        //find and check user password
        $user = $userRepository->findByEmail(
            $data['email']
        );
        if (!$user || !$encoder->isPasswordValid(
                $user,
                $data['password']
            )) {
            return $this->json(
                [
                    'success' => false,
                    'message' => 'email or password is wrong',
                ],
                400
            );
        }

        $payload = [
            "id" => $user->getId(),
            "email" => $user->getEmail(),
            "exp" => (new \DateTime())->modify(
                "+1 hour"
            )->getTimestamp(),
        ];

        $jwt = JWT::encode(
            $payload,
            $this->getParameter(
                'JWT_SECRET'
            )
        );

        return $this->json(
            [
                'success' => true,
                'token' => $jwt
            ]
        );
    }
}
