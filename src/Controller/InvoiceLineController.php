<?php

namespace App\Controller;

use App\Entity\InvoiceLine;
use App\Form\InvoiceLineType;
use App\Repository\InvoiceLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invoice-line')]
final class InvoiceLineController extends AbstractController
{
    #[Route(name: 'app_invoice_line_index', methods: ['GET'])]
    public function index(InvoiceLineRepository $invoiceLineRepository): Response
    {
        return $this->render('invoice_line/index.html.twig', [
            'invoice_lines' => $invoiceLineRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_invoice_line_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $invoiceLine = new InvoiceLine();
        $form = $this->createForm(InvoiceLineType::class, $invoiceLine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($invoiceLine);
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_line_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice_line/new.html.twig', [
            'invoice_line' => $invoiceLine,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_line_show', methods: ['GET'])]
    public function show(InvoiceLine $invoiceLine): Response
    {
        return $this->render('invoice_line/show.html.twig', [
            'invoice_line' => $invoiceLine,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_invoice_line_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, InvoiceLine $invoiceLine, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvoiceLineType::class, $invoiceLine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_invoice_line_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoice_line/edit.html.twig', [
            'invoice_line' => $invoiceLine,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_line_delete', methods: ['POST'])]
    public function delete(Request $request, InvoiceLine $invoiceLine, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$invoiceLine->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($invoiceLine);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invoice_line_index', [], Response::HTTP_SEE_OTHER);
    }
}
