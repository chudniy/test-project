<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Comment;
use App\Service\RedmineClient;

class ProjectController extends Controller
{
    /**
     * @Route("/", name="project_list")
     *
     * @param RedmineClient $redmineClient
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(RedmineClient $redmineClient)
    {
        $projects = array();

        $client = $redmineClient->getClient();

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
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
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
     * @param RedmineClient $redmineClient
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function show($projectId, RedmineClient $redmineClient)
    {
        $client = $redmineClient->getClient();

        $projectData = $client->project->show($projectId);
        $issuesData = $client->issue->all(['project_id' => $projectId]);

        // if projectId is incorrect
        if (!$projectData) {
            $this->addFlash(
                'error',
                "The project was not found on the server"
            );

            return $this->redirect($this->generateUrl('project_list'));
        }


        return $this->render('project/show.html.twig', array(
            'project' => $projectData ? $projectData['project'] : false,
            'issues_list' => $issuesData['issues'] ?? false,
        ));
    }

    /**
     * @Route("/time/add", name="time_add")
     *
     * @param Request $request
     * @param RedmineClient $redmineClient
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Exception
     */
    public function addTime(Request $request, RedmineClient $redmineClient)
    {
        $client = $redmineClient->getClient();

        $issue_id = $request->request->get('issue_id');
        $hours = $request->request->get('hours');
        $activity = $request->request->get('activity');
        $comment = $request->request->get('comment') ? $request->request->get('comment') : '';

        if ($issue_id && $hours && $activity) {
            $client->time_entry->create([
                'issue_id' => $issue_id,
                'hours' => $hours,
                'activity_id' => $activity,
                'comments' => $comment,
            ]);

            $this->addFlash(
                'notice',
                "The time item was added to this task"
            );
        } else {
            $this->addFlash(
                'error',
                "Something went wrong!"
            );
        }

        return $this->redirect($this->generateUrl('time_entries', array('issueId' => $issue_id)));
    }

    /**
     * @Route("/time/delete", name="delete_time")
     *
     * @param Request $request
     * @param RedmineClient $redmineClient
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteTime(Request $request, RedmineClient $redmineClient)
    {
        $timeEntryId = $request->request->get('time_id');

        $client = $redmineClient->getClient();

        if ($timeEntryId){
            $client->time_entry->remove($timeEntryId);
        }

        return $this->json(array('message' => 'Time entry was deleted'));
    }

    /**
     * @Route("/time/{issueId}", name="time_entries")
     *
     * @param integer $issueId
     * @param RedmineClient $redmineClient
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function time($issueId, RedmineClient $redmineClient)
    {
        $client = $redmineClient->getClient();

        $timeEntriesData = $client->time_entry->all([
            'issue_id' => $issueId,
        ]);

        return $this->render('project/time.html.twig', array(
            'issue_id' => $issueId,
            'user_id' => 20,
            'time_data' => $timeEntriesData,
        ));
    }
}