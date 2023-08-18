<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\RegistrationFormTypeAdmin;
use App\Form\UserEditType;
use App\Security\UserAuthAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use DateTime;
class AdminController extends AbstractController
{
    #[Route('/add', name: 'app_adduser')]
    public function add(Request $request,VerifyEmailHelperInterface $verifyEmailHelper, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UserAuthAuthenticator $authenticator, EntityManagerInterface $entityManager , MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormTypeAdmin::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $uploadedFile = $form->get('img')->getData();

            if ($uploadedFile) {
                // generate a unique file name
                $newFileName = md5(uniqid()) . '.' . $uploadedFile->guessExtension();
                $targetDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/images';
            
                // move the uploaded file to the target directory
                $uploadedFile->move(
                    $targetDirectory, // specify the target directory where the file should be saved
                    $newFileName      // specify the new file name
                );
                    
                            // set the image path to the path of the uploaded file
                            $user->setImg('uploads/images/' . $newFileName);
            
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
        }
            $date=new DateTime();
            $user->setCreatedAt($date);
            $user->setIsVerified(true);
            $entityManager->persist($user);
            $entityManager->flush();
         
            return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Admin/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/display', name: 'app_index')]
    public function index(UserRepository $utilisateurRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('Admin/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }
    #[Route('/{id}/ban', name: 'app_ban',methods:['GET'])]
    public function ban(MailerInterface $mailer,User $user,UserRepository $utilisateurRepository,EntityManagerInterface $entityManager ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user->setIsBanned(true);
        $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            
            $email = (new TemplatedEmail())
            ->from(new Address('skander.damergi@esprit.tn', 'CORIDE Bot'))
            ->to($user->getEmail())
            ->subject('Your Email confirmation link')
            ->htmlTemplate('security/ban.html.twig')
            ->context([
                'message' => 'You have been banned. Please contact support for more details.',
            ]);
            
            $mailer->send($email);
        return $this->render('Admin/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);

    }
    #[Route('/{id}/unban', name: 'app_unban',methods:['GET'])]
    public function unban(MailerInterface $mailer,User $user,UserRepository $utilisateurRepository,EntityManagerInterface $entityManager ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user->setIsBanned(false);
        $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            
            
        return $this->render('Admin/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);

    }
    #[Route('/{id}', name: 'app_show', methods: ['GET'])]
    public function show(User $utilisateur): Response
    {
        return $this->render('Admin/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }
    #[Route('/{id}', name: 'app_delete', methods: ['POST'])]
    public function delete(Request $request, User $utilisateur, UserRepository $utilisateurRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $utilisateurRepository->remove($utilisateur, true);
        }

        return $this->redirectToRoute('app_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'app_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $utilisateur, UserRepository $utilisateurRepository,EntityManagerInterface $entityManager ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $form = $this->createForm(UserEditType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('img')->getData();

            if ($uploadedFile) {
                // generate a unique file name
                $newFileName = md5(uniqid()) . '.' . $uploadedFile->guessExtension();
                $targetDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/images';
            
                // move the uploaded file to the target directory
                $uploadedFile->move(
                    $targetDirectory, // specify the target directory where the file should be saved
                    $newFileName      // specify the new file name
                );
                    
                            // set the image path to the path of the uploaded file
                            $utilisateur->setImg('uploads/images/' . $newFileName);
                
            }
            $entityManager->persist($utilisateur);
            $entityManager->flush();
            return $this->redirectToRoute('app_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Admin/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }
}