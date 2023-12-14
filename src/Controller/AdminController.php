<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{

    public function __construct(
        private ArticleRepository $articleRepository,
        private CategoryRepository $categoryRepository
    ) {
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        try{
            $this->denyAccessUnlessGranted("ROLE_ADMIN", null, "Vous n'êtes pas autorisé");
        }catch(AccessDeniedException $e){
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'articles' => $this->articleRepository->findAll(),
            'categories' => $this->categoryRepository->findAll()
        ]);
    }
}
