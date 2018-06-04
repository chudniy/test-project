<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Comment;
use Redmine;

class ProjectController extends Controller
{
    /**
     * @Route("/", name="project_list")
     */
    public function list()
    {
        $projects = array();

        $apiKey = $this->container->getParameter('redmine.key');
        $apiUrl = $this->container->getParameter('redmine.url');

        $client = new Redmine\Client($apiUrl, $apiKey);

        $projectsData = $client->project->all();

        if ($projectsData) {
            $projects = $projectsData['projects'];

            foreach ($projects as $index => $project) {
                $comment = $this->getDoctrine()
                                ->getRepository(Comment::class)
                                ->findOneBy(array('project_id' => $project['id']));

                if ($comment) {
                    $projects[$index]['comment'] = $comment->getComment();
                } else {
                    $projects[$index]['comment'] = '';
                }
            }
        }

        return $this->render('project/list.html.twig', array(
            'project_array' => $projects,
        ));
    }

    /**
     * @Route("/project/add_comment", name="add_comment")
     */
    public function addComment(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $projectId = $request->request->get('project_id');
        $commentText = $request->request->get('comment');

        $comment = $this->getDoctrine()
                        ->getRepository(Comment::class)
                        ->findOneBy(array('project_id' => $projectId));

        if ($comment) {
            $comment->setComment($commentText);

            $entityManager->flush();

            return $this->json(array('message' => 'Comment was changed'));
        }

        $comment = new Comment();
        $comment->setProjectId($projectId);
        $comment->setComment($commentText);

        $entityManager->persist($comment);

        $entityManager->flush();

        return $this->json(array('message' => 'Comment was added'));

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
                "The project was not found on the server"
            );

            return $this->redirect($this->generateUrl('project_list'));
        }


        dump($projectData['project']);
        dump($issuesData);

        return $this->render('project/show.html.twig', array(
            'project'     => $projectData ? $projectData['project'] : false,
            'issues_list' => $issuesData['issues'] ?? false,
        ));
    }

}