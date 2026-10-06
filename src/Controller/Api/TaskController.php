<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\ProjectRepository;
use App\Security\Voter\ProjectVoter;
use App\Security\Voter\TaskVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tasks', name: 'api_tasks_')]
class TaskController extends AbstractController
{
    private const READ_CONTEXT = ['groups' => ['task:read']];
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, TaskRepository $taskRepository, ProjectRepository $projectRepository, #[CurrentUser] User $user): JsonResponse
    {
        $project = null;

        if($request->query->has('projectId')) { // teste l'existence de la clé (ici le projectId)

            $projectId = filter_var($request->query->get('projectId'), FILTER_VALIDATE_INT); // vérifie que ce qu'on récupère puis renvoie est bien un entier avec FILTER_VALIDATE_INT dans filter_var()

            if($projectId === false) {

                return $this->json(['error' => 'projectId invalide'], 400);
            }

            $project = $projectRepository->find($projectId);

            if($project === null) {

                return $this->json(['error' => 'Project not found'], 404);
            }

            $this->denyAccessUnlessGranted(ProjectVoter::ACCESS, $project);
        }

        return $this->json($taskRepository->findByOwner($user, $project), context: self::READ_CONTEXT); // context: nous permet ici d'éviter de toujours réécrire '200, []'
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'] ,methods: ['GET'])] // requirements nous permet, avec un regex, de vérifier que l'id est bien juste composé de chiffres
    public function show(Task $task): JsonResponse
    {
        $this->denyAccessUnlessGranted(TaskVoter::ACCESS, $task);

        return $this->json($task, context: self::READ_CONTEXT); 
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, ProjectRepository $projectRepository): JsonResponse
    {
        $data = $request->toArray(); // vérifie qu'on a bien du json qui donne un tableau

        $project = isset($data['projectId']) ? $projectRepository->find($data['projectId']) : null;

        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }

        $this->denyAccessUnlessGranted(ProjectVoter::ACCESS, $project);

        $task = new Task();
        $task->setProject($project);
        $task->setStatus($data['status'] ?? 'todo');
        $this->applyData($task, $data); // applyData() s'occupe du reste, voir plus bas

        if ($errorResponse = $this->validationErrors($task))  {
            return $errorResponse;
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->json($task, context: self::READ_CONTEXT);
    }

    #[Route('/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function update(Task $task, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(TaskVoter::ACCESS, $task);

        $this->applyData($task, $request->toArray());

        if($errorResponse = $this->validationErrors($task)) {
            return $errorResponse;
        }

        $this->entityManager->flush();

        return $this->json($task, context: self::READ_CONTEXT);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Task $task): JsonResponse
    {
        $this->denyAccessUnlessGranted(TaskVoter::ACCESS, $task);

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return new JsonResponse(null, 204);
    }

    /**
     * applyData() applique les champs présents dans le payload, 
     * mettant ainsi à jour les données qui sont pas vite (et nous évite d'écrire des lignes en plus pour update chaque donnée)
     * @param Task $task
     * @param array $data
     * @throws BadRequestHttpException
     * @return void
     */
    private function applyData(Task $task, array $data): void
    {
        if(array_key_exists('title', $data)) {
            $task->setTitle((string) $data['title']);
        }

        if (array_key_exists('description', $data)) {
            $task->setDescription($data['description']);
        }

        if (array_key_exists('status', $data)) {
            $task->setStatus((string) $data['status']);
        }

        if (array_key_exists('dueDate', $data)) {
            try {
                $task->setDueDate($data['dueDate'] ? new \DateTime($data['dueDate']) : null);
            } catch (\Exception) {
                throw new BadRequestHttpException('InvalidDueDateFormat');
            }
        }
    }

    /**
     * Vérifie si les contraintes de l'entité sont respectée (ici NotBlank, donc s'assurer que certains champs dne sont pas vides)
     * Renvoie un tableau avec messages détaillés si il ya  des erreurs quelque part
     * @param Task $task
     * @return JsonResponse|null
     */
    private function validationErrors(Task $task): ?JsonResponse
    {
        $violations = $this->validator->validate($task);

        if(count($violations) === 0) {
            return null;
        }

        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $this->json(['errors' => $errors], 422);
    }
}