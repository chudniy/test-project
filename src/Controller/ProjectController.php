<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Redmine;

class ProjectController extends Controller
{
    /**
     * @Route("/", name="project_list")
     */
    public function list()
    {
        $apiKey = $this->container->getParameter('redmine.key');
        $apiUrl = $this->container->getParameter('redmine.url');

        $client = new Redmine\Client($apiUrl, $apiKey);

        return $this->render('project/list.html.twig', array(
            'project_array' => $client->project->all()['projects'],
        ));
    }

    /**
     * @Route("/project/{projectId}", name="project_show")
     *
     * @param integer $projectId
     */
    public function show($projectId)
    {
        $apiKey = $this->container->getParameter('redmine.key');
        $apiUrl = $this->container->getParameter('redmine.url');

        $client = new Redmine\Client($apiUrl, $apiKey);

        $projectData = $client->project->show($projectId);
        $issuesData  = $client->issue->all(['project_id' => $projectId]);

        // if projectId is incorrect
        if ( ! $projectData) {
            $this->addFlash(
                'error',
                "Project wasn't founded on the server"
            );

            return $this->redirect($this->generateUrl('project_list'));
        }


        dump($projectData['project']);
        dump($issuesData);

        return $this->render('project/show.html.twig', array(
            'project'     => $projectData ? $projectData['project'] : false,
            'issues_list' => $issuesData['issues'] ?? null,
        ));
    }
}