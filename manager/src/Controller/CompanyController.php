<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Company\Entity\Company;
use App\Model\User\Entity\User\User;
use App\Model\Company\UseCase\Create;
use App\Model\User\UseCase\Edit;
use App\Model\User\UseCase\Role;
use App\Model\User\UseCase\SignUp\Confirm;
use App\ReadModel\Company\CompanyFetcher;
use App\ReadModel\Company\Filter;
use App\ReadModel\Work\Members\Member\MemberFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/company", name="company")
 * @IsGranted("ROLE_MANAGE_USERS")
 */
class CompanyController extends AbstractController
{
    private const PER_PAGE = 10;
    
    private $errors;
    
    public function __construct(ErrorHandler $errors)
    {
        $this->errors = $errors;
    }
    
    /**
     * @Route("", name="")
     * @param     Request        $request
     * @param     CompanyFetcher $fetcher
     * @return    Response
     */
    public function index(Request $request, CompanyFetcher $fetcher): Response
    {
        $filter = new Filter\Filter();
        $form = $this->createForm(Filter\Form::class, $filter);
        $form->handleRequest($request);
        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            self::PER_PAGE,
            $request->query->get('sort', 'date'),
            $request->query->get('direction', 'desc'),
        );
        
        return $this->render(
            'app/company/index.html.twig',
            [
                'pagination' => $pagination,
                'form' => $form->createView(),
            ]
        );
    }
    
    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler): Response
    {
        $command = new Create\Command();
        
        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $company = $handler->handle($command);
                $this->addFlash('success', 'Компания ' .$company. ' успешно создана');
                return $this->redirectToRoute('company');
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }
        
        return $this->render('app/users/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/{id}/edit", name=".edit")
     * @param User $user
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(User $user, Request $request, Edit\Handler $handler): Response
    {
        if ($user->getId()->getValue() === $this->getUser()->getId()) {
            $this->addFlash('error', 'Unable to edit yourself.');
            return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
        }
        
        $command = Edit\Command::fromUser($user);
        
        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }
        
        return $this->render('app/users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/{id}/role", name=".role")
     * @param User $user
     * @param Request $request
     * @param Role\Handler $handler
     * @return Response
     */
    public function role(User $user, Request $request, Role\Handler $handler): Response
    {
        if ($user->getId()->getValue() === $this->getUser()->getId()) {
            $this->addFlash('error', 'Unable to change role for yourself.');
            return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
        }
        
        $command = Role\Command::fromUser($user);
        
        $form = $this->createForm(Role\Form::class, $command);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }
        
        return $this->render('app/users/role.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/{id}/confirm", name=".confirm", methods={"POST"})
     * @param User $user
     * @param Request $request
     * @param Confirm\Manual\Handler $handler
     * @return Response
     */
    public function confirm(User $user, Request $request, Confirm\Manual\Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('confirm', $request->request->get('token'))) {
            return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
        }
        
        $command = new Confirm\Manual\Command($user->getId()->getValue());
        
        try {
            $handler->handle($command);
        } catch (\DomainException $e) {
            $this->errors->handle($e);
            $this->addFlash('error', $e->getMessage());
        }
        
        return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
    }
    
    /**
     * @Route("/{id}", name=".show")
     * @param Company $company
     * @return Response
     */
    public function show(Company $company): Response
    {
        dump($company);
        return $this->render('app/company/show.html.twig', compact('company'));
    }
    
    /**
     * @Route("/{id}/company", name=".company")
     * @param User $user
     * @param MemberFetcher $members
     * @return Response
     */
    public function addCompany(User $user, MemberFetcher $members): Response
    {
        $member = $members->find($user->getId()->getValue());
        
        $form = '';
        
        return $this->render('app/users/company.html.twig', compact('user', 'member'));
    }
}
