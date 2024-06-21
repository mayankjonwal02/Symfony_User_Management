<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\UploadService;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\BufferedOutput;
use App\Command\BackupCommand;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Exception\CommandNotFoundException;

use Symfony\Component\HttpFoundation\Response;



class UserController extends AbstractController
{
    private $entityManager;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }
    #[Route('/user', name: 'app_user')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    #[Route('/api/connection', name: 'connection', methods: ['GET'])]
    public function getConnection(Connection $connection): JsonResponse
    {
        try {
            $connection->connect();
            if ($connection->isConnected()) {
                return new JsonResponse(['message' => 'Connection is successful'], 200);
            } else {
                return new JsonResponse(['message' => 'Connection is not successful'], 404);
            }
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 404);
        }
    }


    #[Route('/api/upload', name: 'upload', methods: ['POST'])]
    public function upload(Request $request, UploadService $uploadService): JsonResponse
    {
        $uploadedFile = $request->files->get('file'); // Assuming the form field name is 'file'

        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'File not found'], 400);
        }

        try {
            $uploadService->uploadFile($uploadedFile);

            return new JsonResponse(['message' => 'File uploaded and data saved successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/users', name: 'upload', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        // Get all users from the database
        $users = $this->userRepository->findAll();
        $userData = [];

        // Prepare response data (example: extract relevant fields)
        foreach ($users as $user) {
            $userData[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'address' => $user->getAddress(),
                'role' => $user->getRole(),
                // Add other fields as needed
            ];
        }

        // Return JSON response with user data
        return new JsonResponse($userData);
    }

    #[Route('/api/backup', name: 'backup', methods: ['GET'])]
    public function backup(KernelInterface $kernel): BinaryFileResponse
    {
        try {
            // Get the Symfony application instance
            $application = new Application($kernel);
            $application->setAutoExit(false); // Prevents the command from exiting the script

            // Find the BackupCommand by its name
            $command = $application->find('app:backup-db');

            // Create input and output instances for the command
            $input = new ArrayInput([]);
            $output = new BufferedOutput();

            // Execute the command
            $command->run($input, $output);

            // Check if the backup file was created successfully
            $backupFilePath = 'backup.sql'; // Assuming backup.sql is the file generated by the BackupCommand

            // Check if the file exists
            if (!file_exists($backupFilePath)) {
                return new JsonResponse(['error' => 'Backup file not found.'], 404);
            }

            // Create BinaryFileResponse to return the backup.sql file
            $response = new BinaryFileResponse($backupFilePath);

            // Set headers for file download
            $response->headers->set('Content-Type', 'application/sql');
            $response->headers->set('Content-Disposition', 'attachment; filename="backup.sql"');

            return $response;
        } catch (CommandNotFoundException $e) {
            return new JsonResponse(['error' => 'Backup command not found.'], 404);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An unexpected error occurred.'], 500);
        }
    }


    #[Route('/api/restore', name: 'restore', methods: ['POST'])]

    public function restoreDatabase(Request $request): Response
    {
        // Handle file upload
        $file = $request->files->get('file');

        if (!$file) {
            return new JsonResponse(['error' => 'Backup file not found in request body.'], Response::HTTP_BAD_REQUEST);
        }

        // Validate file type if needed
        // Example: Ensure it's a .sql file
        if ($file->getClientOriginalExtension() !== 'sql') {
            return new JsonResponse(['error' => 'Invalid file format. Expected .sql file.'], Response::HTTP_BAD_REQUEST);
        }

        // Move uploaded file to a temporary location
        $backupFilePath = $file->move($this->getParameter('kernel.project_dir'), 'temp_backup.sql')->getRealPath();
        // return new JsonResponse(['message' => file_exists($backupFilePath)]);
        if (!file_exists($backupFilePath)) {
            return new JsonResponse(['error' => 'Backup file not found at the specified path.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Load .env file

        $databaseUrl = $_ENV['DATABASE_URL'];

        // Parse DATABASE_URL
        $dbConfig = parse_url($databaseUrl);
        $dsn = sprintf('mysql:host=%s;dbname=%s', $dbConfig['host'], trim($dbConfig['path'], '/'));
        $user = $dbConfig['user'] ?? null;
        $password = $dbConfig['pass'] ?? null;

        // Construct the command to restore the database
        $command = sprintf(
            'mysql -u%s -p%s %s < %s',
            $user,
            $password,
            trim($dbConfig['path'], '/'),
            $backupFilePath
        );

        // Execute the command
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(3600); // Optional: Set timeout if needed

        try {
            $process->mustRun();
            // Clean up temporary backup file
            unlink($backupFilePath);
            return new JsonResponse(['message' => 'Database restore successful.']);
        } catch (ProcessFailedException $exception) {
            // Clean up temporary backup file on failure
            unlink($backupFilePath);
            return new JsonResponse(['error' => 'Error restoring database: ' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
