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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    public function __construct(private ArticleRepository $articleRepository, private MailerInterface $mailer)
    {
    }

    #[Route('/', name: 'app_main', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $data = new SearchDataArticle();
        $formfilter = $this->createForm(SearchArticleForm::class, $data);
        $formfilter->handleRequest($request);

        $articles = $this->articleRepository->findSearch($data);

        return $this->render('main/index.html.twig', [
            'articles' => $articles,
            'formfilter' => $formfilter,
        ]);
    }
}
