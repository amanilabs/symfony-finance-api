<?php
// src/Controller/CategoryController.php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse; // Added for validation errors

#[Route('/api/categories')]
class CategoryController extends AbstractController
{
    private SerializerInterface $serializer;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    // --- GET ALL CATEGORIES ---
    #[Route('', name: 'category_get_all', methods: ['GET'])]
    public function getAll(CategoryRepository $categoryRepository): Response
    {
        // Get the current user from the security token
        $user = $this->getUser();
        
        // Find categories owned by the current user
        $categories = $categoryRepository->findBy(['owner' => $user]);

        // Serialize using the 'category:read' group defined in the Category entity
        $jsonContent = $this->serializer->serialize(
            $categories, 
            'json', 
            ['groups' => 'category:read']
        );

        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    // --- CREATE NEW CATEGORY ---
    #[Route('', name: 'category_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        // Deserialize the JSON request body into a new Category object
        /** @var Category $category */
        $category = $this->serializer->deserialize($request->getContent(), Category::class, 'json');
        
        // Set the owner to the current authenticated user
        $category->setOwner($this->getUser());

        // Validate the entity (e.g., name must not be blank)
        $errors = $this->validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // Serialize the created object for the response, including the generated ID
        $jsonContent = $this->serializer->serialize(
            $category, 
            'json', 
            ['groups' => 'category:read']
        );

        return new Response($jsonContent, Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
    }

    // --- GET SINGLE CATEGORY ---
    #[Route('/{id}', name: 'category_get_one', methods: ['GET'])]
    public function getOne(Category $category): Response
    {
        // Check if the current user is the owner of the category
        if ($category->getOwner() !== $this->getUser()) {
            // Throw 403 Forbidden if not the owner
            throw $this->createAccessDeniedException('You do not have access to this category.');
        }

        $jsonContent = $this->serializer->serialize(
            $category, 
            'json', 
            ['groups' => 'category:read']
        );

        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    // --- UPDATE CATEGORY ---
    #[Route('/{id}', name: 'category_update', methods: ['PUT'])]
    public function update(Request $request, Category $category): Response
    {
        // Check if the current user is the owner
        if ($category->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not have access to update this category.');
        }

        // Use the existing category object to deserialize, updating its properties
        $updatedCategory = $this->serializer->deserialize(
            $request->getContent(), 
            Category::class, 
            'json', 
            ['object_to_populate' => $category]
        );

        // Re-validate the updated entity
        $errors = $this->validator->validate($updatedCategory);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // We only need to flush since the object is already managed by Doctrine
        $this->entityManager->flush();

        $jsonContent = $this->serializer->serialize(
            $updatedCategory, 
            'json', 
            ['groups' => 'category:read']
        );

        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    // --- DELETE CATEGORY ---
    #[Route('/{id}', name: 'category_delete', methods: ['DELETE'])]
    public function delete(Category $category): Response
    {
        // Check if the current user is the owner
        if ($category->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not have access to delete this category.');
        }

        // NOTE: Doctrine will automatically prevent deletion if this category is referenced 
        // by any existing transactions (due to the foreign key constraint).

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        // Return a successful but empty response
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

