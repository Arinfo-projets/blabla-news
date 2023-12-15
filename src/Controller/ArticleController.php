<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Image;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/article')]
class ArticleController extends AbstractController
{

    public function __construct(private PictureService $pictureService)
    {
    }

    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $this->denyAccessUnlessGranted("ROLE_ADMIN", null, "Vous n'êtes pas autorisé");
        } catch (AccessDeniedException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_main');
        }

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $images = $form->get('images')->getData();

            foreach ($images as $image) {

                $fichier = $this->pictureService->add($image, '');

                $img = new Image();
                $img->setPath($fichier);
                $article->addImage($img);
            }


            $article->setUser($this->getUser());
            $entityManager->persist($article);
            $entityManager->flush();
            $this->addFlash('success', "Article créer");
            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }


        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {

        try {
            $this->denyAccessUnlessGranted("ROLE_ADMIN", null, "Vous n'êtes pas autorisé");
        } catch (AccessDeniedException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_main');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();

            foreach ($images as $image) {

                $fichier = $this->pictureService->add($image, '');

                $img = new Image();
                $img->setPath($fichier);
                $article->addImage($img);
            }
            $this->addFlash('success', "Article modifier");

            $entityManager->flush();

            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        try {
            $this->denyAccessUnlessGranted("ROLE_ADMIN", null, "Vous n'êtes pas autoriser");
        } catch (AccessDeniedException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();

            foreach ($article->getImages() as $image){
                $this->pictureService->delete($image->getPath());
            }

        }
        $this->addFlash('success', "Article supprimer");
        return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
    }

}
