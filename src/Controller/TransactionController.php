<?php
// src/Controller/TransactionController.php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\Category; // You need this to check for Category existence
use App\Repository\TransactionRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/transactions')]
class TransactionController extends AbstractController
{
    private Security $security;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * Retrieves all transactions for the authenticated user.
     * The Doctrine Extension will automatically filter by the current user.
     */
    #[Route('', name: 'transaction_index', methods: ['GET'])]
    public function index(TransactionRepository $transactionRepository): Response
    {
        // The CurrentUserExtension (which we added previously) automatically filters this query
        // to only include transactions where transaction.owner = current_user.
        $transactions = $transactionRepository->findAll();

        // Use serialization groups to control what data is exposed
        $json = $this->serializer->serialize($transactions, 'json', ['groups' => 'transaction:read']);

        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * Creates a new transaction.
     */
    #[Route('', name: 'transaction_new', methods: ['POST'])]
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $transaction->setOwner($user);
        // 1. Check for authenticated user (Owner)
        $user = $this->security->getUser();
        if (!$user) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        // 2. Validate essential data
        if (!isset($data['amount'], $data['description'], $data['type'], $data['date'], $data['category'])) {
             return new Response('Missing required fields (amount, description, type, date, category)', Response::HTTP_BAD_REQUEST);
        }

        // 3. Find the Category
        $categoryId = $data['category']['id'] ?? $data['category'];
        if (!$categoryId) {
            return new Response('Category ID is missing or invalid.', Response::HTTP_BAD_REQUEST);
        }

        $category = $categoryRepository->find($categoryId);
        if (!$category || $category->getOwner() !== $user) {
            // Must check if the category belongs to the user for security
            return new Response('Category not found or does not belong to the current user.', Response::HTTP_BAD_REQUEST);
        }

        // 4. Create and set Transaction properties
        $transaction = new Transaction();
        $transaction->setOwner($user);
        $transaction->setCategory($category);
        $transaction->setAmount((float) $data['amount']);
        $transaction->setDescription($data['description']);
        $transaction->setType($data['type']);

        try {
            // Date handling: Ensure it is a DateTimeImmutable object
            $date = new \DateTimeImmutable($data['date']);
            $transaction->setDate($date);
        } catch (\Exception $e) {
            return new Response('Invalid date format.', Response::HTTP_BAD_REQUEST);
        }
        
        // 5. Validate the Transaction object using entity constraints
        $errors = $this->validator->validate($transaction);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return new Response(json_encode(['errors' => $errorMessages]), Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/json']);
        }


        // 6. Save to database
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        // 7. Return the new transaction data
        $json = $this->serializer->serialize($transaction, 'json', ['groups' => 'transaction:read']);

        return new Response($json, Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
    }
    
    /**
     * Retrieves a single transaction.
     */
    #[Route('/{id}', name: 'transaction_show', methods: ['GET'])]
    public function show(Transaction $transaction): Response
    {
        // Security check: ensure the transaction belongs to the current user
        if ($transaction->getOwner() !== $this->security->getUser()) {
            // Note: CurrentUserExtension already filters this, but this is a secondary safety check
            return new Response('Not Found', Response::HTTP_NOT_FOUND); 
        }

        $json = $this->serializer->serialize($transaction, 'json', ['groups' => 'transaction:read']);

        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * Updates an existing transaction.
     */
    #[Route('/{id}', name: 'transaction_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, Transaction $transaction, CategoryRepository $categoryRepository): Response
    {
        // Security check: ensure the transaction belongs to the current user
        $user = $this->security->getUser();
        if ($transaction->getOwner() !== $user) {
            return new Response('Unauthorized or Not Found', Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Update properties only if they exist in the request body
        if (isset($data['amount'])) {
            $transaction->setAmount((float) $data['amount']);
        }
        if (isset($data['description'])) {
            $transaction->setDescription($data['description']);
        }
        if (isset($data['type'])) {
            $transaction->setType($data['type']);
        }
        if (isset($data['date'])) {
            try {
                $date = new \DateTimeImmutable($data['date']);
                $transaction->setDate($date);
            } catch (\Exception $e) {
                return new Response('Invalid date format.', Response::HTTP_BAD_REQUEST);
            }
        }
        
        // Handle Category update
        if (isset($data['category'])) {
            $categoryId = $data['category']['id'] ?? $data['category'];
            $category = $categoryRepository->find($categoryId);
            
            // Security check: ensure the new category exists and belongs to the user
            if (!$category || $category->getOwner() !== $user) {
                return new Response('Category not found or does not belong to the current user.', Response::HTTP_BAD_REQUEST);
            }
            $transaction->setCategory($category);
        }

        // Validate the updated object
        $errors = $this->validator->validate($transaction);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return new Response(json_encode(['errors' => $errorMessages]), Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/json']);
        }

        $this->entityManager->flush();

        $json = $this->serializer->serialize($transaction, 'json', ['groups' => 'transaction:read']);

        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * Deletes a transaction.
     */
    #[Route('/{id}', name: 'transaction_delete', methods: ['DELETE'])]
    public function delete(Transaction $transaction): Response
    {
        // Security check: ensure the transaction belongs to the current user
        if ($transaction->getOwner() !== $this->security->getUser()) {
            return new Response('Unauthorized or Not Found', Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($transaction);
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
