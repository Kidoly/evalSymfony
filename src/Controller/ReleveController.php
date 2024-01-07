<?php

namespace App\Controller;

use App\Entity\Releve;
use App\Form\ReleveType;
use App\Repository\ReleveRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/releve')]
class ReleveController extends AbstractController
{
    #[Route('/', name: 'app_releve_index', methods: ['GET'])]
    public function index(ReleveRepository $releveRepository): Response
    {
        $releves = $releveRepository->findAll();

        $matrices = [];
        foreach ($releves as $releve) {
            $matrices[] = $this->transformMatrix($this->generateMatrix($releve->getReleveBrut()));
        }
        return $this->render('releve/index.html.twig', [
            'releves' => $releves,
            'matrices' => $matrices,
        ]);
    }

    public function generateMatrix($matrixString)
    {
        $s = explode("/", $matrixString);
        $matrix = [];
        foreach ($s as $key => $value) {
            $matrix[$key] = $this->randomList($value);
        }
        return $matrix;
    }

    public function randomList($n)
    {
        $n = intval($n);
        if (0 <= $n && $n <= 9) {
            while (true) {
                $l = array_map(function () {
                    return rand(0, 1);
                }, range(1, 9));

                $counts = array_count_values($l);

                // Check if key '1' exists in the counts array
                if (isset($counts[1]) && $counts[1] == $n) {
                    break;
                }
            }
            return $l;
        } else {
            throw new \Exception("Error: n must be an integer between 0 and 9.");
        }
    }

    public function transformMatrix($matrix)
    {
        $result = [];

        // Take the 3 first values of the 3 first lists
        $result[] = array_merge(array_slice($matrix[0], 0, 3), array_slice($matrix[1], 0, 3), array_slice($matrix[2], 0, 3));

        // Take the 3-6 values of the 3 first lists
        $result[] = array_merge(array_slice($matrix[0], 3, 3), array_slice($matrix[1], 3, 3), array_slice($matrix[2], 3, 3));

        // Take the 3 last values of the 3 first lists
        $result[] = array_merge(array_slice($matrix[0], -3), array_slice($matrix[1], -3), array_slice($matrix[2], -3));

        // Take the 3 values of the 3-6 list
        $result[] = array_merge(array_slice($matrix[3], 0, 3), array_slice($matrix[4], 0, 3), array_slice($matrix[5], 0, 3));

        // Take the 3-6 values of the 3-6 list
        $result[] = array_merge(array_slice($matrix[3], 3, 3), array_slice($matrix[4], 3, 3), array_slice($matrix[5], 3, 3));

        // Take the 3 last values of the 3-6 list
        $result[] = array_merge(array_slice($matrix[3], -3), array_slice($matrix[4], -3), array_slice($matrix[5], -3));

        // Take the 3 first values of the 3 last list
        $result[] = array_merge(array_slice($matrix[count($matrix) - 3], 0, 3), array_slice($matrix[count($matrix) - 2], 0, 3), array_slice($matrix[count($matrix) - 1], 0, 3));

        // Take the 3-6 values of the 3 last list
        $result[] = array_merge(array_slice($matrix[count($matrix) - 3], 3, 3), array_slice($matrix[count($matrix) - 2], 3, 3), array_slice($matrix[count($matrix) - 1], 3, 3));

        // Take the 3 last values of the 3 last list
        $result[] = array_merge(array_slice($matrix[count($matrix) - 3], -3), array_slice($matrix[count($matrix) - 2], -3), array_slice($matrix[count($matrix) - 1], -3));

        return $result;
    }

    #[Route('/new', name: 'app_releve_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $releve = new Releve();
        $form = $this->createForm(ReleveType::class, $releve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($releve);
            $entityManager->flush();

            return $this->redirectToRoute('app_releve_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('releve/new.html.twig', [
            'releve' => $releve,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_releve_show', methods: ['GET'])]
    public function show(Releve $releve): Response
    {
        return $this->render('releve/show.html.twig', [
            'releve' => $releve,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_releve_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Releve $releve, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReleveType::class, $releve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_releve_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('releve/edit.html.twig', [
            'releve' => $releve,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_releve_delete', methods: ['POST'])]
    public function delete(Request $request, Releve $releve, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $releve->getId(), $request->request->get('_token'))) {
            $entityManager->remove($releve);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_releve_index', [], Response::HTTP_SEE_OTHER);
    }
}
