<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new user or admin for the micro-learning platform.',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask('Email', null, function (?string $value) {
            if (!$value) {
                throw new \RuntimeException('L\'email est requis.');
            }

            return $value;
        });

        if ($this->userRepository->findOneBy(['email' => $email])) {
            $io->error('Un utilisateur avec cet email existe déjà.');
            return Command::FAILURE;
        }

        $password = $io->askHidden('Mot de passe', function (?string $value) {
            if (!$value) {
                throw new \RuntimeException('Le mot de passe est requis.');
            }

            return $value;
        });

        $role = $io->choice('Type de compte', ['ROLE_USER', 'ROLE_ADMIN'], 'ROLE_USER');

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Utilisateur');
        $user->setLastName('Créé par CLI');
        $user->setRoles([$role]);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Utilisateur %s créé avec le rôle %s.', $email, $role));

        return Command::SUCCESS;
    }
}
