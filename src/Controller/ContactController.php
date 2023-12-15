<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/contact')]
class ContactController extends AbstractController
{

    public function __construct(private MailerInterface $mailer)
    {
    }

    #[Route('/', name: 'app_contact_index', methods: ['GET'])]
    public function index(ContactRepository $contactRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted("ROLE_ADMIN", null, "Vous n'êtes pas autorisé");
        } catch (AccessDeniedException $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }


        return $this->render('contact/index.html.twig', [
            'contacts' => $contactRepository->findBy([], ['createdAt' => 'desc']),
        ]);
    }

    #[Route('/new', name: 'app_contact_new', methods: ['POST', 'GET'])]
    public function new(Request $request, EntityManagerInterface $entityManager)
    {
        $contact = new Contact();

        $formContact = $this->createForm(ContactType::class, $contact);
        $formContact->handleRequest($request);


        if ($formContact->isSubmitted() && $formContact->isValid()) {
            $contact->setUser($this->getUser());
            $contact->setCreatedAt(new \DateTime);
            $entityManager->persist($contact);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from('email.test@gmaild.fr')
                ->to('yoann.piard@gmail.com')
                ->subject("Demande d'information")
                ->htmlTemplate('email/contact_template_email.html.twig')
                ->context([
                    'contact' => $contact,
                ]);

            $this->mailer->send($email);

            $this->addFlash('success', "Message envoyé");
            return $this->redirectToRoute('app_main', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contact/new.html.twig', [
            'formContact' => $formContact,
        ]);
    }

    #[Route('/{id}', name: 'app_contact_show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        return $this->render('contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/{id}', name: 'app_contact_delete', methods: ['POST'])]
    public function delete(Request $request, Contact $contact, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $contact->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contact);
            $entityManager->flush();
        }

        $this->addFlash('success', "Message supprimé");
        return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
    }
}
