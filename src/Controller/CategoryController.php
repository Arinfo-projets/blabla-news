<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/category')]
class CategoryController extends AbstractController
{

    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $this->denyAccessUnlessGranted("ROLE_ADMIN", null, "Vous n'êtes pas autorisé");
        } catch (AccessDeniedException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_main');
        }

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', "Catégorie ajouté");
            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        try {
            $this->denyAccessUnlessGranted("ROLE_ADMIN", null, "Vous n'êtes pas autorisé");
        } catch (AccessDeniedException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_main');
        }

        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        try {
            $this->denyAccessUnlessGranted("ROLE_ADMIN", null, "Vous n'êtes pas autorisé");
        } catch (AccessDeniedException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_main');
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', "Catégorie modifier");
            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        try {
            $this->denyAccessUnlessGranted("ROLE_ADMIN", null, "Vous n'êtes pas autorisé");
        } catch (AccessDeniedException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_main');
        }

        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }
        $this->addFlash('success', "Catégorie supprimer");
        return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
    }
}
