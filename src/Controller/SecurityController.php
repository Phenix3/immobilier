<?php

namespace App\Controller;

use App\Util\TokenGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\User;
use App\Form\UserType;
use App\Notification\EmailConfirmationNotification;
use App\Repository\UserRepository;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY", message="Vous etes déjà connecté")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
    {
        return $this->render('security/login.html.twig', [
            'last_email' => $utils->getLastUsername(),
            'errors' => $utils->getLastAuthenticationError()
        ]);
    }

    /**
     * @Route("register", name="register")
     * @param UserPasswordEncoderInterface $encoder
     * @param Request $request
     * @param EmailConfirmationNotification $notification
     * @return Response
     */
    public function register(UserPasswordEncoderInterface $encoder, Request $request, EmailConfirmationNotification $notification)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $user->setConfirmationToken(TokenGenerator::generateToken(60));
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();
            $notification->notify($user);
            $this->addFlash(
                'success',
                'Votre compte à bien été crée. Veillez confirmer votre compte en cliquant sur le lien qui vous a été envoyé dans votre boite mail.');
            $this->redirectToRoute('home');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("security/confirm/{id}/{token}", name="security.confirm")
     * @param User $user
     * @param Request $request
     * @param ObjectManager $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function confirm(User $user, Request $request)
    {
        if ($user->getConfirmationToken() === $request->get('token')) {
            $user->setConfirmationToken(null)
                ->setEmailValidatedAt(new \DateTime());
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Votre compte a bien ete confirme. Vous pouvez vous connecter');
            return  $this->redirectToRoute('login');
        } else {
            $this->addFlash('danger', 'Ce token ne correspond pas');
            return  $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("security/user/reset-password", name="security.reset_password_request")
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @param UserRepository $repository
     * @param ObjectManager $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function resetPasswordRequest(Request $request, \Swift_Mailer $mailer, UserRepository $repository, ObjectManager $manager)
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->add('Envoyer', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $repository->findOneBy(['email' => $form->getData()['email']]);
            if (!$user) {
                $this->addFlash('danger', 'Ce compte n\'existe pas. Veillez corriger votre E-mail s\'il y\'a une erreur');
                return $this->redirectToRoute('security.reset_password_request');
            }
            $token = TokenGenerator::generateToken(60);
            $message = (new \Swift_Message('Reset Password'))
                ->setFrom('contact@agence.com')
                ->setTo($user->getEmail())
                ->setBody($this->render('emails/reset.html.twig', [
                    'reset_token' => $token,
                    'user' => $user
                ]), 'text/html');
            $mailer->send($message);
            $user->setResetToken($token);
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Veillez cliquer sur l\'E-mail qui vous a ete envoyer pour renouveller votre mot de passe.');
            return $this->redirectToRoute('home');
        }

        return $this->render('security/reset-password.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("security/reset-password/{id?}/{token?}", name="security.reset_password")
     * @param User $user
     * @param ObjectManager $manager
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function resetPassword(User $user, ObjectManager $manager, Request $request, UserPasswordEncoderInterface $encoder)
    {
        if ($user === null || $request->get('token') === null) {
            throw new UnauthorizedHttpException('', 'Acces non authorise');
        }

        $form = $this->createFormBuilder()
            ->add('password', PasswordType::class, [])
            ->add('confirm_password', PasswordType::class)
            ->add('Modifier mon mot de passe', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
if ($user->getResetToken() === $request->get('token')) {

    if ($form->isSubmitted() && $form->isValid()) {
        $hash = $encoder->encodePassword($user, $form->getData()['password']);
        $user->setPassword($hash);
        $user->setResetToken(null);

        $manager->persist($user);
        $manager->flush();

        $this->addFlash('success', 'Votre mot de passe a bien ete reinitilaiser.<br/> Vous pouvz vous connecter a present');
        return $this->redirectToRoute('login');
    }
    } else {
        $this->addFlash('danger', 'Impossible de réinitialiser votre mot de passe avec ce lien.<br/> Veillez demander un nouveau lien de réinitialisation.');
    }
            return $this->render('security/new-password.html.twig', [
                'form' => $form->createView()]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        $this->addFlash('success', 'Vous avez bien été deconnecté');
    }
}
