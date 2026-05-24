<?php

namespace App\Controller;

use App\Entity\MonitoredDate;
use App\Entity\PermitWatch;
use App\Form\MonitoredDateType;
use App\Form\PermitWatchType;
use App\Repository\MonitoredDateRepository;
use App\Repository\PermitWatchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MonitoredDateController extends AbstractController
{
    #[Route('/monitored_date/new', name: 'app_monitored_date_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $monitoredDate = new MonitoredDate();
        $form = $this->createForm(MonitoredDateType::class, $monitoredDate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the current user
            $monitoredDate->setUser($this->getUser());

            $em->persist($monitoredDate);
            $em->flush();

            $this->addFlash('success', 'Now monitoring!');

            return $this->redirectToRoute('app_my_monitored_dates');
        }

        return $this->render('monitoredDate/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/monitored_dates', name: 'app_my_monitored_dates')]
    public function show(MonitoredDateRepository $monitoredDateRepository): Response
    {
        $user = $this->getUser();
        $monitoredDates = $monitoredDateRepository->findBy(
            ['user' => $user],
            ['date' => 'ASC']
        );

        return $this->render('monitoredDate/monitoredDates.html.twig', [
            'monitoredDates' => $monitoredDates,
        ]);
    }

    #[Route('/monitored_date/delete/{id}', name: 'app_monitored_date_delete')]
    public function delete(Request $request, MonitoredDate $monitoredDate, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $monitoredDate->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        //Ensure the permit watch belongs to the current user
        if ($monitoredDate->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not have permission to delete this.');
        }

        $em->remove($monitoredDate);
        $em->flush();

        $this->addFlash('success', 'Monitored date deleted successfully!');

        return $this->redirectToRoute('app_my_monitored_dates');
    }
}
