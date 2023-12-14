<?php

namespace App\Controller;

use App\Data\SearchDataArticle;
use App\Entity\Contact;
use App\Form\ContactType;
use App\Form\SearchArticleForm;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    public function __construct(private ArticleRepository $articleRepository)
    {
    }

    #[Route('/', name: 'app_main', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = new SearchDataArticle();
        $contact = new Contact();

        $formfilter = $this->createForm(SearchArticleForm::class, $data);
        $formfilter->handleRequest($request);
        $formContact = $this->createForm(ContactType::class, $contact);
        $formContact->handleRequest($request);


        if ($formContact->isSubmitted() && $formContact->isValid()) {
            $contact->setUser($this->getUser());
            $contact->setCreatedAt(new \DateTime);
            $entityManager->persist($contact);
            $entityManager->flush();
            $this->addFlash('success', "message envoyé");
            return $this->redirectToRoute('app_main', [], Response::HTTP_SEE_OTHER);
        }



        $articles = $this->articleRepository->findSearch($data);


        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'articles' => $articles,
            'formfilter' => $formfilter,
            'formContact' => $formContact
        ]);
    }
}
