<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Redmine;

class ProjectController extends Controller
{
    /**
     * @Route("/", name="project_list")
     */
    public function list()
    {
        $client = new Redmine\Client('https://redmine.ekreative.com', '2fda745bb4cdd835fdf41ec1fab82a13ddc1a54c');

        return $this->render('project/list.html.twig', array(
            'project_array' =>  $client->project->all()['projects'],
        ));
    }

    /**
     * @Route("/project/{projectId}", name="project_show")
     *
     * @param integer $projectId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($projectId = null)
    {
        $client = new Redmine\Client('https://redmine.ekreative.com', '2fda745bb4cdd835fdf41ec1fab82a13ddc1a54c');

        if ($projectId) {
            $projectData = $client->project->show($projectId);
            $issuesData = $client->issue->all(['project_id' => $projectId]);


            dump( $projectData['project'] );
            dump(  $issuesData);

            return $this->render('project/show.html.twig', array(
                'project' => $projectData ? $projectData['project'] : false,
                'issues_list' =>  isset($issuesData['issues'])? $issuesData['issues'] : null,
            ));
        } else {
            return $this->redirect($this->generateUrl('project_list', array(
                'error' =>true,
                'error_text' => "Project wasn't founded on the server",
            )), 301
            );
        }
    }
}