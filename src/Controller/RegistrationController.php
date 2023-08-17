<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\RegistrationFormType;
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
class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request,VerifyEmailHelperInterface $verifyEmailHelper, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UserAuthAuthenticator $authenticator, EntityManagerInterface $entityManager , MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
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
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );
            $email = (new TemplatedEmail())
            ->from(new Address('skander.damergi@esprit.tn', 'CORIDE Bot'))
            ->to($user->getEmail())
            ->subject('Your Email confirmation link')
            ->htmlTemplate('security/emailverify.html.twig')
            ->context([
                'signedUrl' => $signatureComponents->getSignedUrl()], 
            );
            $mailer->send($email);
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route("/verify", name : 'app_verify_email')]
    public function verifyUserEmail(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        
        $utilisateur = $userRepository->find($request->query->get('id'));
        if (!$utilisateur) {
            throw $this->createNotFoundException('user not found');
        }
         
        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $utilisateur->getId(),
                $utilisateur->getEmail()
            );
            $utilisateur->setIsVerified(true);
            $entityManager->flush();
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
            // The email has been successfully confirmed. Handle the success case here.
        } catch (VerifyEmailExceptionInterface $e) {
            // The email could not be confirmed. Handle the error case here.
            throw $this->createNotFoundException();
        }
        return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
       
    }
}
