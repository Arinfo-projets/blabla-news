<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ArticleRepository;
use App\Repository\ImageRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/image')]
class ImageController extends AbstractController
{

    public function __construct(private PictureService $pictureService, private ArticleRepository $articleRepository)
    {
    }

    #[Route('/', name: 'app_image_index', methods: ['GET'])]
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('image/index.html.twig', [
            'images' => $imageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_image_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $this->denyAccessUnlessGranted("ROLE_ADMIN", null, "Vous n'êtes pas autorisé");
        } catch (AccessDeniedException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_main');
        }

        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($image);
            $entityManager->flush();

            return $this->redirectToRoute('app_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('image/new.html.twig', [
            'image' => $image,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_image_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Image $image, EntityManagerInterface $entityManager): Response
    {
        try {
            $this->denyAccessUnlessGranted("ROLE_ADMIN", null, "Vous n'êtes pas autorisé");
        } catch (AccessDeniedException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_main');
        }

        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->pictureService->delete($image->getPath());
            return $this->redirectToRoute('app_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('image/edit.html.twig', [
            'image' => $image,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_image_delete', methods: ['POST'])]
    public function delete(Request $request, Image $image, EntityManagerInterface $entityManager): Response
    {
        try {
            $this->denyAccessUnlessGranted("ROLE_ADMIN", null, "Vous n'êtes pas autorisé");
        } catch (AccessDeniedException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_main');
        }

        if ($this->isCsrfTokenValid('delete' . $image->getId(), $request->request->get('_token'))) {
            $articleId = $image->getArticle()->getId();

            $article = $this->articleRepository->find($articleId);

            if ($article && Count($article->getImages()) === 1) {
                $this->addFlash('error', "Impossible de supprimer l'image");
                return $this->redirectToRoute('app_article_edit', ['id' => $articleId], Response::HTTP_SEE_OTHER);
            }

            $this->pictureService->delete($image->getPath());
            $entityManager->remove($image);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_edit', ['id' => $articleId], Response::HTTP_SEE_OTHER);
    }
}
